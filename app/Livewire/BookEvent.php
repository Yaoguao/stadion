<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\SeatInstance;
use App\Services\BookingService;
use App\Services\SeatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BookEvent extends Component
{
    public $eventId;
    public $event;
    public $selectedSeats = [];
    public $seatInstances = [];
    public $seatsBySector = [];
    public $seatsBySectorGrouped;
    public $totalAmount = 0;
    public $showSummary = false;
    public $showSeatModal = false;
    public $priceFilter = null;
    public $selectedSector = null;

    protected $listeners = ['seatSelected', 'seatDeselected'];

    public function mount($eventId)
    {
        // Валидация и нормализация eventId
        if (empty($eventId)) {
            abort(404, 'Событие не указано');
        }

        $this->eventId = $eventId;
        
        // Загружаем событие с проверкой существования
        $this->event = Event::with('venue')->find($eventId);
        
        if (!$this->event) {
            abort(404, 'Событие не найдено');
        }
        
        if (!$this->event->is_published) {
            abort(404, 'Событие не опубликовано');
        }

        // Проверяем, что событие еще не прошло
        if ($this->event->start_at && $this->event->start_at->isPast()) {
            abort(404, 'Событие уже прошло');
        }

        $this->loadSeatInstances();
    }

    public function loadSeatInstances()
    {
        // Инициализируем пустые коллекции
        $this->seatInstances = [];
        $this->seatsBySector = [];
        $this->seatsBySectorGrouped = collect();
        
        if (!$this->event || !$this->event->venue_id) {
            return;
        }

        // ОПТИМИЗАЦИЯ: Загружаем только статистику по секторам для схемы (не все места!)
        try {
            $sectorStats = SeatInstance::where('event_id', $this->eventId)
                ->join('seats', 'seat_instances.seat_id', '=', 'seats.id')
                ->selectRaw('
                    seats.sector,
                    COUNT(*) as total_seats,
                    SUM(CASE WHEN seat_instances.status = ? THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN seat_instances.status = ? THEN 1 ELSE 0 END) as reserved,
                    SUM(CASE WHEN seat_instances.status = ? THEN 1 ELSE 0 END) as sold,
                    MIN(seat_instances.price) as min_price,
                    MAX(seat_instances.price) as max_price
                ', [
                    SeatInstance::STATUS_AVAILABLE,
                    SeatInstance::STATUS_RESERVED,
                    SeatInstance::STATUS_SOLD
                ])
                ->groupBy('seats.sector')
                ->get()
                ->keyBy('sector');

            // Создаем упрощенную структуру для схемы
            $this->seatsBySectorGrouped = $sectorStats->map(function($stat) {
                return [
                    'total_seats' => (int) ($stat->total_seats ?? 0),
                    'available' => (int) ($stat->available ?? 0),
                    'reserved' => (int) ($stat->reserved ?? 0),
                    'sold' => (int) ($stat->sold ?? 0),
                    'min_price' => (float) ($stat->min_price ?? 0),
                    'max_price' => (float) ($stat->max_price ?? 0),
                ];
            });
        } catch (\Exception $e) {
            // В случае ошибки оставляем пустую коллекцию
            \Log::error('Error loading seat instances: ' . $e->getMessage());
            $this->seatsBySectorGrouped = collect();
        }
    }

    /**
     * Загружает места для конкретного сектора (ленивая загрузка)
     */
    public function loadSeatsForSector($sectorName)
    {
        // Проверяем, что событие и venue_id существуют
        if (!$this->event || !$this->event->venue_id || empty($sectorName)) {
            return collect();
        }

        // Проверяем, что eventId валиден
        if (empty($this->eventId)) {
            return collect();
        }

        try {
            // Загружаем только места для выбранного сектора
            $seats = \App\Models\Seat::where('venue_id', $this->event->venue_id)
                ->where('sector', $sectorName)
                ->with(['seatInstances' => function($query) {
                    $query->where('event_id', $this->eventId);
                }])
                ->orderBy('row_num')
                ->orderBy('seat_number')
                ->get();

            // Группируем по рядам
            return $seats->groupBy('row_num')->map(function($seatsInRow) {
                return $seatsInRow->sortBy('seat_number')->values();
            });
        } catch (\Exception $e) {
            \Log::error('Error loading seats for sector: ' . $e->getMessage(), [
                'eventId' => $this->eventId,
                'sectorName' => $sectorName,
                'venueId' => $this->event->venue_id ?? null,
            ]);
            return collect();
        }
    }

    public function toggleSeat($seatInstanceId)
    {
        // Проверяем валидность параметров
        if (empty($seatInstanceId) || empty($this->eventId)) {
            $this->dispatch('error', message: 'Неверные параметры запроса.');
            return;
        }

        // Ленивая загрузка экземпляра места если его нет
        if (!isset($this->seatInstances[$seatInstanceId])) {
            $seatInstance = SeatInstance::where('id', $seatInstanceId)
                ->where('event_id', $this->eventId)
                ->with(['seat'])
                ->first();
            
            if (!$seatInstance) {
                $this->dispatch('error', message: 'Место не найдено.');
                return;
            }
            
            // Проверяем, что место принадлежит текущему событию
            if ($seatInstance->event_id !== $this->eventId) {
                $this->dispatch('error', message: 'Место не принадлежит этому событию.');
                return;
            }
            
            $this->seatInstances[$seatInstanceId] = $seatInstance;
        } else {
            $seatInstance = $this->seatInstances[$seatInstanceId];
            // Если это массив (старые данные), загружаем объект
            if (is_array($seatInstance)) {
                $seatInstance = SeatInstance::where('id', $seatInstanceId)
                    ->where('event_id', $this->eventId)
                    ->with(['seat'])
                    ->first();
                if ($seatInstance) {
                    $this->seatInstances[$seatInstanceId] = $seatInstance;
                } else {
                    $this->dispatch('error', message: 'Место не найдено.');
                    return;
                }
            }
        }

        // Проверяем доступность места (быстрая проверка без запроса к БД)
        $status = is_array($seatInstance) ? ($seatInstance['status'] ?? null) : $seatInstance->status;
        if ($status !== SeatInstance::STATUS_AVAILABLE) {
            $this->dispatch('error', message: 'Это место уже занято или забронировано.');
            return;
        }

        // Переключаем выбор места
        if (in_array($seatInstanceId, $this->selectedSeats)) {
            $this->selectedSeats = array_values(array_diff($this->selectedSeats, [$seatInstanceId]));
        } else {
            $this->selectedSeats[] = $seatInstanceId;
        }

        $this->calculateTotal();
    }

    public function selectSector($sectorName)
    {
        // Проверяем валидность параметров
        if (empty($sectorName) || !$this->event || !$this->event->venue_id || empty($this->eventId)) {
            $this->dispatch('error', message: 'Неверные параметры запроса.');
            return;
        }

        $this->selectedSector = $sectorName;
        $this->showSeatModal = true;
        // Ленивая загрузка мест для сектора
        $this->seatsBySector = [$sectorName => $this->loadSeatsForSector($sectorName)];
        
        // Предзагружаем ВСЕ экземпляры мест для сектора одним запросом
        $sectorSeats = $this->seatsBySector[$sectorName] ?? collect();
        $allSeatInstanceIds = [];
        
        foreach ($sectorSeats as $rowSeats) {
            foreach ($rowSeats as $seat) {
                $instance = $seat->seatInstances->where('event_id', $this->eventId)->first();
                if ($instance) {
                    $allSeatInstanceIds[] = $instance->id;
                }
            }
        }
        
        // Добавляем уже выбранные места
        $allSeatInstanceIds = array_merge($allSeatInstanceIds, $this->selectedSeats);
        $allSeatInstanceIds = array_unique($allSeatInstanceIds);
        
        // Загружаем все недостающие экземпляры одним запросом
        if (!empty($allSeatInstanceIds)) {
            $existingIds = array_keys($this->seatInstances);
            $newIds = array_diff($allSeatInstanceIds, $existingIds);
            
            if (!empty($newIds)) {
                $newInstances = SeatInstance::whereIn('id', $newIds)
                    ->where('event_id', $this->eventId)
                    ->with(['seat'])
                    ->get()
                    ->keyBy('id');
                
                foreach ($newInstances as $id => $instance) {
                    $this->seatInstances[$id] = $instance;
                }
            }
        }
    }

    public function closeSeatModal()
    {
        // Быстрое закрытие без дополнительных операций
        $this->showSeatModal = false;
        $this->selectedSector = null;
        // Не очищаем seatsBySector для быстрого повторного открытия
    }

    public function calculateTotal()
    {
        $this->totalAmount = 0;
        foreach ($this->selectedSeats as $seatInstanceId) {
            if (isset($this->seatInstances[$seatInstanceId])) {
                $seatInstance = $this->seatInstances[$seatInstanceId];
                $price = is_array($seatInstance) ? ($seatInstance['price'] ?? 0) : $seatInstance->price;
                $this->totalAmount += (float) $price;
            }
        }
    }

    public function getSeatStatus($seatInstanceId)
    {
        // Быстрая проверка - если место выбрано, возвращаем сразу
        if (in_array($seatInstanceId, $this->selectedSeats)) {
            return 'selected';
        }

        // Если экземпляр уже загружен, используем его
        if (isset($this->seatInstances[$seatInstanceId])) {
            $seatInstance = $this->seatInstances[$seatInstanceId];
            if (is_array($seatInstance)) {
                return $seatInstance['status'] ?? 'unknown';
            }
            return $seatInstance->status;
        }

        // Если не загружен, возвращаем unknown (не делаем запрос к БД для производительности)
        // Статус будет обновлен при следующем обновлении компонента
        return 'unknown';
    }

    public function createBooking()
    {
        if (empty($this->selectedSeats)) {
            $this->dispatch('error', message: 'Выберите хотя бы одно место.');
            return;
        }

        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        try {
            DB::beginTransaction();

            // Проверяем, что все выбранные места все еще доступны
            $availableSeats = SeatInstance::whereIn('id', $this->selectedSeats)
                ->where('status', SeatInstance::STATUS_AVAILABLE)
                ->where('event_id', $this->eventId)
                ->lockForUpdate()
                ->get();

            if ($availableSeats->count() !== count($this->selectedSeats)) {
                DB::rollBack();
                $this->dispatch('error', message: 'Некоторые места уже заняты. Пожалуйста, выберите другие места.');
                $this->loadSeatInstances();
                return;
            }

            // Пересчитываем сумму на основе актуальных цен
            $actualTotal = $availableSeats->sum('price');
            if (abs($actualTotal - $this->totalAmount) > 0.01) {
                $this->totalAmount = $actualTotal;
            }

            // Создаем бронирование
            $bookingService = app(BookingService::class);
            $seatService = app(SeatService::class);

            $bookingData = [
                'user_id' => Auth::id(),
                'event_id' => $this->eventId,
                'total_amount' => $this->totalAmount,
                'status' => \App\Models\Booking::STATUS_PENDING,
                'expires_at' => now()->addMinutes(15), // 15 минут на оплату
            ];

            // Подготавливаем данные для элементов бронирования
            $itemsData = [];
            foreach ($availableSeats as $seatInstance) {
                $itemsData[] = [
                    'seat_instance_id' => $seatInstance->id,
                    'price' => $seatInstance->price,
                    'fee' => 0, // Можно добавить комиссию
                ];
            }

            // Создаем бронирование с элементами
            $booking = $bookingService->createBooking($bookingData, $itemsData);

            if (!$booking) {
                throw new \Exception('Не удалось создать бронирование');
            }

            // Резервируем места после создания бронирования
            $expiresAt = new \DateTime();
            $expiresAt->modify('+15 minutes');
            
            foreach ($availableSeats as $seatInstance) {
                $result = $seatService->reserveSeat(
                    $seatInstance->id,
                    $booking->id,
                    $expiresAt
                );
                
                if (!$result) {
                    throw new \Exception("Не удалось зарезервировать место {$seatInstance->id}");
                }
            }
            
            DB::commit();
            
            // Очищаем выбранные места
            $this->selectedSeats = [];
            $this->totalAmount = 0;
            
            // Обновляем только статистику схемы (быстро)
            $this->loadSeatInstances();

            session()->flash('success', 'Бронирование создано успешно! У вас есть 15 минут на оплату.');
            session()->flash('activeTab', 'bookings');
            return $this->redirect(route('profile'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Произошла ошибка при создании бронирования: ' . $e->getMessage();
            $this->dispatch('error', message: $errorMessage);
            \Log::error('Booking error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'selectedSeats' => $this->selectedSeats,
                'eventId' => $this->eventId,
                'userId' => Auth::id(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.book-event')->layout('layouts.app');
    }
}


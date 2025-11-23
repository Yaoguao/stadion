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
    public $seatsBySector;
    public $seatsBySectorGrouped;
    public $totalAmount = 0;
    public $showSummary = false;
    public $showSeatModal = false;
    public $priceFilter = null;
    public $selectedSector = null;

    protected $listeners = ['seatSelected', 'seatDeselected'];

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->event = Event::with('venue')->findOrFail($eventId);
        
        if (!$this->event->is_published) {
            abort(404);
        }

        $this->loadSeatInstances();
    }

    public function loadSeatInstances()
    {
        // Инициализируем пустые коллекции
        $this->seatInstances = collect();
        $this->seatsBySector = collect();
        $this->seatsBySectorGrouped = collect();
        
        if (!$this->event || !$this->event->venue_id) {
            return;
        }

        // ОПТИМИЗАЦИЯ: Загружаем только статистику по секторам для схемы (не все места!)
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
                'total_seats' => (int) $stat->total_seats,
                'available' => (int) $stat->available,
                'reserved' => (int) $stat->reserved,
                'sold' => (int) $stat->sold,
                'min_price' => (float) $stat->min_price,
                'max_price' => (float) $stat->max_price,
            ];
        });
    }

    /**
     * Загружает места для конкретного сектора (ленивая загрузка)
     */
    public function loadSeatsForSector($sectorName)
    {
        if (!$this->event || !$this->event->venue_id) {
            return collect();
        }

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
    }

    public function toggleSeat($seatInstanceId)
    {
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
            
            $this->seatInstances[$seatInstanceId] = $seatInstance;
        } else {
            $seatInstance = $this->seatInstances[$seatInstanceId];
        }

        // Проверяем доступность места
        if ($seatInstance->status !== SeatInstance::STATUS_AVAILABLE) {
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
        $this->selectedSector = $sectorName;
        $this->showSeatModal = true;
        // Ленивая загрузка мест для сектора
        $this->seatsBySector = collect([$sectorName => $this->loadSeatsForSector($sectorName)]);
        
        // Загружаем экземпляры мест для выбранных мест (если есть) и для текущего сектора
        $seatInstanceIds = $this->selectedSeats;
        
        // Добавляем ID экземпляров из текущего сектора
        $sectorSeats = $this->seatsBySector[$sectorName] ?? collect();
        foreach ($sectorSeats as $rowSeats) {
            foreach ($rowSeats as $seat) {
                $instance = $seat->seatInstances->where('event_id', $this->eventId)->first();
                if ($instance && !in_array($instance->id, $seatInstanceIds)) {
                    $seatInstanceIds[] = $instance->id;
                }
            }
        }
        
        if (!empty($seatInstanceIds)) {
            $existingIds = $this->seatInstances->keys()->toArray();
            $newIds = array_diff($seatInstanceIds, $existingIds);
            
            if (!empty($newIds)) {
                $newInstances = SeatInstance::whereIn('id', $newIds)
                    ->where('event_id', $this->eventId)
                    ->with(['seat'])
                    ->get()
                    ->keyBy('id');
                
                $this->seatInstances = $this->seatInstances->merge($newInstances);
            }
        }
    }

    public function closeSeatModal()
    {
        $this->showSeatModal = false;
        $this->selectedSector = null;
    }

    public function calculateTotal()
    {
        $this->totalAmount = 0;
        foreach ($this->selectedSeats as $seatInstanceId) {
            if (isset($this->seatInstances[$seatInstanceId])) {
                $this->totalAmount += (float) $this->seatInstances[$seatInstanceId]->price;
            }
        }
    }

    public function getSeatStatus($seatInstanceId)
    {
        if (in_array($seatInstanceId, $this->selectedSeats)) {
            return 'selected';
        }

        if (!isset($this->seatInstances[$seatInstanceId])) {
            // Пытаемся загрузить статус из БД
            $status = SeatInstance::where('id', $seatInstanceId)
                ->where('event_id', $this->eventId)
                ->value('status');
            
            return $status ?: 'unknown';
        }

        return $this->seatInstances[$seatInstanceId]->status;
    }

    public function createBooking()
    {
        if (empty($this->selectedSeats)) {
            $this->dispatch('error', message: 'Выберите хотя бы одно место.');
            return;
        }

        if (!Auth::check()) {
            return redirect()->route('login');
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
            return redirect()->route('profile')->with('activeTab', 'bookings');

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


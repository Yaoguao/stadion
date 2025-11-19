<?php

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatInstance;
use App\Services\EventService;
use Livewire\Component;

class ManageSeats extends Component
{
    public $eventId;
    public $event;
    public $seats = [];
    public $seatInstances = [];
    public $showGenerateForm = false;
    public $sectors = 4;
    public $rows = 20;
    public $seatsPerRow = 30;
    public $basePrice = 1000;

    public function mount($event)
    {
        $this->eventId = $event;
        $this->loadEvent();
        $this->loadSeats();
    }

    public function loadEvent()
    {
        $eventService = app(EventService::class);
        $this->event = $eventService->getEventById($this->eventId, true);

        if (!$this->event) {
            session()->flash('error', 'Событие не найдено.');
            return $this->redirect(route('admin.events.index'), navigate: true);
        }
    }

    public function loadSeats()
    {
        // Загружаем места venue
        $this->seats = Seat::where('venue_id', $this->event->venue_id)
            ->orderBy('sector')
            ->orderBy('row_num')
            ->orderBy('seat_number')
            ->get();

        // Загружаем экземпляры мест для события
        $this->seatInstances = SeatInstance::where('event_id', $this->eventId)
            ->with('seat')
            ->get()
            ->keyBy('seat_id');
    }

    public function generateSeats()
    {
        $this->validate([
            'sectors' => 'required|integer|min:1|max:20',
            'rows' => 'required|integer|min:1|max:100',
            'seatsPerRow' => 'required|integer|min:1|max:100',
            'basePrice' => 'required|numeric|min:0',
        ]);

        $venue = $this->event->venue;
        $created = 0;
        $skipped = 0;

        for ($sector = 1; $sector <= $this->sectors; $sector++) {
            $sectorName = $this->getSectorName($sector);
            
            for ($row = 1; $row <= $this->rows; $row++) {
                for ($seatNum = 1; $seatNum <= $this->seatsPerRow; $seatNum++) {
                    $price = $this->calculatePrice($this->basePrice, $sector, $row, $this->rows);
                    
                    $exists = Seat::where('venue_id', $venue->id)
                        ->where('sector', $sectorName)
                        ->where('row_num', $row)
                        ->where('seat_number', $seatNum)
                        ->exists();

                    if (!$exists) {
                        Seat::create([
                            'venue_id' => $venue->id,
                            'sector' => $sectorName,
                            'zone' => $this->getZone($sector, $this->sectors),
                            'row_num' => $row,
                            'seat_number' => $seatNum,
                            'base_price' => $price,
                            'view_rating' => $this->calculateViewRating($sector, $row, $this->rows),
                            'is_wheelchair' => false,
                        ]);
                        $created++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }

        $this->loadSeats();
        $this->showGenerateForm = false;

        session()->flash('success', "Создано мест: {$created}, Пропущено (уже существуют): {$skipped}");
    }

    public function generateSeatInstances()
    {
        $eventService = app(EventService::class);
        $created = $eventService->generateSeatInstances($this->eventId, $this->event->venue_id);

        $this->loadSeats();
        session()->flash('success', "Создано экземпляров мест для события: {$created}");
    }

    public function deleteAllSeats()
    {
        if ($this->seats->count() === 0) {
            session()->flash('error', 'Нет мест для удаления.');
            return;
        }

        // Проверяем, есть ли активные бронирования
        $hasActiveBookings = SeatInstance::where('event_id', $this->eventId)
            ->whereIn('status', [SeatInstance::STATUS_RESERVED, SeatInstance::STATUS_SOLD])
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя удалить места, так как есть активные бронирования. Сначала отмените все бронирования.');
            return;
        }

        // Удаляем экземпляры мест для события
        SeatInstance::where('event_id', $this->eventId)->delete();

        // Удаляем сами места venue
        Seat::where('venue_id', $this->event->venue_id)->delete();

        $this->loadSeats();
        session()->flash('success', 'Все места успешно удалены.');
    }

    public function deleteSeatInstances()
    {
        $count = SeatInstance::where('event_id', $this->eventId)->count();
        
        if ($count === 0) {
            session()->flash('error', 'Нет экземпляров мест для удаления.');
            return;
        }

        // Проверяем, есть ли активные бронирования
        $hasActiveBookings = SeatInstance::where('event_id', $this->eventId)
            ->whereIn('status', [SeatInstance::STATUS_RESERVED, SeatInstance::STATUS_SOLD])
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя удалить экземпляры мест, так как есть активные бронирования. Сначала отмените все бронирования.');
            return;
        }

        SeatInstance::where('event_id', $this->eventId)->delete();

        $this->loadSeats();
        session()->flash('success', "Удалено экземпляров мест: {$count}");
    }

    public function regenerateSeats()
    {
        // Проверяем, есть ли активные бронирования
        $hasActiveBookings = SeatInstance::where('event_id', $this->eventId)
            ->whereIn('status', [SeatInstance::STATUS_RESERVED, SeatInstance::STATUS_SOLD])
            ->exists();

        if ($hasActiveBookings) {
            session()->flash('error', 'Нельзя пересоздать места, так как есть активные бронирования. Сначала отмените все бронирования.');
            return;
        }

        $venue = $this->event->venue;
        
        // Удаляем экземпляры мест для события
        SeatInstance::where('event_id', $this->eventId)->delete();
        
        // Удаляем существующие места venue
        Seat::where('venue_id', $venue->id)->delete();

        // Генерируем новые места
        $created = 0;
        for ($sector = 1; $sector <= $this->sectors; $sector++) {
            $sectorName = $this->getSectorName($sector);
            
            for ($row = 1; $row <= $this->rows; $row++) {
                for ($seatNum = 1; $seatNum <= $this->seatsPerRow; $seatNum++) {
                    $price = $this->calculatePrice($this->basePrice, $sector, $row, $this->rows);
                    
                    Seat::create([
                        'venue_id' => $venue->id,
                        'sector' => $sectorName,
                        'zone' => $this->getZone($sector, $this->sectors),
                        'row_num' => $row,
                        'seat_number' => $seatNum,
                        'base_price' => $price,
                        'view_rating' => $this->calculateViewRating($sector, $row, $this->rows),
                        'is_wheelchair' => false,
                    ]);
                    $created++;
                }
            }
        }

        // Создаем экземпляры для события
        $eventService = app(EventService::class);
        $instancesCreated = $eventService->generateSeatInstances($this->eventId, $venue->id);

        $this->loadSeats();
        session()->flash('success', "Схема мест успешно пересоздана. Создано мест: {$created}, экземпляров: {$instancesCreated}");
    }

    private function getSectorName(int $sector): string
    {
        $sectors = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        return $sectors[$sector - 1] ?? "S{$sector}";
    }

    private function getZone(int $sector, int $totalSectors): string
    {
        if ($sector <= $totalSectors / 4) {
            return 'VIP';
        } elseif ($sector <= $totalSectors / 2) {
            return 'Premium';
        } else {
            return 'Standard';
        }
    }

    private function calculatePrice(float $basePrice, int $sector, int $row, int $totalRows): float
    {
        $zoneMultiplier = $sector <= 2 ? 1.5 : ($sector <= 4 ? 1.2 : 1.0);
        $rowMultiplier = 1.0 + (($totalRows - $row) / $totalRows) * 0.3;
        return round($basePrice * $zoneMultiplier * $rowMultiplier, 2);
    }

    private function calculateViewRating(int $sector, int $row, int $totalRows): int
    {
        $rowRating = 5 - (int)(($row - 1) / ($totalRows / 5));
        return max(1, min(5, $rowRating));
    }

    public function render()
    {
        $seatsBySector = $this->seats->groupBy('sector');
        $seatInstancesCount = $this->seatInstances->count();
        $availableCount = $this->seatInstances->where('status', SeatInstance::STATUS_AVAILABLE)->count();
        $reservedCount = $this->seatInstances->where('status', SeatInstance::STATUS_RESERVED)->count();
        $soldCount = $this->seatInstances->where('status', SeatInstance::STATUS_SOLD)->count();

        return view('livewire.admin.events.manage-seats', [
            'seatsBySector' => $seatsBySector,
            'seatInstancesCount' => $seatInstancesCount,
            'availableCount' => $availableCount,
            'reservedCount' => $reservedCount,
            'soldCount' => $soldCount,
        ])->layout('layouts.admin');
    }
}


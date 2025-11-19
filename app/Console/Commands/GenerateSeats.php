<?php

namespace App\Console\Commands;

use App\Models\Seat;
use App\Models\Venue;
use Illuminate\Console\Command;

class GenerateSeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seats:generate 
                            {venue : ID или название места проведения}
                            {--sectors=4 : Количество секторов}
                            {--rows=20 : Количество рядов в секторе}
                            {--seats-per-row=30 : Количество мест в ряду}
                            {--base-price=1000 : Базовая цена места}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует схему мест для места проведения (venue)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $venueIdentifier = $this->argument('venue');
        $sectors = (int) $this->option('sectors');
        $rows = (int) $this->option('rows');
        $seatsPerRow = (int) $this->option('seats-per-row');
        $basePrice = (float) $this->option('base-price');

        // Находим venue
        $venue = Venue::where('id', $venueIdentifier)
            ->orWhere('name', 'like', "%{$venueIdentifier}%")
            ->first();

        if (!$venue) {
            $this->error("Место проведения не найдено: {$venueIdentifier}");
            return 1;
        }

        $this->info("Генерация мест для: {$venue->name}");
        $this->info("Секторов: {$sectors}, Рядов: {$rows}, Мест в ряду: {$seatsPerRow}");

        if ($this->confirm('Продолжить? Это создаст ' . ($sectors * $rows * $seatsPerRow) . ' мест.')) {
            $bar = $this->output->createProgressBar($sectors * $rows * $seatsPerRow);
            $bar->start();

            $created = 0;
            $skipped = 0;

            for ($sector = 1; $sector <= $sectors; $sector++) {
                $sectorName = $this->getSectorName($sector);
                
                for ($row = 1; $row <= $rows; $row++) {
                    for ($seatNum = 1; $seatNum <= $seatsPerRow; $seatNum++) {
                        // Вычисляем цену в зависимости от сектора и ряда
                        $price = $this->calculatePrice($basePrice, $sector, $row, $rows);
                        
                        // Проверяем, не существует ли уже такое место
                        $exists = Seat::where('venue_id', $venue->id)
                            ->where('sector', $sectorName)
                            ->where('row_num', $row)
                            ->where('seat_number', $seatNum)
                            ->exists();

                        if (!$exists) {
                            Seat::create([
                                'venue_id' => $venue->id,
                                'sector' => $sectorName,
                                'zone' => $this->getZone($sector, $sectors),
                                'row_num' => $row,
                                'seat_number' => $seatNum,
                                'base_price' => $price,
                                'view_rating' => $this->calculateViewRating($sector, $row, $rows),
                                'is_wheelchair' => false,
                            ]);
                            $created++;
                        } else {
                            $skipped++;
                        }
                        
                        $bar->advance();
                    }
                }
            }

            $bar->finish();
            $this->newLine();
            $this->info("Готово! Создано мест: {$created}, Пропущено (уже существуют): {$skipped}");
            
            return 0;
        }

        $this->info('Операция отменена.');
        return 0;
    }

    /**
     * Получить название сектора
     */
    private function getSectorName(int $sector): string
    {
        $sectors = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        return $sectors[$sector - 1] ?? "S{$sector}";
    }

    /**
     * Получить зону
     */
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

    /**
     * Вычислить цену места
     */
    private function calculatePrice(float $basePrice, int $sector, int $row, int $totalRows): float
    {
        // VIP зона - дороже
        $zoneMultiplier = $sector <= 2 ? 1.5 : ($sector <= 4 ? 1.2 : 1.0);
        
        // Ближе к полю - дороже
        $rowMultiplier = 1.0 + (($totalRows - $row) / $totalRows) * 0.3;
        
        return round($basePrice * $zoneMultiplier * $rowMultiplier, 2);
    }

    /**
     * Вычислить рейтинг обзора
     */
    private function calculateViewRating(int $sector, int $row, int $totalRows): int
    {
        // Ближе к полю и в центре - лучше обзор
        $rowRating = 5 - (int)(($row - 1) / ($totalRows / 5));
        return max(1, min(5, $rowRating));
    }
}

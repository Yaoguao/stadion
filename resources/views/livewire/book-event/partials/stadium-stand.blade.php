@php
    $sectorCount = count($sectors);
    if ($isHorizontal) {
        $sectorSize = $sectorCount > 0 ? $standWidth / max($sectorCount, 1) : 0;
    } else {
        $sectorSize = $sectorCount > 0 ? $standHeight / max($sectorCount, 1) : 0;
    }
    
    $labelX = $isHorizontal ? $standX + $standWidth / 2 : $standX + $standWidth / 2;
    $labelY = $isHorizontal ? $standY - 10 : $standY - 10;
@endphp

<g>
    <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="middle" class="text-xs font-bold fill-gray-700" style="font-size: 11px;">{{ $standName }}</text>
    @foreach($sectors as $idx => $sectorName)
        @php
            $sectorData = $seatsBySectorGrouped[$sectorName] ?? null;
            $hasSeats = $sectorData && isset($sectorData['total_seats']) && $sectorData['total_seats'] > 0;
            $hasAvailable = $sectorData && isset($sectorData['available']) && $sectorData['available'] > 0;
            $isFiltered = $priceFilter && $sectorData && (
                ($sectorData['min_price'] ?? 0) > $priceFilter || 
                ($sectorData['max_price'] ?? 0) < $priceFilter
            );
            $sectorColor = $color;
            if (!$hasAvailable || $isFiltered) {
                $sectorColor = '#d1d5db';
            }
            $isClickable = $hasSeats && !$isFiltered;
            
            if ($isHorizontal) {
                $sectorX = $standX + ($idx * $sectorSize);
                $sectorY = $standY;
                $sectorWidth = $sectorSize - 2;
                $sectorHeight = $standHeight;
            } else {
                $sectorX = $standX;
                $sectorY = $standY + ($idx * $sectorSize);
                $sectorWidth = $standWidth;
                $sectorHeight = $sectorSize - 2;
            }
        @endphp
        
        @if(!$isFiltered)
            <rect
                x="{{ $sectorX }}"
                y="{{ $sectorY }}"
                width="{{ $sectorWidth }}"
                height="{{ $sectorHeight }}"
                fill="{{ $sectorColor }}"
                stroke="#ffffff"
                stroke-width="2"
                class="transition-all hover:opacity-80 {{ $isClickable ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                @if($isClickable)
                    wire:click="selectSector('{{ $sectorName }}')"
                @endif
                style="pointer-events: {{ $isClickable ? 'all' : 'none' }};"
            />
            @if($hasAvailable)
                <text
                    x="{{ $sectorX + $sectorWidth/2 }}"
                    y="{{ $sectorY + 15 }}"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    class="text-xs font-bold fill-white pointer-events-none"
                    style="font-size: 10px;"
                >
                    {{ $sectorName }}
                </text>
            @endif
            
            <!-- Места в секторе -->
            @if(isset($seatsBySector[$sectorName]))
                @php
                    $rowsInSector = $seatsBySector[$sectorName];
                    $maxSeatsPerRow = $rowsInSector->map(function($row) { return $row->count(); })->max() ?? 10;
                    $maxRows = $rowsInSector->count();
                    $seatSize = min(
                        ($sectorWidth - 10) / max($maxSeatsPerRow, 1),
                        ($sectorHeight - 25) / max($maxRows, 1),
                        6
                    );
                    $seatSpacing = 1.5;
                    $padding = 5;
                @endphp
                @foreach($rowsInSector as $rowNum => $seatsInRow)
                    @php
                        $seatsInRowSorted = $seatsInRow->sortBy('seat_number')->values();
                        if ($isHorizontal) {
                            $rowY = $sectorY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                            $rowStartX = $sectorX + $padding + (($sectorWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                        } else {
                            $rowStartY = $sectorY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                            $rowX = $sectorX + $padding + (($sectorWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                        }
                    @endphp
                    @foreach($seatsInRowSorted as $seatIndex => $seat)
                        @php
                            $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                            $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                            $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                            $isFilteredSeat = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                            
                            if ($isHorizontal) {
                                $seatX = $rowStartX + ($seatIndex * ($seatSize + $seatSpacing));
                                $seatY = $rowY;
                            } else {
                                $seatX = $rowX + ($seatIndex * ($seatSize + $seatSpacing));
                                $seatY = $rowStartY;
                            }
                            
                            $seatColor = '#d1d5db';
                            if ($seatInstanceId && !$isFilteredSeat) {
                                if ($status === 'available') $seatColor = '#10b981';
                                elseif ($status === 'selected') $seatColor = '#3b82f6';
                                elseif ($status === 'reserved') $seatColor = '#f59e0b';
                                elseif ($status === 'sold') $seatColor = '#ef4444';
                            }
                        @endphp
                        @if($seatInstanceId && !$isFilteredSeat)
                            @if($isHorizontal && $seatY < $sectorY + $sectorHeight - 5)
                                <rect
                                    x="{{ $seatX }}"
                                    y="{{ $seatY }}"
                                    width="{{ $seatSize }}"
                                    height="{{ $seatSize }}"
                                    fill="{{ $seatColor }}"
                                    stroke="#ffffff"
                                    stroke-width="0.5"
                                    class="transition-all hover:opacity-80 {{ in_array($status, ['available', 'selected']) ? 'cursor-pointer' : 'cursor-not-allowed' }}"
                                    @if(in_array($status, ['available', 'selected']))
                                        @click.stop="$wire.toggleSeat('{{ $seatInstanceId }}')"
                                    @endif
                                    style="pointer-events: {{ in_array($status, ['available', 'selected']) ? 'all' : 'none' }};"
                                    title="{{ $sectorName }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ $seatInstance ? number_format($seatInstance->price, 0) : '' }} ₽"
                                />
                            @elseif(!$isHorizontal && $seatY < $sectorY + $sectorHeight - 5)
                                <rect
                                    x="{{ $seatX }}"
                                    y="{{ $seatY }}"
                                    width="{{ $seatSize }}"
                                    height="{{ $seatSize }}"
                                    fill="{{ $seatColor }}"
                                    stroke="#ffffff"
                                    stroke-width="0.5"
                                    class="transition-all hover:opacity-80 {{ in_array($status, ['available', 'selected']) ? 'cursor-pointer' : 'cursor-not-allowed' }}"
                                    @if(in_array($status, ['available', 'selected']))
                                        @click.stop="$wire.toggleSeat('{{ $seatInstanceId }}')"
                                    @endif
                                    style="pointer-events: {{ in_array($status, ['available', 'selected']) ? 'all' : 'none' }};"
                                    title="{{ $sectorName }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ $seatInstance ? number_format($seatInstance->price, 0) : '' }} ₽"
                                />
                            @endif
                        @endif
                    @endforeach
                @endforeach
            @endif
        @endif
    @endforeach
</g>


<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Заголовок события -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $event->title }}</h1>
            @if($event->venue)
                <p class="text-gray-600 mb-2">
                    <span class="font-medium">Место проведения:</span> {{ $event->venue->name }}
                </p>
            @endif
            @if($event->start_at)
                <p class="text-gray-600">
                    <span class="font-medium">Дата и время:</span> 
                    {{ $event->start_at->format('d.m.Y H:i') }}
                </p>
            @endif
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Обработчик ошибок Livewire -->
        <div x-data="{ showError: false, errorMessage: '' }" 
             @error.window="showError = true; errorMessage = $event.detail.message; setTimeout(() => showError = false, 5000)"
             x-show="showError"
             x-transition
             class="fixed top-4 right-4 z-50 max-w-md">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-lg" role="alert">
                <span class="block sm:inline" x-text="errorMessage"></span>
                <button @click="showError = false" class="absolute top-0 right-0 px-2 py-1 text-red-700 hover:text-red-900">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Схема стадиона -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Выберите места</h2>
                    
                    <!-- Фильтр по ценам -->
                    @if($seatsBySectorGrouped && $seatsBySectorGrouped->count() > 0)
                        @php
                            // Используем статистику для получения диапазонов цен
                            $allPrices = collect();
                            foreach($seatsBySectorGrouped as $sectorData) {
                                if (isset($sectorData['min_price']) && isset($sectorData['max_price'])) {
                                    // Добавляем минимальную и максимальную цену для каждого сектора
                                    $allPrices->push($sectorData['min_price']);
                                    if ($sectorData['min_price'] != $sectorData['max_price']) {
                                        $allPrices->push($sectorData['max_price']);
                                    }
                                }
                            }
                            $uniquePrices = $allPrices->unique()->sort()->values();
                        @endphp
                        @if($uniquePrices->count() > 0)
                            <div class="mb-6 flex flex-wrap gap-2 items-center">
                                <span class="text-sm text-gray-600 mr-2">Цена от:</span>
                                <button
                                    wire:click="$set('priceFilter', null)"
                                    class="px-3 py-1 rounded-full text-sm border-2 transition-colors {{ !$priceFilter ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                >
                                    Все
                                </button>
                                @foreach($uniquePrices->take(8) as $price)
                                    <button
                                        wire:click="$set('priceFilter', {{ $price }})"
                                        class="px-3 py-1 rounded-full text-sm border-2 transition-colors {{ $priceFilter == $price ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                    >
                                        {{ number_format($price, 0, ',', ' ') }} ₽
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <!-- Легенда -->
                    <div class="mb-6 flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-green-500 border border-gray-300 rounded"></div>
                            <span>Свободно</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-blue-500 border border-gray-300 rounded"></div>
                            <span>Выбрано</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-yellow-500 border border-gray-300 rounded"></div>
                            <span>Забронировано</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-red-500 border border-gray-300 rounded"></div>
                            <span>Занято</span>
                        </div>
                    </div>

                    <!-- Интерактивная SVG схема стадиона -->
                    <div class="relative bg-white rounded-lg overflow-hidden" style="min-height: 700px;" x-data="{ selectedSector: null }">
                        @php
                            // Подсчитываем количество секторов
                            $groupedCount = 0;
                            
                            if (isset($seatsBySectorGrouped) && $seatsBySectorGrouped) {
                                if (is_countable($seatsBySectorGrouped)) {
                                    $groupedCount = $seatsBySectorGrouped->count();
                                } elseif (is_array($seatsBySectorGrouped)) {
                                    $groupedCount = count($seatsBySectorGrouped);
                                }
                            }
                            
                            // Для отображения схемы достаточно только статистики по секторам
                            $canDisplay = $groupedCount > 0;
                        @endphp
                        
                        @if($canDisplay)
                            @php
                                // Получаем список секторов из статистики
                                $sectors = $seatsBySectorGrouped->keys()->sort()->values();
                                
                                // Распределяем секторы по трибунам
                                $northSectors = [];
                                $eastSectors = [];
                                $southSectors = [];
                                $westSectors = [];
                                
                                // Простое распределение: первые секторы - север, затем по часовой стрелке
                                $sectorCount = $sectors->count();
                                $perStand = ceil($sectorCount / 4);
                                
                                foreach($sectors as $index => $sectorName) {
                                    if ($index < $perStand) {
                                        $northSectors[] = $sectorName;
                                    } elseif ($index < $perStand * 2) {
                                        $eastSectors[] = $sectorName;
                                    } elseif ($index < $perStand * 3) {
                                        $southSectors[] = $sectorName;
                                    } else {
                                        $westSectors[] = $sectorName;
                                    }
                                }
                                
                                // Размеры
                                $svgWidth = 1000;
                                $svgHeight = 700;
                                $fieldX = $svgWidth / 2;
                                $fieldY = $svgHeight / 2;
                                $fieldWidth = 300;
                                $fieldHeight = 200;
                                $standDepth = 150;
                            @endphp
                            
                            <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="w-full h-full" style="min-height: 700px;">
                                <!-- Фон -->
                                <rect width="{{ $svgWidth }}" height="{{ $svgHeight }}" fill="#f8fafc"/>
                                
                                <!-- СЕВЕРНАЯ ТРИБУНА (сверху) -->
                                @php
                                    $northY = 50;
                                    $northHeight = $standDepth;
                                    $northX = $fieldX - $fieldWidth/2;
                                    $northWidth = $fieldWidth;
                                    $northSectorWidth = $northWidth / max(count($northSectors), 1);
                                @endphp
                                <g>
                                    <text x="{{ $fieldX }}" y="{{ $northY - 10 }}" text-anchor="middle" class="text-xs font-bold fill-gray-700" style="font-size: 11px;">СЕВЕРНАЯ ТРИБУНА</text>
                                    @foreach($northSectors as $idx => $sectorName)
                                        @php
                                            $sectorData = $seatsBySectorGrouped[$sectorName] ?? null;
                                            $hasSeats = $sectorData && isset($sectorData['total_seats']) && $sectorData['total_seats'] > 0;
                                            $hasAvailable = $sectorData && isset($sectorData['available']) && $sectorData['available'] > 0;
                                            $isFiltered = $priceFilter && $sectorData && (
                                                ($sectorData['min_price'] ?? 0) > $priceFilter || 
                                                ($sectorData['max_price'] ?? 0) < $priceFilter
                                            );
                                            $avgPrice = $sectorData ? (($sectorData['min_price'] ?? 0) + ($sectorData['max_price'] ?? 0)) / 2 : 0;
                                            $color = '#f59e0b'; // Оранжевый для северной трибуны
                                            if (!$hasAvailable || $isFiltered) $color = '#d1d5db';
                                            
                                            // Сектор кликабелен если есть места
                                            $isClickable = $hasSeats && !$isFiltered;
                                            
                                            $sectorX = $northX + ($idx * $northSectorWidth);
                                        @endphp
                                        @if(!$isFiltered)
                                            <rect
                                                x="{{ $sectorX }}"
                                                y="{{ $northY }}"
                                                width="{{ $northSectorWidth - 2 }}"
                                                height="{{ $northHeight }}"
                                                fill="{{ $color }}"
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
                                                    x="{{ $sectorX + $northSectorWidth/2 }}"
                                                    y="{{ $northY + 15 }}"
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
                                                    $seatSize = min(($northSectorWidth - 10) / max($maxSeatsPerRow, 1), ($northHeight - 25) / max($maxRows, 1), 6);
                                                    $seatSpacing = 1.5;
                                                    $padding = 5;
                                                @endphp
                                                @foreach($rowsInSector as $rowNum => $seatsInRow)
                                                    @php
                                                        $rowY = $northY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                                                        $seatsInRowSorted = $seatsInRow->sortBy('seat_number')->values();
                                                        $rowStartX = $sectorX + $padding + (($northSectorWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                                                    @endphp
                                                    @foreach($seatsInRowSorted as $seatIndex => $seat)
                                                        @php
                                                            $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                                            $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                                            $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                                            $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                                            
                                                            $seatX = $rowStartX + ($seatIndex * ($seatSize + $seatSpacing));
                                                            
                                                            $seatColor = '#d1d5db';
                                                            if ($seatInstanceId && !$isFiltered) {
                                                                if ($status === 'available') $seatColor = '#10b981';
                                                                elseif ($status === 'selected') $seatColor = '#3b82f6';
                                                                elseif ($status === 'reserved') $seatColor = '#f59e0b';
                                                                elseif ($status === 'sold') $seatColor = '#ef4444';
                                                            }
                                                        @endphp
                                                        @if($seatInstanceId && !$isFiltered && $rowY < $northY + $northHeight - 5)
                                                            <rect
                                                                x="{{ $seatX }}"
                                                                y="{{ $rowY }}"
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
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                </g>
                                
                                <!-- ВОСТОЧНАЯ ТРИБУНА (справа) -->
                                @php
                                    $eastX = $fieldX + $fieldWidth/2;
                                    $eastY = $fieldY - $fieldHeight/2;
                                    $eastWidth = $standDepth;
                                    $eastHeight = $fieldHeight;
                                    $eastSectorHeight = $eastHeight / max(count($eastSectors), 1);
                                @endphp
                                <g>
                                    <text x="{{ $eastX + $eastWidth/2 }}" y="{{ $fieldY - $fieldHeight/2 - 10 }}" text-anchor="middle" class="text-xs font-bold fill-gray-700" style="font-size: 11px;">ВОСТОЧНАЯ ТРИБУНА</text>
                                    @foreach($eastSectors as $idx => $sectorName)
                                        @php
                                            $sectorData = $seatsBySectorGrouped[$sectorName] ?? null;
                                            $hasSeats = $sectorData && isset($sectorData['total_seats']) && $sectorData['total_seats'] > 0;
                                            $hasAvailable = $sectorData && isset($sectorData['available']) && $sectorData['available'] > 0;
                                            $isFiltered = $priceFilter && $sectorData && (
                                                ($sectorData['min_price'] ?? 0) > $priceFilter || 
                                                ($sectorData['max_price'] ?? 0) < $priceFilter
                                            );
                                            $color = '#3b82f6'; // Синий для восточной трибуны
                                            if (!$hasAvailable || $isFiltered) $color = '#d1d5db';
                                            
                                            $isClickable = $hasSeats && !$isFiltered;
                                            
                                            $sectorY = $eastY + ($idx * $eastSectorHeight);
                                        @endphp
                                        @if(!$isFiltered)
                                            <rect
                                                x="{{ $eastX }}"
                                                y="{{ $sectorY }}"
                                                width="{{ $eastWidth }}"
                                                height="{{ $eastSectorHeight - 2 }}"
                                                fill="{{ $color }}"
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
                                                    x="{{ $eastX + $eastWidth/2 }}"
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
                                                    $maxSeatsPerRow = $rowsInSector->map(function($row) { return $row->count(); })->max() ?? 5;
                                                    $maxRows = $rowsInSector->count();
                                                    $seatSize = min(($eastWidth - 10) / max($maxSeatsPerRow, 1), ($eastSectorHeight - 25) / max($maxRows, 1), 6);
                                                    $seatSpacing = 1.5;
                                                    $padding = 5;
                                                @endphp
                                                @foreach($rowsInSector as $rowNum => $seatsInRow)
                                                    @php
                                                        $seatsInRowSorted = $seatsInRow->sortBy('seat_number')->values();
                                                        $rowStartY = $sectorY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                                                        $rowX = $eastX + $padding + (($eastWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                                                    @endphp
                                                    @foreach($seatsInRowSorted as $seatIndex => $seat)
                                                        @php
                                                            $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                                            $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                                            $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                                            $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                                            
                                                            $seatX = $rowX + ($seatIndex * ($seatSize + $seatSpacing));
                                                            $seatY = $rowStartY;
                                                            
                                                            $seatColor = '#d1d5db';
                                                            if ($seatInstanceId && !$isFiltered) {
                                                                if ($status === 'available') $seatColor = '#10b981';
                                                                elseif ($status === 'selected') $seatColor = '#3b82f6';
                                                                elseif ($status === 'reserved') $seatColor = '#f59e0b';
                                                                elseif ($status === 'sold') $seatColor = '#ef4444';
                                                            }
                                                        @endphp
                                                        @if($seatInstanceId && !$isFiltered && $seatY < $sectorY + $eastSectorHeight - 5)
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
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                </g>
                                
                                <!-- ЮЖНАЯ ТРИБУНА (снизу) -->
                                @php
                                    $southY = $fieldY + $fieldHeight/2;
                                    $southHeight = $standDepth;
                                    $southX = $fieldX - $fieldWidth/2;
                                    $southWidth = $fieldWidth;
                                    $southSectorWidth = $southWidth / max(count($southSectors), 1);
                                @endphp
                                <g>
                                    <text x="{{ $fieldX }}" y="{{ $southY + $southHeight + 20 }}" text-anchor="middle" class="text-xs font-bold fill-gray-700" style="font-size: 11px;">ЮЖНАЯ ТРИБУНА</text>
                                    @foreach($southSectors as $idx => $sectorName)
                                        @php
                                            $sectorData = $seatsBySectorGrouped[$sectorName] ?? null;
                                            $hasSeats = $sectorData && isset($sectorData['total_seats']) && $sectorData['total_seats'] > 0;
                                            $hasAvailable = $sectorData && isset($sectorData['available']) && $sectorData['available'] > 0;
                                            $isFiltered = $priceFilter && $sectorData && (
                                                ($sectorData['min_price'] ?? 0) > $priceFilter || 
                                                ($sectorData['max_price'] ?? 0) < $priceFilter
                                            );
                                            $color = '#f59e0b'; // Оранжевый для южной трибуны
                                            if (!$hasAvailable || $isFiltered) $color = '#d1d5db';
                                            
                                            $isClickable = $hasSeats && !$isFiltered;
                                            
                                            $sectorX = $southX + ($idx * $southSectorWidth);
                                        @endphp
                                        @if(!$isFiltered)
                                            <rect
                                                x="{{ $sectorX }}"
                                                y="{{ $southY }}"
                                                width="{{ $southSectorWidth - 2 }}"
                                                height="{{ $southHeight }}"
                                                fill="{{ $color }}"
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
                                                    x="{{ $sectorX + $southSectorWidth/2 }}"
                                                    y="{{ $southY + 15 }}"
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
                                                    $seatSize = min(($southSectorWidth - 10) / max($maxSeatsPerRow, 1), ($southHeight - 25) / max($maxRows, 1), 6);
                                                    $seatSpacing = 1.5;
                                                    $padding = 5;
                                                @endphp
                                                @foreach($rowsInSector as $rowNum => $seatsInRow)
                                                    @php
                                                        $rowY = $southY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                                                        $seatsInRowSorted = $seatsInRow->sortBy('seat_number')->values();
                                                        $rowStartX = $sectorX + $padding + (($southSectorWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                                                    @endphp
                                                    @foreach($seatsInRowSorted as $seatIndex => $seat)
                                                        @php
                                                            $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                                            $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                                            $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                                            $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                                            
                                                            $seatX = $rowStartX + ($seatIndex * ($seatSize + $seatSpacing));
                                                            
                                                            $seatColor = '#d1d5db';
                                                            if ($seatInstanceId && !$isFiltered) {
                                                                if ($status === 'available') $seatColor = '#10b981';
                                                                elseif ($status === 'selected') $seatColor = '#3b82f6';
                                                                elseif ($status === 'reserved') $seatColor = '#f59e0b';
                                                                elseif ($status === 'sold') $seatColor = '#ef4444';
                                                            }
                                                        @endphp
                                                        @if($seatInstanceId && !$isFiltered && $rowY < $southY + $southHeight - 5)
                                                            <rect
                                                                x="{{ $seatX }}"
                                                                y="{{ $rowY }}"
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
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                </g>
                                
                                <!-- ЗАПАДНАЯ ТРИБУНА (слева) -->
                                @php
                                    $westX = $fieldX - $fieldWidth/2 - $standDepth;
                                    $westY = $fieldY - $fieldHeight/2;
                                    $westWidth = $standDepth;
                                    $westHeight = $fieldHeight;
                                    $westSectorHeight = $westHeight / max(count($westSectors), 1);
                                @endphp
                                <g>
                                    <text x="{{ $westX + $westWidth/2 }}" y="{{ $fieldY - $fieldHeight/2 - 10 }}" text-anchor="middle" class="text-xs font-bold fill-gray-700" style="font-size: 11px;">ЗАПАДНАЯ ТРИБУНА</text>
                                    @foreach($westSectors as $idx => $sectorName)
                                        @php
                                            $sectorData = $seatsBySectorGrouped[$sectorName] ?? null;
                                            $hasSeats = $sectorData && isset($sectorData['total_seats']) && $sectorData['total_seats'] > 0;
                                            $hasAvailable = $sectorData && isset($sectorData['available']) && $sectorData['available'] > 0;
                                            $isFiltered = $priceFilter && $sectorData && (
                                                ($sectorData['min_price'] ?? 0) > $priceFilter || 
                                                ($sectorData['max_price'] ?? 0) < $priceFilter
                                            );
                                            $color = '#ef4444'; // Красный для западной трибуны
                                            if (!$hasAvailable || $isFiltered) $color = '#d1d5db';
                                            
                                            $isClickable = $hasSeats && !$isFiltered;
                                            
                                            $sectorY = $westY + ($idx * $westSectorHeight);
                                        @endphp
                                        @if(!$isFiltered)
                                            <rect
                                                x="{{ $westX }}"
                                                y="{{ $sectorY }}"
                                                width="{{ $westWidth }}"
                                                height="{{ $westSectorHeight - 2 }}"
                                                fill="{{ $color }}"
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
                                                    x="{{ $westX + $westWidth/2 }}"
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
                                                    $maxSeatsPerRow = $rowsInSector->map(function($row) { return $row->count(); })->max() ?? 5;
                                                    $maxRows = $rowsInSector->count();
                                                    $seatSize = min(($westWidth - 10) / max($maxSeatsPerRow, 1), ($westSectorHeight - 25) / max($maxRows, 1), 6);
                                                    $seatSpacing = 1.5;
                                                    $padding = 5;
                                                @endphp
                                                @foreach($rowsInSector as $rowNum => $seatsInRow)
                                                    @php
                                                        $seatsInRowSorted = $seatsInRow->sortBy('seat_number')->values();
                                                        $rowStartY = $sectorY + 20 + (($rowNum - 1) * ($seatSize + $seatSpacing) * 2);
                                                        $rowX = $westX + $padding + (($westWidth - ($seatsInRowSorted->count() * ($seatSize + $seatSpacing))) / 2);
                                                    @endphp
                                                    @foreach($seatsInRowSorted as $seatIndex => $seat)
                                                        @php
                                                            $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                                            $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                                            $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                                            $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                                            
                                                            $seatX = $rowX + ($seatIndex * ($seatSize + $seatSpacing));
                                                            $seatY = $rowStartY;
                                                            
                                                            $seatColor = '#d1d5db';
                                                            if ($seatInstanceId && !$isFiltered) {
                                                                if ($status === 'available') $seatColor = '#10b981';
                                                                elseif ($status === 'selected') $seatColor = '#3b82f6';
                                                                elseif ($status === 'reserved') $seatColor = '#f59e0b';
                                                                elseif ($status === 'sold') $seatColor = '#ef4444';
                                                            }
                                                        @endphp
                                                        @if($seatInstanceId && !$isFiltered && $seatY < $sectorY + $westSectorHeight - 5)
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
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                    </g>
                            
                            <!-- Поле в центре -->
                            <rect
                                x="{{ $fieldX - $fieldWidth/2 }}"
                                y="{{ $fieldY - $fieldHeight/2 }}"
                                width="{{ $fieldWidth }}"
                                height="{{ $fieldHeight }}"
                                fill="#22c55e"
                                fill-opacity="0.4"
                                stroke="#16a34a"
                                stroke-width="3"
                            />
                            <!-- Центральная линия -->
                            <line
                                x1="{{ $fieldX - $fieldWidth/2 }}"
                                y1="{{ $fieldY }}"
                                x2="{{ $fieldX + $fieldWidth/2 }}"
                                y2="{{ $fieldY }}"
                                stroke="#ffffff"
                                stroke-width="2"
                            />
                            <!-- Центральный круг -->
                            <circle
                                cx="{{ $fieldX }}"
                                cy="{{ $fieldY }}"
                                r="40"
                                fill="none"
                                stroke="#ffffff"
                                stroke-width="2"
                            />
                            <text
                                x="{{ $fieldX }}"
                                y="{{ $fieldY }}"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                class="text-sm font-semibold fill-white"
                                style="font-size: 14px; font-weight: bold;"
                            >
                                ПОЛЕ
                            </text>
                        </svg>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg mb-4">Схема мест для этого события пока не настроена</p>
                            
                            <!-- Отладочная информация -->
                            <div class="bg-gray-100 p-4 rounded-lg text-left max-w-md mx-auto mb-4">
                                <p class="text-sm font-semibold mb-2">Отладочная информация:</p>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    <li>Группированных секторов: {{ $groupedCount ?? 0 }}</li>
                                    <li>Экземпляров мест: {{ is_countable($seatInstances) ? $seatInstances->count() : (is_array($seatInstances) ? count($seatInstances) : 0) }}</li>
                                    @if($event && $event->venue)
                                        <li>Venue ID: {{ $event->venue->id }}</li>
                                        <li>Venue: {{ $event->venue->name }}</li>
                                        <li>Event ID: {{ $event->id }}</li>
                                    @endif
                                </ul>
                            </div>
                            
                            @if($event->venue)
                                <p class="text-gray-400 text-sm mb-2">
                                    Место проведения: {{ $event->venue->name }}
                                </p>
                                <p class="text-gray-400 text-sm mb-4">
                                    Для настройки схемы мест перейдите в 
                                    <a href="{{ route('admin.events.manage-seats', $event->id) }}" class="text-blue-600 hover:underline">
                                        Админ панель → События → Управление местами
                                    </a>
                                </p>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 max-w-md mx-auto">
                                    <p class="text-sm text-yellow-800 font-semibold mb-2">Инструкция:</p>
                                    <ol class="text-xs text-yellow-700 space-y-1 list-decimal list-inside">
                                        <li>Создайте схему мест для venue</li>
                                        <li>Создайте экземпляры мест для события</li>
                                        <li>Обновите страницу</li>
                                    </ol>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Панель выбранных мест -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Выбранные места</h2>
                    
                    @if(count($selectedSeats) > 0)
                        <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                            @foreach($selectedSeats as $seatInstanceId)
                                @php
                                    $seatInstance = $seatInstances[$seatInstanceId] ?? null;
                                @endphp
                                @if($seatInstance && $seatInstance->seat)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                Сектор {{ $seatInstance->seat->sector }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                Ряд {{ $seatInstance->seat->row_num }}, Место {{ $seatInstance->seat->seat_number }}
                                            </p>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900 ml-4">
                                            {{ number_format($seatInstance->price, 2) }} ₽
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-medium text-gray-900">Итого:</span>
                                <span class="text-2xl font-bold text-blue-600">{{ number_format($totalAmount, 2) }} ₽</span>
                            </div>
                        </div>

                        @auth
                            <button
                                type="button"
                                wire:click="createBooking"
                                wire:loading.attr="disabled"
                                wire:target="createBooking"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="createBooking">Забронировать</span>
                                <span wire:loading wire:target="createBooking" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Обработка...
                                </span>
                            </button>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors text-center"
                            >
                                Войти для бронирования
                            </a>
                        @endauth
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">Выберите сектор на схеме, затем места</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно выбора мест -->
    @if($showSeatModal && $selectedSector)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click.self="closeSeatModal">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
                <!-- Заголовок модального окна -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900">Выбор мест - Сектор {{ $selectedSector }}</h2>
                    <button
                        type="button"
                        wire:click="closeSeatModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Контент модального окна -->
                <div class="flex-1 overflow-y-auto p-6">
                    @if(isset($seatsBySector[$selectedSector]) && $seatsBySector[$selectedSector]->count() > 0)
                        <div class="space-y-4">
                            @foreach($seatsBySector[$selectedSector] as $rowNum => $seats)
                                <div class="flex items-center gap-3">
                                    <div class="w-20 text-sm text-gray-600 font-medium text-right flex-shrink-0">
                                        Ряд {{ $rowNum }}
                                    </div>
                                    <div class="flex gap-2 flex-wrap flex-1">
                                        @foreach($seats as $seat)
                                            @php
                                                $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                                $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                                $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                                $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                            @endphp
                                            
                                            @if($seatInstanceId && !$isFiltered)
                                                @if(in_array($status, ['reserved', 'sold', 'blocked', 'unknown']))
                                                    <div
                                                        @class([
                                                            'w-12 h-12 text-xs font-medium rounded border-2 flex items-center justify-center',
                                                            'bg-yellow-500 border-yellow-600 text-white opacity-75' => $status === 'reserved',
                                                            'bg-red-500 border-red-600 text-white opacity-75' => $status === 'sold',
                                                            'bg-gray-400 border-gray-500 text-white opacity-75' => $status === 'blocked',
                                                            'bg-gray-200 border-gray-300 text-gray-500' => $status === 'unknown',
                                                        ])
                                                        title="{{ $seat->sector }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ number_format($seatInstance->price, 2) }} ₽ (недоступно)"
                                                    >
                                                        {{ $seat->seat_number }}
                                                    </div>
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="toggleSeat('{{ $seatInstanceId }}')"
                                                        wire:loading.attr="disabled"
                                                        wire:key="modal-seat-{{ $seatInstanceId }}"
                                                        @class([
                                                            'w-12 h-12 text-xs font-medium rounded border-2 transition-all hover:scale-110 flex items-center justify-center cursor-pointer',
                                                            'bg-green-500 border-green-600 text-white hover:bg-green-600' => $status === 'available',
                                                            'bg-blue-500 border-blue-600 text-white hover:bg-blue-600' => $status === 'selected',
                                                        ])
                                                        title="{{ $seat->sector }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ number_format($seatInstance->price, 2) }} ₽"
                                                    >
                                                        {{ $seat->seat_number }}
                                                    </button>
                                                @endif
                                            @elseif(!$seatInstanceId)
                                                <div
                                                    class="w-12 h-12 text-xs font-medium rounded border-2 bg-gray-200 border-gray-300 text-gray-500 flex items-center justify-center"
                                                    title="Место не доступно"
                                                >
                                                    {{ $seat->seat_number }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">Места для этого сектора не найдены</p>
                        </div>
                    @endif
                </div>

                <!-- Футер модального окна -->
                <div class="border-t border-gray-200 p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Выбрано мест: <span class="font-semibold text-gray-900">{{ count($selectedSeats) }}</span></p>
                        <p class="text-sm text-gray-600">Итого: <span class="font-bold text-blue-600 text-lg">{{ number_format($totalAmount, 2) }} ₽</span></p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            wire:click="closeSeatModal"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

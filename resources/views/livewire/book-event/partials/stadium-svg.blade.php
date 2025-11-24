@php
    // Получаем список секторов из статистики
    $sectors = (isset($seatsBySectorGrouped) && $seatsBySectorGrouped) 
        ? $seatsBySectorGrouped->keys()->sort()->values() 
        : collect();
    
    // Распределяем секторы по трибунам
    $northSectors = [];
    $eastSectors = [];
    $southSectors = [];
    $westSectors = [];
    
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
    
    @include('livewire.book-event.partials.stadium-stand', [
        'sectors' => $northSectors,
        'standName' => 'СЕВЕРНАЯ ТРИБУНА',
        'standX' => $fieldX - $fieldWidth/2,
        'standY' => 50,
        'standWidth' => $fieldWidth,
        'standHeight' => $standDepth,
        'color' => '#f59e0b',
        'isHorizontal' => true
    ])
    
    @include('livewire.book-event.partials.stadium-stand', [
        'sectors' => $eastSectors,
        'standName' => 'ВОСТОЧНАЯ ТРИБУНА',
        'standX' => $fieldX + $fieldWidth/2,
        'standY' => $fieldY - $fieldHeight/2,
        'standWidth' => $standDepth,
        'standHeight' => $fieldHeight,
        'color' => '#3b82f6',
        'isHorizontal' => false
    ])
    
    @include('livewire.book-event.partials.stadium-stand', [
        'sectors' => $southSectors,
        'standName' => 'ЮЖНАЯ ТРИБУНА',
        'standX' => $fieldX - $fieldWidth/2,
        'standY' => $fieldY + $fieldHeight/2,
        'standWidth' => $fieldWidth,
        'standHeight' => $standDepth,
        'color' => '#f59e0b',
        'isHorizontal' => true
    ])
    
    @include('livewire.book-event.partials.stadium-stand', [
        'sectors' => $westSectors,
        'standName' => 'ЗАПАДНАЯ ТРИБУНА',
        'standX' => $fieldX - $fieldWidth/2 - $standDepth,
        'standY' => $fieldY - $fieldHeight/2,
        'standWidth' => $standDepth,
        'standHeight' => $fieldHeight,
        'color' => '#ef4444',
        'isHorizontal' => false
    ])
    
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


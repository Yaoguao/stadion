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
                            $allPrices = collect();
                            foreach($seatsBySectorGrouped as $sectorData) {
                                if (isset($sectorData['min_price']) && isset($sectorData['max_price'])) {
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
                            $groupedCount = 0;
                            if (isset($seatsBySectorGrouped) && $seatsBySectorGrouped) {
                                if (is_countable($seatsBySectorGrouped)) {
                                    $groupedCount = $seatsBySectorGrouped->count();
                                } elseif (is_array($seatsBySectorGrouped)) {
                                    $groupedCount = count($seatsBySectorGrouped);
                                }
                            }
                            $canDisplay = $groupedCount > 0;
                        @endphp
                        
                        @if($canDisplay)
                            @include('livewire.book-event.partials.stadium-svg')
                        @else
                            @include('livewire.book-event.partials.no-seats-message')
                        @endif
                    </div>
                </div>
            </div>

            <!-- Панель выбранных мест -->
            <div class="lg:col-span-1">
                @include('livewire.book-event.partials.selected-seats-panel')
            </div>
        </div>
    </div>

    <!-- Модальное окно выбора мест -->
    @if($showSeatModal && $selectedSector)
        @include('livewire.book-event.partials.seat-modal')
    @endif
</div>

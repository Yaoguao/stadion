<div>
    <!-- Hero Section with Stadium Image -->
    <div class="relative h-[500px] md:h-[600px] overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900 to-purple-900">
            @if($events->count() > 0 && $events->first()->venue)
                <!-- Placeholder for stadium image - можно заменить на реальное изображение -->
                <div class="w-full h-full bg-gradient-to-br from-gray-800 via-gray-700 to-gray-900 flex items-center justify-center">
                    <div class="text-center text-white z-10 relative px-4">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                            {{ $events->first()->venue->name ?? 'СТАДИОН БИЛЕТЫ' }}
                        </h1>
                        <p class="text-xl md:text-2xl mb-6 text-gray-200 drop-shadow-md">
                            Афиша мероприятий {{ date('Y') }} - {{ date('Y', strtotime('+1 year')) }}
                        </p>
                        <a href="#events" class="inline-block bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg">
                            Купить билеты
                        </a>
                    </div>
                </div>
            @else
                <div class="w-full h-full bg-gradient-to-br from-blue-900 via-purple-800 to-blue-900 flex items-center justify-center">
                    <div class="text-center text-white z-10 relative px-4">
                        <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                            СТАДИОН БИЛЕТЫ
                        </h1>
                        <p class="text-xl md:text-2xl mb-6 text-gray-200 drop-shadow-md">
                            Афиша мероприятий {{ date('Y') }} - {{ date('Y', strtotime('+1 year')) }}
                        </p>
                        <a href="#events" class="inline-block bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors shadow-lg">
                            Купить билеты
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Best Events Section -->
    @if($popularEvents->count() > 0)
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Лучшие мероприятия</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($popularEvents->take(2) as $event)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 border border-gray-200">
                            <div class="relative h-64 bg-gradient-to-br from-gray-800 to-gray-900">
                                @if($event->image_url)
                                    <img 
                                        src="{{ $event->image_url }}" 
                                        alt="{{ $event->title }}" 
                                        class="w-full h-full object-cover"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center {{ $event->image_url ? 'hidden' : '' }}" style="{{ $event->image_url ? '' : 'display: flex;' }}">
                                    <span class="text-white text-6xl">🎫</span>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                    <div class="flex justify-between items-end text-white">
                                        <div>
                                            <div class="text-sm opacity-90">{{ $event->start_at->format('d.m.Y, H:i') }}</div>
                                        </div>
                                        <a href="#" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $event->title }}</h3>
                                <p class="text-gray-600">
                                    {{ $event->venue->city ?? '' }}{{ $event->venue && $event->venue->city ? '. ' : '' }}{{ $event->venue->name ?? 'Место не указано' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- All Events Section -->
    <div id="events" class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-3xl font-bold text-gray-900">Афиша</h2>
                    <div class="flex gap-3">
                        <select wire:model.live="perPage" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="12">12 на странице</option>
                            <option value="24">24 на странице</option>
                            <option value="36">36 на странице</option>
                        </select>
                    </div>
                </div>
                <!-- Search -->
                <div class="max-w-md">
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Поиск событий..." 
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        >
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        @if($search)
                            <button 
                                wire:click="$set('search', '')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($events->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($events as $event)
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-all duration-300 border border-gray-200 flex flex-col">
                            <div class="w-full h-56 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center relative overflow-hidden">
                                @if($event->image_url)
                                    <img 
                                        src="{{ $event->image_url }}" 
                                        alt="{{ $event->title }}" 
                                        class="w-full h-full object-cover"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center {{ $event->image_url ? 'hidden' : '' }}" style="{{ $event->image_url ? '' : 'display: flex;' }}">
                                    <span class="text-white text-6xl">🎫</span>
                                </div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">{{ $event->title }}</h3>
                                @if($event->description)
                                    <p class="text-gray-600 text-xs mb-3 line-clamp-2">{{ $event->description }}</p>
                                @endif
                                <div class="space-y-2 mb-4 flex-1">
                                    <div class="flex items-center text-xs text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="font-medium">{{ $event->venue->name ?? 'Место не указано' }}</span>
                                        @if($event->venue && $event->venue->city)
                                            <span class="text-gray-500 ml-1">, {{ $event->venue->city }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-semibold text-gray-900">
                                            {{ $event->start_at->format('d.m.Y') }} в {{ $event->start_at->format('H:i') }}
                                        </span>
                                    </div>
                                    @if($event->end_at)
                                        <div class="flex items-center text-xs text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-gray-500">До {{ $event->end_at->format('H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($event->id)
                                    <a href="{{ route('events.book', $event->id) }}" class="block w-full bg-red-600 hover:bg-red-700 text-white text-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors mt-auto">
                                        Выбрать места
                                    </a>
                                @else
                                    <div class="block w-full bg-gray-400 text-white text-center px-4 py-2.5 rounded-lg text-sm font-medium cursor-not-allowed mt-auto">
                                        Выбрать места
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center py-4">
                    {{ $events->links() }}
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-lg shadow border border-gray-100">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">События не найдены</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($search)
                            Попробуйте изменить параметры поиска.
                        @else
                            В данный момент нет доступных событий.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Stadium Map Section -->
    @php
        $mainVenue = \App\Models\Venue::first();
    @endphp
    @if($mainVenue)
        <div class="bg-white py-12 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Адрес {{ $mainVenue->name }}:</h2>
                <div class="mb-4">
                    @if($mainVenue->address)
                        <p class="text-gray-700 text-lg mb-2">{{ $mainVenue->address }}</p>
                    @endif
                    @if($mainVenue->city)
                        <p class="text-gray-600">{{ $mainVenue->city }}</p>
                    @endif
                </div>
                
                <!-- Map Container -->
                <div class="w-full h-96 bg-gray-200 rounded-lg overflow-hidden border border-gray-300 relative">
                    @php
                        $address = $mainVenue->address ?? 'Москва';
                        $mapUrl = 'https://yandex.ru/map-widget/v1/?ll=37.622504%2C55.753215&z=15';
                        if ($address) {
                            $mapUrl .= '&text=' . urlencode($address);
                        }
                    @endphp
                    <iframe 
                        src="{{ $mapUrl }}" 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        allowfullscreen="true"
                        style="position:absolute; top:0; left:0;"
                    ></iframe>
                </div>
                
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="https://yandex.ru/maps/?pt={{ urlencode($mainVenue->address ?? '') }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Как добраться
                    </a>
                    <a href="https://taxi.yandex.ru/" target="_blank" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Доехать на такси
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Features Section -->
    <div class="bg-gray-50 py-12 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Почему выбирают нас</h2>
                <p class="text-gray-600 text-sm">Надежный сервис для покупки билетов</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                    <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Безопасная оплата</h3>
                    <p class="text-gray-600 text-sm">Защищенные платежи через проверенные платежные системы</p>
                </div>
                <div class="text-center bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                    <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Мгновенное подтверждение</h3>
                    <p class="text-gray-600 text-sm">Получайте билеты сразу после оплаты на email</p>
                </div>
                <div class="text-center bg-white p-6 rounded-lg border border-gray-100 shadow-sm">
                    <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Выбор мест</h3>
                    <p class="text-gray-600 text-sm">Выбирайте лучшие места на интерактивной карте зала</p>
                </div>
            </div>
        </div>
    </div>
</div>

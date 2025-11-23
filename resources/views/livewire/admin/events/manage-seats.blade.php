<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Управление местами</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Событие: <span class="font-medium">{{ $event->title }}</span>
                </p>
                <p class="text-sm text-gray-600">
                    Место: <span class="font-medium">{{ $event->venue->name ?? 'N/A' }}</span>
                </p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Назад к событиям
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 mb-1">Всего мест</div>
            <div class="text-2xl font-bold text-gray-900">{{ $seats->count() }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 mb-1">Экземпляров для события</div>
            <div class="text-2xl font-bold text-blue-600">{{ $seatInstancesCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 mb-1">Доступно</div>
            <div class="text-2xl font-bold text-green-600">{{ $availableCount }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 mb-1">Занято</div>
            <div class="text-2xl font-bold text-red-600">{{ $reservedCount + $soldCount }}</div>
        </div>
    </div>

    <!-- Действия -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Действия</h2>
        <div class="flex flex-wrap gap-4">
            @if($seats->count() === 0)
                <button
                    wire:click="$set('showGenerateForm', true)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Создать схему мест
                </button>
            @else
                <button
                    wire:click="generateSeatInstances"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Создать экземпляры мест для события
                </button>
                <button
                    wire:click="$set('showGenerateForm', true)"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Добавить места
                </button>
                <button
                    wire:click="regenerateSeats"
                    wire:confirm="Вы уверены? Это удалит все существующие места и создаст новые. Все активные бронирования будут потеряны!"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Пересоздать схему мест
                </button>
                <button
                    wire:click="deleteSeatInstances"
                    wire:confirm="Вы уверены, что хотите удалить все экземпляры мест для этого события?"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Удалить экземпляры мест
                </button>
                <button
                    wire:click="deleteAllSeats"
                    wire:confirm="ВНИМАНИЕ! Это удалит ВСЕ места venue и все экземпляры мест для всех событий этого venue. Вы уверены?"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    Удалить все места
                </button>
            @endif
        </div>
    </div>

    <!-- Форма генерации мест -->
    @if($showGenerateForm)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Генерация схемы мест</h2>
            <form wire:submit.prevent="generateSeats" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sectors" class="block text-sm font-medium text-gray-700 mb-1">
                            Количество секторов
                        </label>
                        <input
                            type="number"
                            id="sectors"
                            wire:model="sectors"
                            min="1"
                            max="20"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <label for="rows" class="block text-sm font-medium text-gray-700 mb-1">
                            Количество рядов в секторе
                        </label>
                        <input
                            type="number"
                            id="rows"
                            wire:model="rows"
                            min="1"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <label for="seatsPerRow" class="block text-sm font-medium text-gray-700 mb-1">
                            Мест в ряду
                        </label>
                        <input
                            type="number"
                            id="seatsPerRow"
                            wire:model="seatsPerRow"
                            min="1"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <label for="basePrice" class="block text-sm font-medium text-gray-700 mb-1">
                            Базовая цена (₽)
                        </label>
                        <input
                            type="number"
                            id="basePrice"
                            wire:model="basePrice"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Будет создано:</strong> {{ $sectors * $rows * $seatsPerRow }} мест
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        Цены будут автоматически рассчитаны в зависимости от сектора и ряда (VIP зона дороже, ближе к полю - дороже)
                    </p>
                </div>
                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        Создать места
                    </button>
                    <button
                        type="button"
                        wire:click="$set('showGenerateForm', false)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Список мест по секторам -->
    @if($seats->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Места по секторам</h2>
                <div class="space-y-6">
                    @foreach($seatsBySector as $sectorName => $seatsInSector)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-md font-semibold text-gray-900 mb-3">
                                Сектор {{ $sectorName }} ({{ $seatsInSector->count() }} мест)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                                @foreach($seatsInSector->take(30) as $seat)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                        <span class="text-gray-700">
                                            Ряд {{ $seat->row_num }}, Место {{ $seat->seat_number }}
                                        </span>
                                        <span class="font-medium text-gray-900">
                                            {{ number_format($seat->base_price, 2) }} ₽
                                        </span>
                                    </div>
                                @endforeach
                                @if($seatsInSector->count() > 30)
                                    <div class="text-gray-500 text-sm p-2">
                                        ... и еще {{ $seatsInSector->count() - 30 }} мест
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <p class="text-gray-500 text-lg mb-4">Схема мест еще не создана</p>
            <p class="text-gray-400 text-sm mb-6">
                Нажмите "Создать схему мест" чтобы начать
            </p>
            <button
                wire:click="$set('showGenerateForm', true)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors"
            >
                Создать схему мест
            </button>
        </div>
    @endif
</div>


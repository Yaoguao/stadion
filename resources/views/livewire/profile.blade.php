<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Личный кабинет</h1>
            <p class="mt-2 text-gray-600">Управление профилем, билетами и бронированиями</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Вкладки -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button
                        wire:click="switchTab('profile')"
                        class="@if($activeTab === 'profile') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                    >
                        Профиль
                    </button>
                    <button
                        wire:click="switchTab('tickets')"
                        class="@if($activeTab === 'tickets') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                    >
                        Мои билеты
                    </button>
                    <button
                        wire:click="switchTab('bookings')"
                        class="@if($activeTab === 'bookings') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                    >
                        Бронирования
                    </button>
                </nav>
            </div>
        </div>

        <!-- Контент вкладок -->
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Вкладка Профиль -->
            @if($activeTab === 'profile')
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Редактирование профиля</h2>
                    
                    <form wire:submit.prevent="updateProfile" class="space-y-6">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">ФИО</label>
                            <input
                                type="text"
                                id="full_name"
                                wire:model="full_name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('full_name') border-red-500 @enderror"
                            >
                            @error('full_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                            <input
                                type="tel"
                                id="phone"
                                wire:model="phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                                placeholder="+7 (999) 123-45-67"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Сохранить изменения
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Смена пароля</h3>
                        
                        <form wire:submit.prevent="updatePassword" class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Текущий пароль</label>
                                <input
                                    type="password"
                                    id="current_password"
                                    wire:model="current_password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                                >
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Новый пароль</label>
                                <input
                                    type="password"
                                    id="new_password"
                                    wire:model="new_password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('new_password') border-red-500 @enderror"
                                >
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтверждение пароля</label>
                                <input
                                    type="password"
                                    id="new_password_confirmation"
                                    wire:model="new_password_confirmation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                                >
                                    Изменить пароль
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Вкладка Билеты -->
            @if($activeTab === 'tickets')
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Мои билеты</h2>
                    
                    @if($tickets && $tickets->count() > 0)
                        <div class="space-y-4">
                            @foreach($tickets as $ticket)
                                @php
                                    $booking = $ticket->bookingItem->booking ?? null;
                                    $event = $booking->event ?? null;
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                {{ $event->title ?? 'Событие' }}
                                            </h3>
                                            @if($event && $event->venue)
                                                <p class="text-gray-600 mb-2">
                                                    <span class="font-medium">Место:</span> {{ $event->venue->name }}
                                                </p>
                                            @endif
                                            @if($event && $event->start_at)
                                                <p class="text-gray-600 mb-2">
                                                    <span class="font-medium">Дата и время:</span> 
                                                    {{ $event->start_at->format('d.m.Y H:i') }}
                                                </p>
                                            @endif
                                            @if($ticket->seat_label)
                                                <p class="text-gray-600 mb-2">
                                                    <span class="font-medium">Место:</span> {{ $ticket->seat_label }}
                                                </p>
                                            @endif
                                            <p class="text-sm text-gray-500">
                                                Статус: 
                                                @if($ticket->validated)
                                                    <span class="text-green-600 font-medium">Использован</span>
                                                @else
                                                    <span class="text-blue-600 font-medium">Активен</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="ml-6 text-right">
                                            @if($ticket->qr_code)
                                                <div class="mb-4">
                                                    <div class="bg-white p-4 border-2 border-gray-300 rounded inline-block">
                                                        <div class="w-32 h-32 bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                                            QR код
                                                        </div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 font-mono break-all max-w-xs">{{ $ticket->qr_code }}</p>
                                            @else
                                                <p class="text-sm text-gray-500">QR-код не сгенерирован</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $tickets->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">У вас пока нет билетов</p>
                            <a href="{{ route('home') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-700">
                                Посмотреть события →
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Вкладка Бронирования -->
            @if($activeTab === 'bookings')
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Мои бронирования</h2>
                    
                    @if($bookings && $bookings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Событие
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Дата события
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Сумма
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Статус
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Дата бронирования
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($bookings as $booking)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $booking->event->title ?? 'N/A' }}
                                                </div>
                                                @if($booking->event->venue)
                                                    <div class="text-sm text-gray-500">
                                                        {{ $booking->event->venue->name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($booking->event->start_at)
                                                        {{ $booking->event->start_at->format('d.m.Y H:i') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ number_format($booking->total_amount, 2) }} ₽
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($booking->status === 'paid')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Оплачено</span>
                                                @elseif($booking->status === 'pending')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Ожидает оплаты</span>
                                                @elseif($booking->status === 'cancelled')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Отменено</span>
                                                @elseif($booking->status === 'expired')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Истекло</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($booking->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $booking->created_at->format('d.m.Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $bookings->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">У вас пока нет бронирований</p>
                            <a href="{{ route('home') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-700">
                                Посмотреть события →
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>


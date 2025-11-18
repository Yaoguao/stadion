<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Редактировать пользователя</h1>
                <p class="text-sm text-gray-600 mt-1">Обновите информацию о пользователе</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Назад к списку
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($user)
        <!-- Form -->
        <div class="bg-white rounded-lg shadow">
            <form wire:submit="update">
                <div class="p-6 space-y-6">
                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Полное имя <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="full_name"
                            wire:model.blur="full_name" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('full_name') border-red-500 @enderror"
                            placeholder="Введите полное имя"
                        >
                        @error('full_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email"
                            wire:model.blur="email" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                            placeholder="user@example.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Телефон
                        </label>
                        <input 
                            type="text" 
                            id="phone"
                            wire:model.blur="phone" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                            placeholder="+7 (999) 123-45-67"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password (optional) -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Новый пароль
                            <span class="text-gray-500 text-xs font-normal">(оставьте пустым, чтобы не менять)</span>
                        </label>
                        <input 
                            type="password" 
                            id="password"
                            wire:model.blur="password" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                            placeholder="Минимум 8 символов"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    @if($password)
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Подтверждение пароля <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation"
                                wire:model.blur="password_confirmation" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password_confirmation') border-red-500 @enderror"
                                placeholder="Повторите пароль"
                            >
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Роли
                        </label>
                        <div class="space-y-2">
                            @forelse($roles as $role)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedRoles"
                                        value="{{ $role->id }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $role->display_name ?? $role->name }}
                                        @if($role->description)
                                            <span class="text-gray-500">({{ $role->description }})</span>
                                        @endif
                                    </span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Роли не найдены</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            <strong>Дата регистрации:</strong> {{ $user->created_at->format('d.m.Y H:i') }}
                        </p>
                        @if($user->updated_at)
                            <p class="text-sm text-gray-600 mt-1">
                                <strong>Последнее обновление:</strong> {{ $user->updated_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <a 
                        href="{{ route('admin.users.index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Отмена
                    </a>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">Загрузка данных пользователя...</p>
        </div>
    @endif
</div>

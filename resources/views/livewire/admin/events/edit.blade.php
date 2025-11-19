<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Редактировать событие</h1>
                <p class="text-sm text-gray-600 mt-1">Обновите информацию о событии</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-gray-600 hover:text-gray-900">
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

    @if($event)
        <!-- Form -->
        <div class="bg-white rounded-lg shadow">
            <form wire:submit="update">
                <div class="p-6 space-y-6">
                    <!-- Venue -->
                    <div>
                        <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Место проведения <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="venue_id"
                            wire:model.blur="venue_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('venue_id') border-red-500 @enderror"
                        >
                            <option value="">Выберите место проведения</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}" {{ $venue->id === $event->venue_id ? 'selected' : '' }}>
                                    {{ $venue->name }} @if($venue->city)({{ $venue->city }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('venue_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Название события <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title"
                            wire:model.blur="title" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                            placeholder="Введите название события"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Описание
                        </label>
                        <textarea 
                            id="description"
                            wire:model.blur="description" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                            placeholder="Введите описание события"
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_at" class="block text-sm font-medium text-gray-700 mb-2">
                            Дата и время начала <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="start_at"
                            wire:model.blur="start_at" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_at') border-red-500 @enderror"
                        >
                        @error('start_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_at" class="block text-sm font-medium text-gray-700 mb-2">
                            Дата и время окончания <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="datetime-local" 
                            id="end_at"
                            wire:model.blur="end_at" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_at') border-red-500 @enderror"
                        >
                        @error('end_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image URL -->
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL изображения
                        </label>
                        <input 
                            type="url" 
                            id="image_url"
                            wire:model.blur="image_url" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('image_url') border-red-500 @enderror"
                            placeholder="https://example.com/image.jpg"
                        >
                        @error('image_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($image_url)
                            <div class="mt-2">
                                <img src="{{ $image_url }}" alt="Preview" class="h-32 w-32 object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <!-- Published -->
                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="is_published"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">
                                Опубликовать событие
                            </span>
                        </label>
                    </div>

                    <!-- Event Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">
                            <strong>Дата создания:</strong> {{ $event->created_at->format('d.m.Y H:i') }}
                        </p>
                        @if($event->updated_at)
                            <p class="text-sm text-gray-600 mt-1">
                                <strong>Последнее обновление:</strong> {{ $event->updated_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($event->bookings)
                            <p class="text-sm text-gray-600 mt-1">
                                <strong>Бронирований:</strong> {{ $event->bookings->count() }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <div>
                        <a 
                            href="{{ route('admin.events.manage-seats', $event->id) }}" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium inline-flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            Управление местами
                        </a>
                    </div>
                    <div class="flex gap-3">
                        <a 
                            href="{{ route('admin.events.index') }}" 
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
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">Загрузка данных события...</p>
        </div>
    @endif
</div>

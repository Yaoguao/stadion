<div>
    <!-- Header with Actions -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">События</h1>
            <p class="text-sm text-gray-600 mt-1">Управление событиями системы</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            + Создать событие
        </a>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-4 items-center">
            <div class="flex-1">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Поиск по названию или описанию..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Статус:</label>
                <select wire:model.live="filterPublished" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Все</option>
                    <option value="published">Опубликованные</option>
                    <option value="unpublished">Неопубликованные</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">На странице:</label>
                <select wire:model.live="perPage" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($events->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Событие
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Место проведения
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Дата начала
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Дата окончания
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Статус
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Бронирований
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Действия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($event->image_url)
                                            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-12 w-12 rounded-lg object-cover mr-3">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                                <span class="text-gray-400 text-xs">Нет фото</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $event->title }}
                                            </div>
                                            @if($event->description)
                                                <div class="text-sm text-gray-500 line-clamp-1">
                                                    {{ Str::limit($event->description, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $event->venue->name ?? 'Не указано' }}
                                    </div>
                                    @if($event->venue && $event->venue->city)
                                        <div class="text-sm text-gray-500">{{ $event->venue->city }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $event->start_at ? $event->start_at->format('d.m.Y H:i') : '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $event->end_at ? $event->end_at->format('d.m.Y H:i') : '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($event->is_published)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Опубликовано
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Не опубликовано
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $event->bookings_count ?? $event->bookings->count() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.events.manage-seats', $event->id) }}" 
                                           class="text-green-600 hover:text-green-900" title="Управление местами">
                                            Места
                                        </a>
                                        <button 
                                            wire:click="togglePublish('{{ $event->id }}', {{ $event->is_published ? 'true' : 'false' }})"
                                            class="text-{{ $event->is_published ? 'yellow' : 'green' }}-600 hover:text-{{ $event->is_published ? 'yellow' : 'green' }}-900">
                                            {{ $event->is_published ? 'Снять' : 'Опубликовать' }}
                                        </button>
                                        <a href="{{ route('admin.events.edit', $event->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            Редактировать
                                        </a>
                                        <button 
                                            wire:click="deleteEvent('{{ $event->id }}')"
                                            wire:confirm="Вы уверены, что хотите удалить это событие?"
                                            class="text-red-600 hover:text-red-900">
                                            Удалить
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $events->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">События не найдены</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($search)
                        Попробуйте изменить параметры поиска.
                    @else
                        Начните с создания нового события.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

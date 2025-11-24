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
    
    @if(isset($event) && $event && $event->venue)
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


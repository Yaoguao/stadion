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


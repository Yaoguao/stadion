<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click.self="closeSeatModal">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
        <!-- Заголовок модального окна -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Выбор мест - Сектор {{ $selectedSector }}</h2>
            <button
                type="button"
                wire:click="closeSeatModal"
                class="text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Контент модального окна -->
        <div class="flex-1 overflow-y-auto p-6">
            @if(isset($seatsBySector[$selectedSector]) && $seatsBySector[$selectedSector]->count() > 0)
                <div class="space-y-4">
                    @foreach($seatsBySector[$selectedSector] as $rowNum => $seats)
                        <div class="flex items-center gap-3">
                            <div class="w-20 text-sm text-gray-600 font-medium text-right flex-shrink-0">
                                Ряд {{ $rowNum }}
                            </div>
                            <div class="flex gap-2 flex-wrap flex-1">
                                @foreach($seats as $seat)
                                    @php
                                        $seatInstance = $seat->seatInstances->where('event_id', $event->id)->first();
                                        $seatInstanceId = $seatInstance ? $seatInstance->id : null;
                                        $status = $seatInstanceId ? $this->getSeatStatus($seatInstanceId) : 'unknown';
                                        $isFiltered = $priceFilter && $seatInstance && $seatInstance->price != $priceFilter;
                                    @endphp
                                    
                                    @if($seatInstanceId && !$isFiltered)
                                        @if(in_array($status, ['reserved', 'sold', 'blocked', 'unknown']))
                                            <div
                                                @class([
                                                    'w-12 h-12 text-xs font-medium rounded border-2 flex items-center justify-center',
                                                    'bg-yellow-500 border-yellow-600 text-white opacity-75' => $status === 'reserved',
                                                    'bg-red-500 border-red-600 text-white opacity-75' => $status === 'sold',
                                                    'bg-gray-400 border-gray-500 text-white opacity-75' => $status === 'blocked',
                                                    'bg-gray-200 border-gray-300 text-gray-500' => $status === 'unknown',
                                                ])
                                                title="{{ $seat->sector }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ number_format($seatInstance->price, 2) }} ₽ (недоступно)"
                                            >
                                                {{ $seat->seat_number }}
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="toggleSeat('{{ $seatInstanceId }}')"
                                                wire:loading.attr="disabled"
                                                wire:key="modal-seat-{{ $seatInstanceId }}"
                                                @class([
                                                    'w-12 h-12 text-xs font-medium rounded border-2 transition-all hover:scale-110 flex items-center justify-center cursor-pointer',
                                                    'bg-green-500 border-green-600 text-white hover:bg-green-600' => $status === 'available',
                                                    'bg-blue-500 border-blue-600 text-white hover:bg-blue-600' => $status === 'selected',
                                                ])
                                                title="{{ $seat->sector }} - Ряд {{ $seat->row_num }} - Место {{ $seat->seat_number }} - {{ number_format($seatInstance->price, 2) }} ₽"
                                            >
                                                {{ $seat->seat_number }}
                                            </button>
                                        @endif
                                    @else
                                        <div
                                            class="w-12 h-12 text-xs font-medium rounded border-2 bg-gray-200 border-gray-300 text-gray-500 flex items-center justify-center"
                                            title="Место не доступно"
                                        >
                                            {{ $seat->seat_number }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">Места для этого сектора не найдены</p>
                </div>
            @endif
        </div>

        <!-- Футер модального окна -->
        <div class="border-t border-gray-200 p-6 flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Выбрано мест: <span class="font-semibold text-gray-900">{{ count($selectedSeats) }}</span></p>
                <p class="text-sm text-gray-600">Итого: <span class="font-bold text-blue-600 text-lg">{{ number_format($totalAmount, 2) }} ₽</span></p>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    wire:click="closeSeatModal"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>


<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage = 15;
    public $filterStatus = '';
    public $filterProvider = '';

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterProvider' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterProvider()
    {
        $this->resetPage();
    }

    public function render()
    {
        $paymentRepository = app(PaymentRepositoryInterface::class);
        
        if ($this->filterStatus) {
            $payments = $paymentRepository->getByStatus($this->filterStatus);
            // Конвертируем коллекцию в пагинатор
            $payments = new \Illuminate\Pagination\LengthAwarePaginator(
                $payments->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->perPage),
                $payments->count(),
                $this->perPage,
                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } elseif ($this->filterProvider) {
            $payments = $paymentRepository->getByProvider($this->filterProvider);
            // Конвертируем коллекцию в пагинатор
            $payments = new \Illuminate\Pagination\LengthAwarePaginator(
                $payments->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->perPage),
                $payments->count(),
                $this->perPage,
                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $payments = $paymentRepository->paginate($this->perPage);
        }

        return view('livewire.admin.payments.index', [
            'payments' => $payments,
        ])->layout('layouts.admin');
    }
}

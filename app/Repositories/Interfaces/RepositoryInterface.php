<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by ID.
     */
    public function find(string $id, array $columns = ['*']): ?Model;

    /**
     * Find a record or fail.
     */
    public function findOrFail(string $id, array $columns = ['*']): Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     */
    public function update(string $id, array $data): bool;

    /**
     * Delete a record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}


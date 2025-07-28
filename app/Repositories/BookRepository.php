<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;

class BookRepository
{
    protected $model;

    public function __construct(Book $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Book
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?Book
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): bool
    {
        return $this->find($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }
}
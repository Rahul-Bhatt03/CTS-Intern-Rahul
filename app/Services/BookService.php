<?php

namespace App\Services;

use App\Repositories\BookRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Book;

class BookService
{
protected $bookRepository;

public function __construct(BookRepository $bookRepository)
{
    $this->bookRepository = $bookRepository;
}

public function getAllBooks(): Collection
{
    return $this->bookRepository->all();
}
public function getBook(int $id): ?Book
{
    return $this->bookRepository->find($id);
}

public function createBook(array $data): Book
{
 try{
    $book=$this->bookRepository->create($data);
    return ['success'=>true,'data'=>$book];
 }catch(\Exception $e){
    return ['success'=>false,'error'=>$e->getMessage()];
 }

}

public function updateBook(int $id,array $data): bool
{
    return $this->bookRepository->update($id,$data);
}

public function deleteBook(int $id):bool{
    try{
        return $this->bookRepository->delete($id);
    }
    catch(\Exception $e){
        return ['success'=>false,'error'=>$e->getMessage()];
    }
}

}
?>
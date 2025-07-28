<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Repositories\BookRepository;
use App\Services\BookService;
use App\Http\Requests\BookRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// use Illuminate\Http\Request;

class BookController extends Controller
{
    protected BookService $bookService;

public function __construct(BookService $bookService){
    $this->bookService=$bookService;
}

public function index():View
{
    $books=$this->bookService->getAllBooks();
    return view('book.index',compact('books'));
}

public function create():View
{
    return view('books.create');
}

public function store(BookRequest $request):RedirectResponse
{
    $result->$this->bookService->createBook($request->validated());
    return $result['success']?redirect()->route('books.index')->with('success','Book created '):back()->with('error',$result['message']);
}

public function show(int $id):View{
    $book=$this->bookService->getBook($id);
    return view('books.show',compact('book'));
}

public function edit(int $id):view(){
    $book=$this->bookService->getBook($id);
    return view('books.edit',compact('book'));
}

public function update(BookRequest $request,int $id):RedirectResponse{
    $success=$this->bookService->updateBook($id,$request->validated());
    return $success?redirect()->route('books.index')->with('success','Book updated'):back()->with('error','Something went wrong');
}

public function delete(int $id):RedirectResponse{
    $success=$this=>bookService->deleteBook($id);
    return $success?redirect()->route('books.index')->with('success','Book deleted'):back()->with('error','Something went wrong');
}

}


?>

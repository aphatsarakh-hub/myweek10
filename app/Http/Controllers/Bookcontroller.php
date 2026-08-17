<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BookController extends Controller
{
    private function getBooks()
    {
        return Session::get('books', [
            ['id' => 1, 'title' => 'Laravel เบื้องต้น', 'author' => 'ผู้เขียน A', 'year' => 2566],
            ['id' => 2, 'title' => 'PHP ขั้นสูง', 'author' => 'ผู้เขียน B', 'year' => 2567],
        ]);
    }

    public function index()
    {
        $books = $this->getBooks();
        return view('books.index', compact('books'));
    }

    public function create()
    {
        return view('books.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'  => 'required|string|max:150',
            'author' => 'required|string|max:100',
            'year'   => 'required|integer|min:2400|max:2600',
        ]);

        $books = $this->getBooks();
        $validated['id'] = count($books) + 1;
        $books[] = $validated;
        Session::put('books', $books);

        return redirect()->route('books.index')->with('success', 'เพิ่มหนังสือเรียบร้อยแล้ว');
    }
}
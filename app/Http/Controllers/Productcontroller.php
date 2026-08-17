<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{
    // ข้อมูลจำลอง (ภายหลังค่อยเปลี่ยนเป็น Database ได้)
    private function getProducts()
    {
        return Session::get('products', [
            ['id' => 1, 'sn' => 'SN12345678', 'name' => 'สินค้า A', 'price' => 150, 'stock' => 20],
            ['id' => 2, 'sn' => 'SN87654321', 'name' => 'สินค้า B', 'price' => 300, 'stock' => 5],
        ]);
    }

    public function index()
    {
        $products = $this->getProducts();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sn'    => 'required|string|max:50',
            'name'  => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $products = $this->getProducts();
        $validated['id'] = count($products) + 1;
        $products[] = $validated;
        Session::put('products', $products);

        return redirect()->route('products.index')->with('success', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }
}
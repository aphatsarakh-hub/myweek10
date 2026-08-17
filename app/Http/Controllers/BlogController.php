<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $blogs = Blog::when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('blog', compact('blogs'));
    }

    public function blog2(Request $request)
    {
        $blogs = Blog::orderBy('id', 'asc')->paginate(15);
        return view('blog2', compact('blogs'));
    }

    public function delete($id)
    {
        Blog::destroy($id);
        return redirect()->back()->with('success', 'ลบบทความเรียบร้อยแล้ว');
    }

    public function changeStatus($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->status = ($blog->status == 'published' || $blog->status == '1' || $blog->status === true) ? 'draft' : 'published';
        $blog->save();

        return redirect()->back()->with('success', 'เปลี่ยนสถานะเรียบร้อยแล้ว');
    }

    public function create()
    {
        return view('from');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:150',
            'content' => 'required|string|min:10',
        ], [
            'title.required'   => 'กรุณากรอกชื่อบทความ',
            'content.required' => 'กรุณากรอกเนื้อหา',
            'content.min'      => 'เนื้อหาต้องมีอย่างน้อย 10 ตัวอักษร',
        ]);

        Blog::create([
            'title'   => $validated['title'],
            'content' => $validated['content'],
            'status'  => 'draft',
        ]);

        return redirect()->route('from')->with('success', 'บันทึกบทความเรียบร้อยแล้ว');
    }
}
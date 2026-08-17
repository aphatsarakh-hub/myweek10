<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    function blogs(){
    $blogs= DB::table('blogs')->get();
 
    return view ('blogs',compact ('blogs'));
}



function abouts(){
    $name ="Aphatsara Khaemadan";
    $data = "8 พฤศจิกายน 2547";
    return view('abouts', compact('name','data'));
}

function create()
{
    return view("form");
}

function insert(Request $request)
{
   $request->validate([
    'title' => 'required|max:50',
    'content' => 'required',
],
[
    'title.required' => 'กรุณาระบุชื่อบทความ'   ,
    'title.max' => 'ชื่อบทความต้องไม่เกิน 50 ตัวอักษร',
    'content.required' => 'กรุณาระบุเนื้อหาบทความ'
]);   
 $data=[
   "title"   => $request->title,
    "content" => $request->content,
 ];
 DB::table('blogs')->insert($data);
 return redirect('/blogs');
}


function delete($id){
DB::table('blogs')->where('id', $id)->delete();
return redirect()->back();
}
function change($id)
{

    $blog = DB::table('blogs')->where('id', $id)->first();

    if (!$blog) {
        return redirect('/blog2');
    }

    if ($blog->status == 'published') {
        $newStatus = 'draft';
    } else {
        $newStatus = 'published';
    }

    DB::table('blogs')->where('id', $id)->update([
            'status' => $newStatus
        ]);
    return redirect('/blog2');
}
function edit($id)
{

    $blog = DB::table('blogs')->where('id', $id)->first();
    return view('edit', compact ('blog'));
}
public function update(Request $request, $id)
{
    DB::table('blogs')->where('id', $id)->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

    return redirect('/blog2');
}
}
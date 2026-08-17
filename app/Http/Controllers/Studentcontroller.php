<?php

namespace App\Http\Controllers;

class StudentController extends Controller
{
    public function show($id)
    {
        return view('student', ['id' => $id]);
    }
}
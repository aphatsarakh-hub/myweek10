<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ClaimController;

Route::get('/', function () {
    return view('index');
})->name('home');


Route::get('/about', [AdminController::class, 'about'])
    ->name('about');


Route::get('/blog', [BlogController::class, 'blog2'])
    ->name('blog');

Route::get('/blog2', [BlogController::class, 'blog2'])
    ->name('blog2');


Route::get('/from', [AdminController::class, 'from'])
    ->name('from');

Route::get('/create', [AdminController::class, 'from'])
    ->name('create');

Route::post('/insert', [AdminController::class, 'insert'])
    ->name('insert');


Route::get('/claim/create', [ClaimController::class, 'create'])
    ->name('claim.create');


Route::get('/test-db', function () {

    try {

        DB::connection()->getPdo();

        return "เชื่อมต่อฐานข้อมูลสำเร็จ : "
            . DB::connection()->getDatabaseName();

    } catch (\Exception $e) {

        return "เชื่อมต่อฐานข้อมูลไม่สำเร็จ : "
            . $e->getMessage();
    }

})->name('test-db');


Route::get('/student/{id}', function ($id) {

    return view('student', [
        'id' => $id
    ]);

})->name('student.profile');


Route::get('/delete/{id}', [BlogController::class, 'delete'])
    ->name('blog.delete');

Route::get('/change-status/{id}', [BlogController::class, 'changeStatus'])
    ->name('blog.change-status');


Route::fallback(function () {

    return 'ไม่พบหน้าเว็บ';

});

Route::get('/delete/{id}', [AdminController::class, 'delete'])->name('delete');
Route::get('/change/{id}', [AdminController::class, 'change'])->name('change');
Route::get('/edit/{id}', [AdminController::class, 'edit'])->name('edit');
Route::post('/update/{id}', [AdminController::class, 'update'])->name('update');


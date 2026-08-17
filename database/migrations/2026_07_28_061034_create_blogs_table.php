<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::create('blogs', function (Blueprint $table) {   // ← เติม s
        $table->id();
        $table->string('title', 255);
        $table->text('content');
        $table->string('status')->default('draft');          // ← จุดที่ 2 แก้ตรงนี้ด้วย
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('blogs');   // ← เติม s
}
};
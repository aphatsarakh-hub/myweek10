<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_claims', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number');
            $table->string('email');
            $table->text('issue_description');
            $table->enum('urgency_level', ['low', 'medium', 'high']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_claims');
    }
};
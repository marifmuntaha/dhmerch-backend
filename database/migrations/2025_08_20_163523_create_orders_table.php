<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('whatsappId');
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('productId');
            $table->string('size');
            $table->string('arm');
            $table->string('price');
            $table->enum('payment', ['1', '2'])->default('1');
            $table->enum('status', [1, 2, 3])->default(1)->comment('1. Belum Bayar, 2. Lunas, 3. Sudah diambil');
            $table->string('reference')->nullable();
            $table->string('payCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

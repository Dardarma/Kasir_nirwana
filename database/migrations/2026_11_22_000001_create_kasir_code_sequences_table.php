<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasir_code_sequences', function (Blueprint $table) {
            $table->date('tanggal')->primary();
            $table->unsignedSmallInteger('last_number')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasir_code_sequences');
    }
};

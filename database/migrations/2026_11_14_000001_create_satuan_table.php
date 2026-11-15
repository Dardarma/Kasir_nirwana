<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->boolean('is_satuan_terkecil')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('satuan')->restrictOnDelete();
            $table->decimal('faktor', 15, 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan');
    }
};
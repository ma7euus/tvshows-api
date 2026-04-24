<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('id_integration')->unique();
            $table->string('name', 265)->nullable();
            $table->string('type', 265)->nullable();
            $table->string('language', 265)->nullable();
            $table->string('status', 265)->nullable();
            $table->integer('runtime')->nullable();
            $table->integer('average_runtime')->nullable();
            $table->string('official_site', 265)->nullable();
            $table->decimal('rating', 5, 2)->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};

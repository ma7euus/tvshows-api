<?php

use App\Enums\TvMazeImportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tvmaze_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status')->default(TvMazeImportStatus::PENDING->value)->index();
            $table->unsignedInteger('current_page')->default(0);
            $table->unsignedInteger('processed_pages')->default(0);
            $table->unsignedInteger('processed_shows')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tvmaze_imports');
    }
};

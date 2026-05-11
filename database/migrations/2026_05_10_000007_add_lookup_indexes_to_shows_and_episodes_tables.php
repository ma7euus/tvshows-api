<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->index('name', 'shows_name_lookup_index');
            $table->index('id_integration', 'shows_id_integration_lookup_index');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->index('id_integration', 'episodes_id_integration_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            $table->dropIndex('shows_name_lookup_index');
            $table->dropIndex('shows_id_integration_lookup_index');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropIndex('episodes_id_integration_lookup_index');
        });
    }
};

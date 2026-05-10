<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'user'],
            [
                'id' => '1d4d45d8-8d79-4aaf-95dc-0bc96de6ba55',
                'password' => Hash::make('user'),
                'role' => Role::USER->value,
                'enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('users')
            ->where('username', 'user')
            ->delete();
    }
};

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (!User::where('email', 'admin@qrgenerator.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@qrgenerator.com',
                'password' => Hash::make('admin123'), // Default password, should be changed
                'is_admin' => true,
            ]);
        }
    }
}

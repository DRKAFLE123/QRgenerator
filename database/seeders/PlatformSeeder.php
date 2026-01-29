<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = config('platforms');

        foreach ($platforms as $key => $data) {
            \App\Models\Platform::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $data['label'],
                    'icon' => $data['icon'],
                    'placeholder' => $data['placeholder'] ?? null,
                    'url_prefix' => $data['url_prefix'] ?? null,
                    'type' => $data['type'] ?? 'url',
                    'is_active' => true,
                ]
            );
        }
    }
}

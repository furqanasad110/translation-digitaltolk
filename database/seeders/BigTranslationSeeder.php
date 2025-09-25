<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BigTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $total = 100_000;  // total records to insert
        $chunk = 1_000;  // insert per chunk
        $iterations = ceil($total / $chunk);

        $this->command->info("Seeding {$total} translations in chunks of {$chunk}...");

        for ($i = 1; $i <= $iterations; $i++) {
            $translations = Translation::factory()
                ->count($chunk)
                ->make()  // create in-memory models
                ->toArray();  // convert to array for bulk insert

            // Convert 'tags' to JSON string for bulk insert
            $translations = array_map(function ($t) {
                if (isset($t['tags'])) {
                    $t['tags'] = json_encode($t['tags']);
                }
                return $t;
            }, $translations);

            Translation::insert($translations);  // bulk insert

            $this->command->info("Inserted chunk {$i}/{$iterations}");
        }

        $this->command->info('Seeding completed!');
    }
}

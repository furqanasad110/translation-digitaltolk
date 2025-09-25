<?php

namespace Tests\Feature;

use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected function seedSmallSet()
    {
        // small set for functional correctness
        Translation::insert([
            [
                'key' => 'k1', 'locale' => 'en', 'content' => 'c1', 'context' => 'web', 'tags' => json_encode(['web']), 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'k2', 'locale' => 'en', 'content' => 'c2', 'context' => 'mobile', 'tags' => json_encode(['mobile']), 'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    public function test_export_returns_expected_structure_and_is_cached()
    {
        $this->seedSmallSet();

        // ensure cache clean
        Cache::flush();

        $resp1 = $this
            ->getJson('/api/export/en.json')
            ->assertStatus(200)
            ->json();

        // first time: not from cache, but response must be array and contain keys
        $this->assertIsArray($resp1);
        $this->assertCount(2, $resp1);

        // second call should hit cache; we measure time (should be much faster)
        $start = microtime(true);
        $resp2 = $this->getJson('/api/export/en.json')->assertStatus(200)->json();
        $elapsed = (microtime(true) - $start) * 1000;
        // cached response should be cheap (< 100 ms). Adjust if needed.
        $this->assertLessThan(200, $elapsed, "Cached export took too long: {$elapsed} ms");
        $this->assertEquals($resp1, $resp2);
    }
}

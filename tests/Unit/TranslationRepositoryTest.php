<?php

namespace Tests\Unit;

use App\Models\Translation;
use App\Repositories\TranslationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_key_locale_context_and_tag()
    {
        Translation::insert([
            ['key' => 't1', 'locale' => 'en', 'content' => 'a', 'context' => 'web', 'tags' => json_encode(['web']), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 't2', 'locale' => 'fr', 'content' => 'b', 'context' => 'mobile', 'tags' => json_encode(['mobile']), 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'tek', 'locale' => 'en', 'content' => 'c', 'context' => 'web', 'tags' => json_encode(['web', 'mobile']), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $repo = new TranslationRepository();

        // search by prefix key
        $res = $repo->search(['key' => 't', 'locale' => 'en'], 10);
        $this->assertNotEmpty($res);
        $this->assertTrue($res->count() >= 1);

        // search by tag
        $res2 = $repo->search(['tag' => 'mobile'], 10);
        $this->assertNotEmpty($res2);
        $found = false;
        foreach ($res2 as $r) {
            if (in_array('mobile', $r->tags ?? []))
                $found = true;
        }
        $this->assertTrue($found);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TranslationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): string
    {
        $user = User::factory()->create();
        return $user->createToken('test')->plainTextToken;
    }

    public function test_store_translation_and_prevent_duplicate()
    {
        $token = $this->authenticate();

        $payload = [
            'key' => 'welcome_message',
            'locale' => 'en',
            'content' => 'Welcome!',
            'context' => 'web',
            'tags' => ['mobile', 'web'],
        ];

        // create
        $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/translations', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['key' => 'welcome_message', 'locale' => 'en']);

        // duplicate: same key+locale+context -> validation error (422)
        $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/translations', $payload)
            ->assertStatus(422);
    }

    public function test_update_by_key_and_show_search()
    {
        $token = $this->authenticate();

        // create two translations with same key but different locales/contexts
        Translation::create([
            'key' => 'greet',
            'locale' => 'en',
            'content' => 'Hello',
            'context' => 'web',
            'tags' => ['web']
        ]);

        Translation::create([
            'key' => 'greet',
            'locale' => 'fr',
            'content' => 'Bonjour',
            'context' => 'mobile',
            'tags' => ['mobile']
        ]);

        // update by key: must include locale to identify record
        $updatePayload = [
            'locale' => 'en',
            'content' => 'Hello, updated',
        ];

        $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/translations/greet', $updatePayload)
            ->assertStatus(200)
            ->assertJsonFragment(['content' => 'Hello, updated']);

        // show by key with locale query
        $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/translations/greet?locale=en')
            ->assertStatus(200)
            ->assertJsonFragment(['content' => 'Hello, updated']);

        // search by tag
        $resp = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/translations?tag=mobile')
            ->assertStatus(200);

        $this->assertStringContainsString('greet', $resp->getContent());
    }
}

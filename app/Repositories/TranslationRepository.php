<?php

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class TranslationRepository
{
    /**
     * Create a new translation
     *
     * @param array $data
     * @return Translation
     */
    public function create(array $data): Translation
    {
        $t = Translation::create($data);

        // Increment version for cache invalidation
        Cache::increment('translations_version');

        return $t;
    }

    /**
     * Update a translation by its key + locale + context
     *
     * @param string $key
     * @param array $data
     * @return Translation
     *
     * @throws ModelNotFoundException
     */
    public function updateByKey(string $key, array $data): Translation
    {
        $locale = $data['locale'] ?? null;
        $context = $data['context'] ?? null;

        $translation = Translation::where('key', $key)
            ->first();

        if (!$translation) {
            throw new ModelNotFoundException("Translation with key '{$key}' not found.");
        }

        $translation->update($data);

        // Invalidate cache
        Cache::increment('translations_version');

        return $translation;
    }

    /**
     * Find a translation by its key + locale + context
     *
     * @param string $key
     * @param string|null $locale
     * @param string|null $context
     * @return Translation|null
     */
    public function findByKey(string $key, ?string $locale = null, ?string $context = null): ?Translation
    {
        return Translation::select(['id', 'key', 'locale', 'content', 'context', 'tags'])
            ->where('key', $key)
            ->when($locale, fn($q) => $q->where('locale', $locale))
            ->when($context, fn($q) => $q->where('context', $context))
            ->first();
    }

    /**
     * Search translations with optional filters (paginated)
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters, int $perPage = 50)
    {
        $q = Translation::query()
            ->select(['id', 'key', 'locale', 'content', 'context', 'tags']);

        if (!empty($filters['key'])) {
            $q->where('key', 'LIKE', "{$filters['key']}%");  // prefix search
        }
        if (!empty($filters['locale'])) {
            $q->where('locale', $filters['locale']);
        }
        if (!empty($filters['context'])) {
            $q->where('context', $filters['context']);
        }
        if (!empty($filters['tag'])) {
            $q->whereJsonContains('tags', $filters['tag']);
        }

        return $q->simplePaginate($perPage);  // faster pagination
    }

    /**
     * Export translations for frontend (JSON) with caching
     *
     * @param string $locale
     * @param array|null $contexts
     * @return array
     */
    public function exportForLocale(string $locale, ?array $contexts = null): array
    {
        $version = Cache::get('translations_version', 0);
        $cacheKey = "export:{$locale}:v:{$version}:" . ($contexts ? md5(json_encode($contexts)) : 'all');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($locale, $contexts) {
            $query = Translation::query()
                ->select(['key', 'content', 'context', 'tags'])
                ->where('locale', $locale);

            if ($contexts) {
                $query->whereIn('context', $contexts);
            }

            // Use cursor for memory-efficient iteration
            $result = [];
            foreach ($query->orderBy('id')->cursor() as $t) {
                $result[] = [
                    'key' => $t->key,
                    'content' => $t->content,
                    'context' => $t->context,
                    'tags' => $t->tags,
                ];
            }

            return $result;
        });
    }
}

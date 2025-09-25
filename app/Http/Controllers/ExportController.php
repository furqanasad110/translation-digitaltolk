<?php

namespace App\Http\Controllers;

use App\Repositories\TranslationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExportController extends Controller
{
    private TranslationRepository $repo;

    public function __construct(TranslationRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @OA\Get(
     *     path="/export/{locale}.json",
     *     summary="Export translations for a given locale",
     * security={{"bearerAuth":{}}},
     *     tags={"Export"},
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="Locale code (e.g., en, fr, es)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="context",
     *         in="query",
     *         description="Comma-separated list of contexts to filter (optional)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JSON export of translations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="key", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="context", type="string"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function export(Request $request, string $locale)
    {
        $contexts = $request->query('context') ? explode(',', $request->query('context')) : null;

        try {
            $payload = $this->repo->exportForLocale($locale, $contexts);
            return response()->json($payload, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export translations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

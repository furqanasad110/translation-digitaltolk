<?php

namespace App\Http\Controllers;

use App\Repositories\TranslationRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Info(
 *     title="Translation Management API",
 *     version="1.0.0",
 *     description="API for managing translations with multiple locales and tags."
 *
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local API server"
 * )
 *   @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class TranslationController extends Controller
{
    private TranslationRepository $repo;

    public function __construct(TranslationRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @OA\Post(
     *     path="/translations",
     *     summary="Create a new translation",
     *     security={{"bearerAuth":{}}},
     *     tags={"Translations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key","locale","content"},
     *             @OA\Property(property="key", type="string"),
     *             @OA\Property(property="locale", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="context", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Translation created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'key' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('translations')->where(fn($query) => $query
                        ->where('locale', $request->locale)
                        ->where('context', $request->context ?? null)),
                ],
                'locale' => 'required|string|max:5',
                'content' => 'required|string',
                'context' => 'nullable|string|max:100',
                'tags' => 'nullable|array',
            ]);

            $translation = $this->repo->create($data);
            return response()->json($translation, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create translation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/translations/{key}",
     *     summary="Update a translation by key",
     * security={{"bearerAuth":{}}},
     *     tags={"Translations"},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Translation key",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="locale", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="context", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Translation updated successfully"),
     *     @OA\Response(response=404, description="Translation not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, string $key)
    {
        try {
            $data = $request->validate([
                'locale' => 'required|string|max:5',
                'content' => 'sometimes|string',
                'context' => 'nullable|string|max:100',
                'tags' => 'nullable|array',
            ]);

            $translation = $this->repo->updateByKey($key, $data);

            return response()->json($translation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['message' => 'Translation not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update translation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/translations/{key}",
     *     summary="Get a translation by key",
     * security={{"bearerAuth":{}}},
     *     tags={"Translations"},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Translation key",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Locale of the translation",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="context",
     *         in="query",
     *         description="Context of the translation",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Translation retrieved successfully"),
     *     @OA\Response(response=404, description="Translation not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function show(Request $request, string $key)
    {
        try {
            $locale = $request->query('locale');
            $context = $request->query('context');

            $translation = $this->repo->findByKey($key, $locale, $context);

            if (!$translation) {
                return response()->json(['message' => 'Translation not found'], 404);
            }

            return response()->json($translation);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve translation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/translations",
     *     summary="Search translations by key, locale, context or tag",
     * security={{"bearerAuth":{}}},
     *     tags={"Translations"},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by key",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Filter by locale",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="context",
     *         in="query",
     *         description="Filter by context",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter by tag",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Translations retrieved successfully"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['key', 'locale', 'context', 'tag']);
            $translations = $this->repo->search($filters);

            return response()->json($translations);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch translations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

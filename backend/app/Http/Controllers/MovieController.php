<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieFavorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    /**
     * The authenticated user via the sanctum guard (null for guests).
     */
    private function currentUser(): ?User
    {
        return Auth::guard('sanctum')->user();
    }

    /**
     * Attach per-user is_favorite boolean to a movie payload.
     */
    private function presentMovie(Movie $movie, ?User $user): array
    {
        $data = $movie->toArray();

        $data['is_favorite'] = $user ? (bool) ($data['movie_favorites_exists'] ?? false) : false;

        unset($data['movie_favorites_exists']);

        return $data;
    }

    /**
     * Display a listing of featured movie soundtracks.
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $favoritesOnly = (bool) $request->boolean('favorites_only');

        // Ownership filters are meaningless without an authenticated user.
        if ($favoritesOnly && !$user) {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ]);
        }

        $query = Movie::query();

        if ($favoritesOnly) {
            $query->whereHas('movieFavorites', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($user) {
            $query->withExists([
                'movieFavorites' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $movies = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Movie $movie) => $this->presentMovie($movie, $user))
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $movies
        ]);
    }

    /**
     * Store a newly created movie soundtrack in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'soundtrack_title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'youtube_url' => 'required|string',
        ]);

        $youtubeId = $this->extractYoutubeId($validated['youtube_url']);

        if (!$youtubeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => [
                    'youtube_url' => ['Please enter a valid YouTube URL.']
                ]
            ], 422);
        }

        $validated['youtube_id'] = $youtubeId;

        $movie = Movie::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Movie soundtrack added successfully!',
            'data' => $movie
        ], 201);
    }

    /**
     * Update the specified movie soundtrack in storage.
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $validated = $request->validate([
            'movie_title' => 'required|string|max:255',
            'soundtrack_title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'youtube_url' => 'required|string',
        ]);

        $youtubeId = $this->extractYoutubeId($validated['youtube_url']);

        if (!$youtubeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => [
                    'youtube_url' => ['Please enter a valid YouTube URL.']
                ]
            ], 422);
        }

        $validated['youtube_id'] = $youtubeId;

        $movie->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Movie soundtrack updated successfully!',
            'data' => $movie
        ]);
    }

    /**
     * Remove the specified movie soundtrack from storage.
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Movie soundtrack deleted successfully'
        ]);
    }

    /**
     * Toggle favorite status for the authenticated user.
     */
    public function toggleFavorite(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);
        $user = $request->user();

        $existing = MovieFavorite::query()
            ->where('user_id', $user->id)
            ->where('movie_id', $movie->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
        } else {
            MovieFavorite::firstOrCreate([
                'user_id' => $user->id,
                'movie_id' => $movie->id,
            ]);
            $isFavorite = true;
        }

        $action = $isFavorite ? 'added to' : 'removed from';

        return response()->json([
            'status' => 'success',
            'message' => "\"{$movie->movie_title}\" {$action} favorites",
            'data' => [
                'is_favorite' => $isFavorite,
            ],
        ]);
    }

    /**
     * Helper to extract 11-character YouTube video ID.
     */
    private function extractYoutubeId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
        if (preg_match($pattern, trim($url), $matches)) {
            return $matches[1];
        }

        // Direct 11-char ID check if user inputs raw ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($url))) {
            return trim($url);
        }

        return null;
    }
}

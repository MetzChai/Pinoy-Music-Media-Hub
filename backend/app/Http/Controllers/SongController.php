<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\PlaylistItem;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    /**
     * The authenticated user via the sanctum guard (null for guests).
     */
    private function currentUser(): ?User
    {
        return Auth::guard('sanctum')->user();
    }

    /**
     * Attach per-user is_favorite / in_playlist booleans to a song payload.
     */
    private function presentSong(Song $song, ?User $user): array
    {
        $data = $song->toArray();

        // If audio_url is a relative local path, resolve it to public storage URL
        if (!empty($data['audio_url']) && !str_starts_with($data['audio_url'], 'http://') && !str_starts_with($data['audio_url'], 'https://')) {
            $path = ltrim($data['audio_url'], '/');
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, 8);
            }
            $data['audio_url'] = Storage::disk('public')->url($path);
        }

        $data['is_favorite'] = $user ? (bool) ($data['favorites_exists'] ?? false) : false;
        $data['in_playlist'] = $user ? (bool) ($data['playlist_items_exists'] ?? false) : false;

        unset($data['favorites_exists'], $data['playlist_items_exists']);

        return $data;
    }

    /**
     * Display a listing of the songs with search & filter support.
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $favoritesOnly = (bool) $request->boolean('favorites_only');
        $playlistOnly = (bool) $request->boolean('playlist_only');

        // Ownership filters are meaningless without an authenticated user.
        if (($favoritesOnly || $playlistOnly) && !$user) {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ]);
        }

        $query = Song::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        }

        if ($request->has('genre') && !empty($request->genre) && $request->genre !== 'All') {
            $query->where('genre', $request->genre);
        }

        if ($favoritesOnly) {
            $query->whereHas('favorites', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($playlistOnly) {
            $query->whereHas('playlistItems', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($user) {
            $query->withExists([
                'favorites' => fn ($q) => $q->where('user_id', $user->id),
                'playlistItems' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $songs = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Song $song) => $this->presentSong($song, $user))
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $songs,
        ]);
    }

    /**
     * Store a newly created song in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'duration' => 'nullable|string|max:20',
            'cover' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3|max:15360',
            'audio_url' => 'nullable|string',
        ]);

        if (empty($validated['duration'])) {
            $validated['duration'] = '4:00';
        }

        if ($request->hasFile('audio_file')) {
            $storedPath = $request->file('audio_file')->store('songs', 'public');
            $validated['audio_url'] = Storage::disk('public')->url($storedPath);
        }
        unset($validated['audio_file']);

        $song = Song::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Song added to catalog successfully!',
            'data' => $this->presentSong($song, $request->user()),
        ], 201);
    }

    /**
     * Display the specified song.
     */
    public function show($id)
    {
        $user = $this->currentUser();

        $query = Song::query();

        if ($user) {
            $query->withExists([
                'favorites' => fn ($q) => $q->where('user_id', $user->id),
                'playlistItems' => fn ($q) => $q->where('user_id', $user->id),
            ]);
        }

        $song = $query->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $this->presentSong($song, $user),
        ]);
    }

    /**
     * Update the specified song in storage.
     */
    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'artist' => 'sometimes|required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:20',
            'cover' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3|max:15360',
            'audio_url' => 'nullable|string',
        ]);

        if ($request->hasFile('audio_file')) {
            // Delete old stored file if it was in local public storage
            if (!empty($song->audio_url) && !str_starts_with($song->audio_url, 'http://www.soundhelix.com')) {
                $oldPath = ltrim(parse_url($song->audio_url, PHP_URL_PATH) ?: $song->audio_url, '/');
                if (str_starts_with($oldPath, 'storage/')) {
                    $oldPath = substr($oldPath, 8);
                }
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $storedPath = $request->file('audio_file')->store('songs', 'public');
            $validated['audio_url'] = Storage::disk('public')->url($storedPath);
        }
        unset($validated['audio_file']);

        $song->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Song updated successfully',
            'data' => $this->presentSong($song, $request->user()),
        ]);
    }

    /**
     * Remove the specified song from storage.
     */
    public function destroy($id)
    {
        $song = Song::findOrFail($id);

        if (!empty($song->audio_url) && !str_starts_with($song->audio_url, 'http://www.soundhelix.com')) {
            $oldPath = ltrim(parse_url($song->audio_url, PHP_URL_PATH) ?: $song->audio_url, '/');
            if (str_starts_with($oldPath, 'storage/')) {
                $oldPath = substr($oldPath, 8);
            }
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $song->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Song deleted successfully',
        ]);
    }

    /**
     * Toggle favorite status for the authenticated user.
     */
    public function toggleFavorite(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $user = $request->user();

        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('song_id', $song->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
        } else {
            Favorite::firstOrCreate([
                'user_id' => $user->id,
                'song_id' => $song->id,
            ]);
            $isFavorite = true;
        }

        $action = $isFavorite ? 'added to' : 'removed from';

        return response()->json([
            'status' => 'success',
            'message' => "\"{$song->title}\" {$action} favorites",
            'data' => [
                'is_favorite' => $isFavorite,
            ],
        ]);
    }

    /**
     * Toggle playlist inclusion for the authenticated user.
     */
    public function togglePlaylist(Request $request, $id)
    {
        $song = Song::findOrFail($id);
        $user = $request->user();

        $existing = PlaylistItem::query()
            ->where('user_id', $user->id)
            ->where('song_id', $song->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $inPlaylist = false;
        } else {
            PlaylistItem::firstOrCreate([
                'user_id' => $user->id,
                'song_id' => $song->id,
            ]);
            $inPlaylist = true;
        }

        $action = $inPlaylist ? 'added to' : 'removed from';

        return response()->json([
            'status' => 'success',
            'message' => "\"{$song->title}\" {$action} your playlist",
            'data' => [
                'in_playlist' => $inPlaylist,
            ],
        ]);
    }

    /**
     * Return overall stats for dashboard.
     *
     * Guests see global distinct counts; logged-in users see their own counts.
     * Response shape is identical for both.
     */
    public function stats()
    {
        $user = $this->currentUser();

        $totalSongs = Song::count();

        if ($user) {
            $favoriteCount = Favorite::where('user_id', $user->id)->count();
            $playlistCount = PlaylistItem::where('user_id', $user->id)->count();
        } else {
            $favoriteCount = Favorite::distinct()->count('song_id');
            $playlistCount = PlaylistItem::distinct()->count('song_id');
        }

        $genres = Song::select('genre')->distinct()->pluck('genre')->toArray();

        // Ensure 'All' is in genres array
        array_unshift($genres, 'All');
        $genres = array_values(array_unique($genres));

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_songs' => $totalSongs,
                'playlist_count' => $playlistCount,
                'favorite_count' => $favoriteCount,
                'genres' => $genres,
            ],
        ]);
    }
}

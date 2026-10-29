<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Movie;
use App\Models\PlaylistItem;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * List all users.
     */
    public function index(): JsonResponse
    {
        $users = User::orderBy('id')->get();

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    /**
     * Update a user (name and/or role). Role is assigned explicitly.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|string|in:user,admin',
        ]);

        $actor = $request->user();

        // Never let an admin demote themselves out of their own admin access.
        if ($user->id === $actor->id && array_key_exists('role', $validated) && $validated['role'] !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot demote yourself.',
            ], 403);
        }

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('role', $validated)) {
            $user->role = $validated['role'];
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a user (an admin cannot delete themselves).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Global counts for the admin dashboard.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => User::count(),
                'songs' => Song::count(),
                'movies' => Movie::count(),
                // rows are unique per (user_id, song_id) pair
                'favorites' => Favorite::count(),
                'favorites_distinct_users' => Favorite::distinct()->count('user_id'),
                'playlist_items' => PlaylistItem::count(),
            ],
        ]);
    }
}

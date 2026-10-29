<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SongUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);

        $adminEmail = config('auth.admin.email', 'admin@mymovieapp.com');
        $this->admin = User::where('email', $adminEmail)->first();

        $this->regularUser = User::create([
            'name' => 'Regular Listener',
            'email' => 'listener@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }

    public function test_admin_can_upload_mp3_and_create_song(): void
    {
        Storage::fake('public');

        $mp3File = UploadedFile::fake()->create('harana.mp3', 2048, 'audio/mpeg');

        $response = $this->actingAs($this->admin, 'sanctum')->post('/api/songs', [
            'title' => 'Harana',
            'artist' => 'Parokya ni Edgar',
            'genre' => 'OPM Rock',
            'duration' => '3:15',
            'audio_file' => $mp3File,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'Harana')
            ->assertJsonPath('data.artist', 'Parokya ni Edgar');

        $song = Song::where('title', 'Harana')->first();
        $this->assertNotNull($song);
        $this->assertNotEmpty($song->audio_url);

        $path = parse_url($song->audio_url, PHP_URL_PATH) ?: $song->audio_url;
        $relativePath = str_starts_with($path, '/storage/') ? substr($path, 9) : ltrim($path, '/');
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_regular_user_cannot_upload_song(): void
    {
        Storage::fake('public');
        $mp3File = UploadedFile::fake()->create('test.mp3', 1024, 'audio/mpeg');

        $response = $this->actingAs($this->regularUser, 'sanctum')->post('/api/songs', [
            'title' => 'Unauthorized Song',
            'artist' => 'Unknown',
            'genre' => 'OPM',
            'audio_file' => $mp3File,
        ]);

        $response->assertStatus(403);
    }

    public function test_rejects_invalid_file_extension(): void
    {
        Storage::fake('public');
        $fakeTxt = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/songs', [
            'title' => 'Invalid File Song',
            'artist' => 'Test Artist',
            'genre' => 'OPM',
            'audio_file' => $fakeTxt,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audio_file']);
    }

    public function test_rejects_file_exceeding_15mb(): void
    {
        Storage::fake('public');
        // 16 MB = 16384 KB (limit is 15360 KB)
        $largeMp3 = UploadedFile::fake()->create('huge.mp3', 16384, 'audio/mpeg');

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/songs', [
            'title' => 'Huge Song',
            'artist' => 'Test Artist',
            'genre' => 'OPM',
            'audio_file' => $largeMp3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audio_file']);
    }

    public function test_admin_can_update_song_and_replace_audio(): void
    {
        Storage::fake('public');

        $firstMp3 = UploadedFile::fake()->create('first.mp3', 1024, 'audio/mpeg');
        $createRes = $this->actingAs($this->admin, 'sanctum')->post('/api/songs', [
            'title' => 'Original Track',
            'artist' => 'Original Artist',
            'genre' => 'OPM',
            'audio_file' => $firstMp3,
        ]);
        $songId = $createRes->json('data.id');

        $secondMp3 = UploadedFile::fake()->create('replacement.mp3', 2048, 'audio/mpeg');
        $updateRes = $this->actingAs($this->admin, 'sanctum')->post("/api/songs/{$songId}", [
            'title' => 'Updated Track Title',
            'artist' => 'Original Artist',
            'genre' => 'OPM Pop',
            'audio_file' => $secondMp3,
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'Updated Track Title');

        $updatedSong = Song::find($songId);
        $this->assertSame('Updated Track Title', $updatedSong->title);
    }

    public function test_deleting_song_removes_uploaded_file_from_storage(): void
    {
        Storage::fake('public');

        $mp3File = UploadedFile::fake()->create('to_delete.mp3', 1024, 'audio/mpeg');
        $createRes = $this->actingAs($this->admin, 'sanctum')->post('/api/songs', [
            'title' => 'Song To Delete',
            'artist' => 'Ephemeral Artist',
            'genre' => 'OPM',
            'audio_file' => $mp3File,
        ]);
        $songId = $createRes->json('data.id');
        $audioUrl = $createRes->json('data.audio_url');

        $relativePath = ltrim(parse_url($audioUrl, PHP_URL_PATH) ?: $audioUrl, '/');
        if (str_starts_with($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, 8);
        }
        Storage::disk('public')->assertExists($relativePath);

        $deleteRes = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/songs/{$songId}");
        $deleteRes->assertStatus(200);

        $this->assertNull(Song::find($songId));
        Storage::disk('public')->assertMissing($relativePath);
    }

    public function test_existing_external_audio_url_songs_continue_to_work(): void
    {
        $song = Song::create([
            'title' => 'Classic External Track',
            'artist' => 'Legacy Band',
            'genre' => 'OPM Rock',
            'duration' => '4:20',
            'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        ]);

        $response = $this->getJson("/api/songs/{$song->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.audio_url', 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3');
    }
}

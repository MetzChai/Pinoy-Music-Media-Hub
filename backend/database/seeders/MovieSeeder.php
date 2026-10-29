<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = [
            [
                'movie_title' => 'That Thing Called Tadhana',
                'soundtrack_title' => 'Tadhana',
                'artist' => 'Up Dharma Down',
                'description' => 'A romantic comedy movie featuring the legendary hit song Tadhana.',
                'cover_image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80',
                'youtube_url' => 'https://www.youtube.com/watch?v=x49xV6X3L2A',
                'youtube_id' => 'x49xV6X3L2A',
            ],
            [
                'movie_title' => 'Kita Kita',
                'soundtrack_title' => 'Two Less Lonely People in the World',
                'artist' => 'KZ Tandingan',
                'description' => 'An emotional romance film highlighting iconic Filipino music and heartfelt comedy.',
                'cover_image' => 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=600&q=80',
                'youtube_url' => 'https://www.youtube.com/watch?v=R2rXf_OIsI4',
                'youtube_id' => 'R2rXf_OIsI4',
            ],
            [
                'movie_title' => 'I\'m Drunk, I Love You',
                'soundtrack_title' => 'Burnout / Migraine',
                'artist' => '3D / Moonstar88',
                'description' => 'A college music film centered on OPM indie band anthems and unrequited love.',
                'cover_image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=600&q=80',
                'youtube_url' => 'https://www.youtube.com/watch?v=LqUuIapfuhE',
                'youtube_id' => 'LqUuIapfuhE',
            ],
        ];

        foreach ($movies as $movieData) {
            Movie::updateOrCreate(
                ['movie_title' => $movieData['movie_title'], 'soundtrack_title' => $movieData['soundtrack_title']],
                $movieData
            );
        }
    }
}

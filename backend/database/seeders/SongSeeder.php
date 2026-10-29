<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Song;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $songs = [
            [
                'title' => 'Tadhana',
                'artist' => 'Up Dharma Down',
                'genre' => 'OPM',
                'duration' => '4:01',
                'cover' => 'https://i1.sndcdn.com/artworks-klRPLROGlkbVZu6X-hac0FQ-t500x500.jpg',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            ],
            [
                'title' => 'Mundo',
                'artist' => 'IV of Spades',
                'genre' => 'OPM',
                'duration' => '4:16',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLF2fCYmgPfNhgwCtNpY4epq0NCm8I_hux6JDRuTq0oA&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
            ],
            [
                'title' => 'Buwan',
                'artist' => 'Juan Karlos',
                'genre' => 'OPM',
                'duration' => '4:27',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJ0WDIh6Maz2F7yCEd7RK7lMhMb9fHrvMH98GurUdEqw&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
            ],
            [
                'title' => 'Raining in Manila',
                'artist' => 'Lola Amour',
                'genre' => 'OPM Pop',
                'duration' => '4:17',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRFwIGlC0cH6qQU0zebG5DIxn2l68w8o5Rq5Tf7sz9iOg&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3',
            ],
            [
                'title' => 'Kathang Isip',
                'artist' => 'Ben&Ben',
                'genre' => 'OPM Acoustic',
                'duration' => '4:17',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTap_FoINujsMRKNoOcPZYBi9TPgxEiGoyB3coGk_4pew&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3',
            ],
            [
                'title' => 'Leaves',
                'artist' => 'Ben&Ben',
                'genre' => 'OPM Acoustic',
                'duration' => '4:00',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS6h_FP8bYWW6_XNyEGq1KJ06b1HAwPqA6qCKaL_yB4RA&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3',
            ],
            [
                'title' => 'With a Smile',
                'artist' => 'Eraserheads',
                'genre' => 'OPM Rock',
                'duration' => '4:37',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRF7JcDs5SvHD2RO11cdM7f4eNsjrghjqeFtWmOuRLC0w&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3',
            ],
            [
                'title' => '214',
                'artist' => 'Rivermaya',
                'genre' => 'OPM Rock',
                'duration' => '4:35',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSy_8hnMqA4RHqTAKfvX4Nzt0KPjI_evaOSludBKZPcAQ&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3',
            ],
            [
                'title' => 'Migraine',
                'artist' => 'Moonstar88',
                'genre' => 'OPM Rock',
                'duration' => '4:03',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQbakfOpwvwjzwzzJPFoGdBCuNKOJxP8c7aZInscXZ5dw&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3',
            ],
            [
                'title' => 'Nobela',
                'artist' => 'Join The Club',
                'genre' => 'OPM Rock',
                'duration' => '4:14',
                'cover' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRdojML_O-EJDWnki4BdHKSP25hcrHirQYUSzvjMyOY4A&s=10',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3',
            ],
            [
                'title' => 'Ang Huling El Bimbo',
                'artist' => 'Eraserheads',
                'genre' => 'OPM Rock',
                'duration' => '7:27',
                'cover' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=500&q=80',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3',
            ],
            [
                'title' => 'Ere',
                'artist' => 'Juan Karlos',
                'genre' => 'OPM Pop',
                'duration' => '4:44',
                'cover' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=500&q=80',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3',
            ],
            [
                'title' => 'Pasilyo',
                'artist' => 'SunKissed Lola',
                'genre' => 'OPM Pop',
                'duration' => '4:30',
                'cover' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=500&q=80',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-13.mp3',
            ]
        ];

        foreach ($songs as $songData) {
            Song::updateOrCreate(
                ['title' => $songData['title'], 'artist' => $songData['artist']],
                $songData
            );
        }
    }
}

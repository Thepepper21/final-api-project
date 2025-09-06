<?php

namespace Database\Seeders;

use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        $disk = 'public';
        $baseDir = 'gallery';
        // akong ge usab
        if (!Storage::disk($disk)->exists($baseDir)) {
            Storage::disk($disk)->makeDirectory($baseDir);
        }

        $samples = [
            ['filename' => 'sample1.png', 'title' => 'Sample One', 'description' => 'Seeded image 1', 'mime' => 'image/png'],
            ['filename' => 'sample2.jpg', 'title' => 'Sample Two', 'description' => 'Seeded image 2', 'mime' => 'image/jpeg'],
            ['filename' => 'sample3.gif', 'title' => 'Sample Three', 'description' => 'Seeded image 3', 'mime' => 'image/gif'],
        ];

        foreach ($samples as $sample) {
            $path = $baseDir.'/'.$sample['filename'];

            if (!Storage::disk($disk)->exists($path)) {
                // Generate a tiny 1x1 pixel image for seeding without external APIs.
                $binary = self::generatePixel($sample['mime']);
                Storage::disk($disk)->put($path, $binary);
            }

            $size = Storage::disk($disk)->size($path);

            Image::updateOrCreate(
                ['path' => $path, 'disk' => $disk],
                [
                    'title' => $sample['title'],
                    'description' => $sample['description'],
                    'filename' => $sample['filename'],
                    'original_name' => $sample['filename'],
                    'mime_type' => $sample['mime'],
                    'size_bytes' => $size,
                ]
            );
        }
    }

    protected static function generatePixel(string $mime): string
    {
        // Minimal binary for 1x1 images in different formats using base64 inline data.
        $map = [
            'image/png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/awJb2cAAAAASUVORK5CYII=',
            'image/jpeg' => '/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxISEhIQEBAQEA8QDw8PDw8PDw8PDw8PFREWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OGxAQGy0lHyYtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAQMBIgACEQEDEQH/xAAbAAABBQEAAAAAAAAAAAAAAAAGAQIDBAUH//EADoQAAICAQIDBAYFBQAAAAAAAAECAxEEEiExQQUTImFxgZEykaGx0fAHFCNCUrLx8fEkM3KSYv/EABkBAQADAQEAAAAAAAAAAAAAAAABAgMEBf/EAB4RAQEAAgMAAwAAAAAAAAAAAAABAhExAxIhQWFR/9oADAMBAAIRAxEAPwD7xREQEREBERAREQEREBERAREQEREH/9k=',
            'image/gif' => 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ];

        $base64 = $map[$mime] ?? $map['image/png'];
        return base64_decode($base64);
    }
}



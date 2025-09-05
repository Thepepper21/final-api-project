<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $images = Image::query()->latest()->paginate(20);
        return response()->json($images);
    }

    public function show(Image $image)
    {
        return response()->json($image);
    }

    public function serve(Image $image): StreamedResponse
    {
        $disk = $image->disk;
        if (!Storage::disk($disk)->exists($image->path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($image->path, $image->original_name ?? $image->filename);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['required', 'file', 'image', 'max:5120'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('image');
        $disk = 'public';
        $path = $file->store('gallery', $disk);

        $image = Image::create([
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?? $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'disk' => $disk,
            'path' => $path,
        ]);

        return response()->json($image, 201);
    }

    public function update(Request $request, Image $image)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $image->fill($validated);
        $image->save();
        return response()->json($image);
    }

    public function destroy(Image $image)
    {
        Storage::disk($image->disk)->delete($image->path);
        $image->delete();
        return response()->json(['message' => 'Deleted']);
    }
}



<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    // POST /api/images  
    public function store(Request $request)
    {
        if (Image::where('owner_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Already own an image'], 409);
        }

        $request->validate([
            'file'  => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'], // до 5 МБ
            'title' => ['nullable', 'string', 'max:100'],
        ]);

        $url = $this->cloudinary()
            ->uploadApi()
            ->upload($request->file('file')->getRealPath(), ['folder' => 'pixelboris'])['secure_url'];

        $image = Image::create([
            'uploader_id' => $request->user()->id,
            'owner_id'    => $request->user()->id,
            'title'       => $request->title,
            'file_path'   => $url,
        ]);

        return response()->json($this->format($image), 201);
    }

    // GET /api/images
    public function index()
    {
        $images = Image::orderByDesc('value')->limit(50)->get();

        return response()->json($images->map(fn ($image) => $this->format($image)));
    }

    // GET /api/my-image
    public function mine(Request $request)
    {
        $image = Image::where('owner_id', $request->user()->id)->first();

        if ($image) {
            return response()->json($this->format($image));
        }

        return response()->json(null);
    }

    // GET /api/images/{id} 
    public function show($id)
    {
        $image = Image::findOrFail($id);

        return response()->json($this->format($image));
    }

    // POST /api/images/{id}  (JSON: { "title": "---" })
    public function update(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        if ($image->owner_id != $request->user()->id) {
            return response()->json(['message' => 'not ur image'], 403);
        }

        $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
        ]);

        $image->update(['title' => $request->title]);

        return response()->json($this->format($image));
    }

    // DELETE /api/images/{id}
    public function destroy(Request $request, $id)
    {
        $image = Image::findOrFail($id);

        if ($image->owner_id != $request->user()->id) {
            return response()->json(['message' => 'not ur image'], 403);
        }

        try {
            $publicId = $this->publicIdFromUrl($image->file_path);
            $this->cloudinary()->uploadApi()->destroy($publicId);
        } catch (\Throwable $e) {
            report($e);
        }

        $image->delete();

        return response()->json(null, 204);
    }

    private function cloudinary()
    {
        return new Cloudinary(config('services.cloudinary.url'));
    }

    // https://res.cloudinary.com/xxx/image/upload/v123/pixelboris/abc.jpg -> pixelboris/abc
    private function publicIdFromUrl($url)
    {
        preg_match('#/upload/(?:v\d+/)?(.+)\.[a-z0-9]+$#i', $url, $m);

        return $m[1];
    }

    private function format($image)
    {
        return [
            'id'           => $image->id,
            'owner_id'     => $image->owner_id,
            'title'        => $image->title,
            'url'          => $image->file_path,
            'clicks_count' => $image->clicks_count,
            'value'        => $image->value,
            'created_at'   => $image->created_at,
        ];
    }
}
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Listing;
use App\Models\Trade;
use App\Services\CloudinaryFiles;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'owner_id' => ['nullable', 'integer'],
        ]);

        $query = Image::query()
            ->with('owner')
            ->withCount(['listings as active_listings_count' => fn ($listings) => $listings->active()])
            ->latest('id');

        $search = $request->string('search')->trim()->value();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('username', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->integer('owner_id'));
        }

        return ImageResource::collection($query->paginate(20)->withQueryString());
    }

    public function destroy(Image $image, CloudinaryFiles $files): Response
    {
        DB::transaction(function () use ($image) {
            Trade::query()
                ->where('offered_image_id', $image->id)
                ->orWhere('requested_image_id', $image->id)
                ->delete();

            Listing::query()->where('image_id', $image->id)->delete();

            $image->delete();
        });

        $files->destroy($image->file_path);

        return response()->noContent();
    }
}

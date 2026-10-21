<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;

class ListingController extends Controller
{
    public function destroy(Listing $listing): ListingResource
    {
        abort_unless($listing->isActive(), 409, 'This listing is no longer active.');

        $listing->cancel();

        return new ListingResource($listing->load(['image', 'seller']));
    }
}

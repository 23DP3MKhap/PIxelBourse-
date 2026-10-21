<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Throwable;

class CloudinaryFiles
{
    public function destroy(string $url): void
    {
        if (! preg_match('#/upload/(?:v\d+/)?(.+)\.[a-z0-9]+$#i', $url, $matches)) {
            return;
        }

        try {
            (new Cloudinary(config('services.cloudinary.url')))->uploadApi()->destroy($matches[1]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

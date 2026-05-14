<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    public function storeOptimizedUpload(UploadedFile $file): string
    {
        $rawContents = file_get_contents($file->getRealPath());

        if ($rawContents === false) {
            $storedPath = $file->store('products', 'public');

            return $this->publicImageUrl($storedPath);
        }

        return $this->storeOptimizedContents($rawContents);
    }

    public function downloadAndStore(?string $url): ?string
    {
        if (! filled($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $response = Http::timeout(20)
            ->accept('image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8')
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        $contentType = strtolower((string) $response->header('content-type'));

        if ($contentType !== '' && ! str_contains($contentType, 'image/')) {
            return null;
        }

        return $this->storeOptimizedContents($response->body());
    }

    public function storeOptimizedContents(string $rawContents): string
    {
        if ($rawContents === '' || ! function_exists('imagecreatefromstring')) {
            return $this->storeRawContents($rawContents, 'jpg');
        }

        $sourceImage = @imagecreatefromstring($rawContents);

        if ($sourceImage === false) {
            return $this->storeRawContents($rawContents, 'jpg');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $maxDimension = 1800;
        $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $extension = function_exists('imagewebp') ? 'webp' : 'jpg';
        $filename = 'products/'.Str::uuid().'.'.$extension;

        ob_start();

        if ($extension === 'webp') {
            imagewebp($targetImage, null, 82);
        } else {
            imagejpeg($targetImage, null, 85);
        }

        $optimizedContents = (string) ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        Storage::disk('public')->put($filename, $optimizedContents);

        return $this->publicImageUrl($filename);
    }

    public function delete(?string $imagePath): void
    {
        if (! filled($imagePath) || filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return;
        }

        $publicPrefix = '/storage/';
        $normalizedPath = str_starts_with($imagePath, $publicPrefix)
            ? ltrim(Str::after($imagePath, $publicPrefix), '/')
            : ltrim(Str::after($imagePath, '/storage/'), '/');

        if ($normalizedPath !== '' && Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }

    private function storeRawContents(string $rawContents, string $extension): string
    {
        $filename = 'products/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($filename, $rawContents);

        return $this->publicImageUrl($filename);
    }

    private function publicImageUrl(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }
}

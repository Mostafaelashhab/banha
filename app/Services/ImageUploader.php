<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Compresses + stores images so the disk doesn't explode.
 *
 * - Resizes anything > MAX_WIDTH down (preserving aspect)
 * - Re-encodes as JPEG at quality 75 (kills metadata, drops 60-80% of size)
 * - Strips EXIF/orientation
 *
 * Typical 4MB phone photo → ~150-300KB on disk.
 */
class ImageUploader
{
    public const MAX_WIDTH        = 1200;
    public const JPEG_QUALITY     = 75;
    public const MAX_INPUT_BYTES  = 5 * 1024 * 1024;

    public static function store(UploadedFile $file, string $directory, ?string $oldUrl = null): ?string
    {
        if ($file->getSize() > self::MAX_INPUT_BYTES) {
            return null;
        }

        $mime = $file->getMimeType();
        $src  = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getPathname()),
            'image/png'  => @imagecreatefrompng($file->getPathname()),
            'image/webp' => @imagecreatefromwebp($file->getPathname()),
            default      => null,
        };
        if (! $src) return null;

        // Auto-orient JPEGs from phones (otherwise photos appear sideways)
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getPathname());
            if (! empty($exif['Orientation'])) {
                $src = self::applyOrientation($src, (int) $exif['Orientation']);
            }
        }

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w > self::MAX_WIDTH) {
            $newW = self::MAX_WIDTH;
            $newH = (int) round($h * (self::MAX_WIDTH / $w));
            $dst  = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        $relPath = $directory.'/'.Str::random(40).'.jpg';
        $tmpPath = sys_get_temp_dir().'/'.basename($relPath);
        imagejpeg($src, $tmpPath, self::JPEG_QUALITY);
        imagedestroy($src);

        Storage::disk('public')->put($relPath, file_get_contents($tmpPath));
        @unlink($tmpPath);

        // Delete old image if it was managed by us
        if ($oldUrl) self::delete($oldUrl);

        return '/storage/'.$relPath;
    }

    public static function delete(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/storage/')) return;
        $rel = ltrim(str_replace('/storage/', '', $url), '/');
        Storage::disk('public')->delete($rel);
    }

    private static function applyOrientation(\GdImage $img, int $orientation): \GdImage
    {
        return match ($orientation) {
            3 => imagerotate($img, 180, 0) ?: $img,
            6 => imagerotate($img, -90, 0) ?: $img,
            8 => imagerotate($img,  90, 0) ?: $img,
            default => $img,
        };
    }
}

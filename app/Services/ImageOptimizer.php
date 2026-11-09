<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Konversi file gambar yang diupload ke WebP berkualitas tinggi,
     * melakukan auto-rotate EXIF jika ada, dan resize proporsional jika resolusi terlalu besar.
     *
     * @return array{disk: string, path: string, mime_type: string, size_bytes: int}
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory = 'posts',
        string $disk = 'public',
        int $maxWidth = 1600,
        int $quality = 82
    ): array {
        try {
            $realPath = $file->getRealPath();
            $mime = $file->getMimeType() ?: $file->getClientMimeType();

            // Load GD image resource sesuai tipe mime
            $image = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
                'image/png' => @imagecreatefrompng($realPath),
                'image/webp' => @imagecreatefromwebp($realPath),
                default => null,
            };

            // Jika bukan format umum GD atau gagal dibaca, fallback simpan aslinya
            if (! $image) {
                $path = $file->store($directory, $disk);

                return [
                    'disk' => $disk,
                    'path' => $path,
                    'mime_type' => $mime,
                    'size_bytes' => $file->getSize(),
                ];
            }

            // Auto-rotate foto jika ada orientation EXIF dari kamera HP/kamera digital
            if (in_array($mime, ['image/jpeg', 'image/jpg'], true) && function_exists('exif_read_data')) {
                $exif = @exif_read_data($realPath);
                if (! empty($exif['Orientation'])) {
                    $image = match ($exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // Resize proporsional jika lebar melebihi $maxWidth (standar 1600px untuk layar retina/desktop)
            if ($origWidth > $maxWidth && $maxWidth > 0) {
                $targetWidth = $maxWidth;
                $targetHeight = (int) round(($origHeight / $origWidth) * $targetWidth);

                $resized = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);

                imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);
                $image = $resized;
            } else {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }

            // Simpan ke file temporary WebP
            $tmpFile = tempnam(sys_get_temp_dir(), 'webp_opt_') . '.webp';
            $success = imagewebp($image, $tmpFile, $quality);

            if ($success && file_exists($tmpFile)) {
                $filename = Str::random(40) . '.webp';
                $path = Storage::disk($disk)->putFileAs($directory, new File($tmpFile), $filename);
                @unlink($tmpFile);

                if ($path) {
                    return [
                        'disk' => $disk,
                        'path' => $path,
                        'mime_type' => 'image/webp',
                        'size_bytes' => (int) Storage::disk($disk)->size($path),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengoptimasi gambar ke WebP, fallback ke file asli: ' . $e->getMessage());
        }

        // Fallback aman jika terjadi error apapun
        $fallbackPath = $file->store($directory, $disk);

        return [
            'disk' => $disk,
            'path' => $fallbackPath,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_converts_uploaded_jpeg_to_optimized_webp(): void
    {
        Storage::fake('public');

        // Buat file JPEG palsu 100x100
        $tmpJpeg = tempnam(sys_get_temp_dir(), 'test_img_') . '.jpg';
        $im = imagecreatetruecolor(100, 100);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 50, 50));
        imagejpeg($im, $tmpJpeg, 90);

        $file = new UploadedFile($tmpJpeg, 'sample.jpg', 'image/jpeg', null, true);

        $optimizer = new ImageOptimizer();
        $result = $optimizer->optimizeAndStore($file, 'posts', 'public');

        $this->assertEquals('public', $result['disk']);
        $this->assertEquals('image/webp', $result['mime_type']);
        $this->assertStringEndsWith('.webp', $result['path']);
        $this->assertTrue(Storage::disk('public')->exists($result['path']));
        $this->assertGreaterThan(0, $result['size_bytes']);

        @unlink($tmpJpeg);
    }
}

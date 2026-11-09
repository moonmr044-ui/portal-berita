<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PHPStan bootstrap
|--------------------------------------------------------------------------
|
| Pest mengikat closure test ke Tests\TestCase saat runtime, tetapi PHPStan
| melihatnya sebagai PHPUnit\Framework\TestCase sehingga method HTTP Laravel
| (get/post/withSession/assertAuthenticatedAs/...) dilaporkan tidak ada.
| Stub di bawah memberi tahu PHPStan bahwa $this pada closure test adalah
| Tests\TestCase, tanpa mengubah kode aplikasi.
|
*/

if (! class_exists('PestBootstrapStub', false)) {
    /**
     * Kelas penanda agar PHPStan dapat menemukan tipe closure Pest.
     */
    final class PestBootstrapStub
    {
        //
    }
}

require_once __DIR__.'/../vendor/autoload.php';

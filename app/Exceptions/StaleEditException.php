<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika penyimpanan artikel gagal karena konflik versi
 * (optimistic locking). Artinya artikel telah diubah pihak lain
 * sejak form dibuka.
 */
final class StaleEditException extends RuntimeException
{
    public function __construct(
        public readonly int $postId,
        public readonly int $expectedVersion,
        public readonly int $actualVersion,
        string $message = '',
    ) {
        $default = sprintf(
            'Konflik edit: artikel #%d sudah diubah pihak lain (versi Anda %d, versi terkini %d). Muat ulang halaman.',
            $postId,
            $expectedVersion,
            $actualVersion,
        );

        parent::__construct($message !== '' ? $message : $default);
    }
}

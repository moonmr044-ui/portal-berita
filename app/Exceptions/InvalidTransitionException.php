<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\PostStatus;
use RuntimeException;

/**
 * Dilempar ketika sebuah transisi status artikel tidak terdaftar
 * pada matriks transisi PostStatus.
 */
final class InvalidTransitionException extends RuntimeException
{
    public function __construct(
        public readonly PostStatus $from,
        public readonly PostStatus $to,
        public readonly ?int $postId = null,
        string $message = '',
    ) {
        $default = $postId === null
            ? sprintf('Transisi status tidak valid: %s -> %s.', $from->value, $to->value)
            : sprintf('Transisi status tidak valid pada artikel #%d: %s -> %s.', $postId, $from->value, $to->value);

        parent::__construct($message !== '' ? $message : $default);
    }

    /**
     * Status asal transisi yang ditolak.
     */
    public function from(): PostStatus
    {
        return $this->from;
    }

    /**
     * Status tujuan transisi yang ditolak.
     */
    public function to(): PostStatus
    {
        return $this->to;
    }
}

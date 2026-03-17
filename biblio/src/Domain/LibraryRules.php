<?php

namespace App\Domain;

final class LibraryRules
{
    public const int MAX_ACTIVE_LOANS = 5;
    public const int MAX_ACTIVE_RESERVATIONS = 3;
    public const int LOAN_DURATION_DAYS = 15;
    public const int RESERVATION_TTL_DAYS = 7;

    public static function reservationExpiryDate(?\DateTimeInterface $now = null): \DateTimeImmutable
    {
        $base = $now ? \DateTimeImmutable::createFromInterface($now) : new \DateTimeImmutable();
        return $base->modify('-' . self::RESERVATION_TTL_DAYS . ' days');
    }

    private function __construct()
    {
    }
}


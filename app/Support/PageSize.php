<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Chooses how many records a list shows per page, smaller on phones.
 *
 * The server can't see the screen, so resources/js/app.js stores the
 * viewport class in the "ps_compact" cookie (1 = phone-sized). Without the
 * cookie (first visit, JavaScript disabled) the desktop size is used.
 */
final class PageSize
{
    public const COOKIE = 'ps_compact';

    /** Main lists: products, batches, shipments, organisations. */
    public const LIST = [12, 6];

    /** Lists nested inside a detail page: a product's batches, a batch's history. */
    public const NESTED = [8, 5];

    /** The audit log, whose entries are short. */
    public const FEED = [20, 10];

    /**
     * @param  array{0: int, 1: int}  $sizes  [desktop, phone]
     */
    public static function for(Request $request, array $sizes = self::LIST): int
    {
        return self::isCompact($request) ? $sizes[1] : $sizes[0];
    }

    public static function isCompact(Request $request): bool
    {
        return $request->cookie(self::COOKIE) === '1';
    }
}

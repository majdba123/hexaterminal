<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Resolves the real visitor IP for public API requests.
 *
 * Public writes (leads, estimates) reach Laravel through the Next.js server
 * proxies, so $request->ip() is the frontend server for every visitor and all
 * visitors share one throttle bucket. The proxy authenticates itself with a
 * server-only shared secret and forwards the visitor IP; only then is that
 * forwarded IP trusted. Any other caller is keyed by its own connection IP,
 * so a client cannot spoof its way into another bucket.
 */
class PublicClientIp
{
    public const SECRET_HEADER = 'X-Hexa-Proxy-Secret';

    public const CLIENT_IP_HEADER = 'X-Hexa-Client-Ip';

    public static function isTrustedProxy(Request $request): bool
    {
        $secret = (string) config('security.public_proxy.secret');
        $provided = (string) $request->header(self::SECRET_HEADER, '');

        return $secret !== '' && $provided !== '' && hash_equals($secret, $provided);
    }

    /**
     * The forwarded visitor IP, or null when the request is not from the
     * trusted proxy or carries no valid visitor IP (e.g. SSR reads).
     */
    public static function forwarded(Request $request): ?string
    {
        if (! self::isTrustedProxy($request)) {
            return null;
        }

        $ip = trim((string) $request->header(self::CLIENT_IP_HEADER, ''));

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
    }

    public static function resolve(Request $request): string
    {
        return self::forwarded($request) ?? (string) $request->ip();
    }
}

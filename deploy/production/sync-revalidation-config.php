<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Dotenv\Dotenv;

[$script, $backendEnv, $frontendProductionEnv, $frontendRuntimeEnv] = array_pad($argv, 4, null);

if (! is_string($backendEnv) || ! is_string($frontendProductionEnv) || ! is_string($frontendRuntimeEnv)) {
    fwrite(STDERR, "Usage: php sync-revalidation-config.php <backend-env> <frontend-production-env> <frontend-runtime-env>\n");
    exit(1);
}

/** @return array<string, string> */
function readEnvironment(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    return Dotenv::parse((string) file_get_contents($path));
}

/** @param array<string, string> $values */
function writeEnvironment(string $path, array $values): void
{
    $contents = (string) file_get_contents($path);

    foreach ($values as $key => $value) {
        $escaped = '"'.str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', '', ''], $value).'"';
        $line = "{$key}={$escaped}";
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';
        $contents = preg_match($pattern, $contents)
            ? (string) preg_replace($pattern, $line, $contents)
            : rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException("Unable to update environment file: {$path}");
    }
}

$backend = readEnvironment($backendEnv);
$frontend = readEnvironment($frontendProductionEnv);
$secret = trim((string) ($frontend['REVALIDATE_SECRET'] ?? ''));

if ($secret === '') {
    $secret = trim((string) ($backend['REVALIDATION_SECRET'] ?? ''));
}

if ($secret === '') {
    $secret = bin2hex(random_bytes(32));
}

writeEnvironment($backendEnv, [
    'REVALIDATION_ENABLED' => 'true',
    'REVALIDATION_URL' => 'https://hexaterminal.com/api/revalidate',
    'REVALIDATION_SECRET' => $secret,
]);

writeEnvironment($frontendProductionEnv, ['REVALIDATE_SECRET' => $secret]);
writeEnvironment($frontendRuntimeEnv, ['REVALIDATE_SECRET' => $secret]);

echo "Revalidation configuration synchronized.\n";

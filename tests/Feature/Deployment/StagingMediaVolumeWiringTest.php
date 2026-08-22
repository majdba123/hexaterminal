<?php

namespace Tests\Feature\Deployment;

use Tests\TestCase;

class StagingMediaVolumeWiringTest extends TestCase
{
    public function test_api_web_mounts_the_same_persisted_public_media_volume_read_only(): void
    {
        $compose = file_get_contents(base_path('deploy/staging/docker-compose.staging.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString(
            '- ../../public:/var/www/hexa/public:ro',
            $compose,
            'api_web must serve the real Laravel public tree.',
        );
        $this->assertStringContainsString(
            '- storage_app:/var/www/hexa/public/storage:ro',
            $compose,
            'api_web must mount the persisted public-media volume at Nginx\'s /storage path, read-only.',
        );
        $this->assertStringNotContainsString(
            '- app_public:/var/www/hexa/public:ro',
            $compose,
            'An isolated app_public volume hides the real public tree and breaks the storage symlink.',
        );
    }

    public function test_storage_location_in_nginx_stays_static_only_and_non_executable(): void
    {
        $nginx = file_get_contents(base_path('deploy/staging/nginx/api-staging.conf'));

        $this->assertIsString($nginx);
        $this->assertStringContainsString('location ^~ /storage/', $nginx);
        $this->assertStringContainsString("location ~ \\.php$ {\n            return 404;", $nginx);
        $this->assertStringContainsString("Content-Security-Policy \"default-src 'none'; sandbox\"", $nginx);
        $this->assertStringContainsString('try_files $uri =404;', $nginx);
        $this->assertStringNotContainsString('fastcgi_pass app:9000;', $this->storageLocation($nginx));
    }

    private function storageLocation(string $nginx): string
    {
        $start = strpos($nginx, 'location ^~ /storage/');
        $end = strpos($nginx, "\n    location ~ \\.php$", $start);

        return substr($nginx, $start, $end - $start);
    }
}

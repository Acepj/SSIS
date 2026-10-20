<?php

declare(strict_types=1);

namespace SSIS\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SSIS\Repositories\JsonStore;
use SSIS\Repositories\UserRepository;

/**
 * End-to-end authentication tests against the real HTTP API using an
 * isolated PHP built-in server and isolated JSON storage.
 */
final class AuthenticationFlowTest extends TestCase
{
    private const HOST = '127.0.0.1';
    private const PORT = 8123;

    /** @var resource|null */
    private static $serverProcess = null;

    private static string $storageDir = '';
    private static string $cookieJarDir = '';

    public static function setUpBeforeClass(): void
    {
        self::$storageDir = sys_get_temp_dir() . '/ssis-int-' . bin2hex(random_bytes(6));
        $dataDir = self::$storageDir . '/data';
        if (!mkdir($dataDir, 0700, true) && !is_dir($dataDir)) {
            self::fail('Unable to create test storage directory.');
        }
        self::$cookieJarDir = self::$storageDir . '/cookies';
        mkdir(self::$cookieJarDir, 0700, true);

        $users = new UserRepository(new JsonStore($dataDir . '/users.json'));
        $users->create([
            'username' => 'test.admin',
            'password_hash' => password_hash('Admin-Pass-123!', PASSWORD_DEFAULT),
            'role' => 'admin',
            'active' => true,
            'display_name' => 'Test Admin',
        ]);
        $users->create([
            'username' => '2026-99001',
            'password_hash' => password_hash('Student-Pass-123!', PASSWORD_DEFAULT),
            'role' => 'student',
            'active' => true,
            'display_name' => 'Test Student',
        ]);
        $users->create([
            'username' => 'test.inactive',
            'password_hash' => password_hash('Inactive-Pass-123!', PASSWORD_DEFAULT),
            'role' => 'student',
            'active' => false,
            'display_name' => 'Inactive Student',
        ]);

        $env = getenv();
        $env['SSIS_STORAGE_PATH'] = self::$storageDir;
        $env['APP_ENV'] = 'testing';

        $publicDir = dirname(__DIR__, 2) . '/public';

        self::$serverProcess = proc_open(
            [PHP_BINARY, '-S', self::HOST . ':' . self::PORT, '-t', $publicDir, $publicDir . '/index.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            dirname(__DIR__, 2),
            $env,
        );

        if (!is_resource(self::$serverProcess)) {
            self::fail('Unable to start the test API server.');
        }

        for ($attempt = 0; $attempt < 50; $attempt++) {
            $connection = @fsockopen(self::HOST, self::PORT, $errno, $errstr, 0.2);
            if (is_resource($connection)) {
                fclose($connection);
                return;
            }
            usleep(100_000);
        }

        self::fail('The test API server did not start on port ' . self::PORT . '.');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
            self::$serverProcess = null;
        }

        self::removeDirectory(self::$storageDir);
    }

    private static function removeDirectory(string $dir): void
    {
        if ($dir === '' || !is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * @param array<string, mixed>|string|null $body
     * @param array<string, string> $extraHeaders
     * @return array{0: int, 1: array<string, mixed>|null} status + decoded body
     */
    private function request(
        string $method,
        string $path,
        array|string|null $body = null,
        ?string $cookieJar = null,
        ?string $csrfToken = null,
        array $extraHeaders = [],
    ): array {
        $ch = curl_init('http://' . self::HOST . ':' . self::PORT . '/api/v1' . $path);

        $headers = ['Accept: application/json'];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        if ($csrfToken !== null) {
            $headers[] = 'X-CSRF-Token: ' . $csrfToken;
        }
        foreach ($extraHeaders as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        if ($cookieJar !== null) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        }

        $responseBody = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [$status, is_string($responseBody) ? json_decode($responseBody, true) : null];
    }

    private function newCookieJar(): string
    {
        return self::$cookieJarDir . '/' . bin2hex(random_bytes(8)) . '.txt';
    }

    private function fetchCsrfToken(string $cookieJar): string
    {
        [$status, $body] = $this->request('GET', '/auth/csrf', null, $cookieJar);
        $this->assertSame(200, $status);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body['data']['token'] ?? '');

        return (string) $body['data']['token'];
    }

    public function testFullLoginLogoutFlow(): void
    {
        $jar = $this->newCookieJar();
        $token = $this->fetchCsrfToken($jar);

        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => 'test.admin',
            'password' => 'wrong-password',
        ], $jar, $token);
        $this->assertSame(401, $status);
        $this->assertSame('INVALID_CREDENTIALS', $body['error']['code']);

        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => 'test.admin',
            'password' => 'Admin-Pass-123!',
            'remember' => false,
        ], $jar, $token);
        $this->assertSame(200, $status);
        $this->assertSame('admin', $body['data']['user']['role']);
        $this->assertSame('Test Admin', $body['data']['user']['displayName']);

        [$status, $body] = $this->request('GET', '/auth/me', null, $jar);
        $this->assertSame(200, $status);
        $this->assertSame('test.admin', $body['data']['user']['identifier']);

        [$status] = $this->request('POST', '/auth/logout', null, $jar, $token);
        $this->assertSame(200, $status);

        [$status, $body] = $this->request('GET', '/auth/me', null, $jar);
        $this->assertSame(401, $status);
        $this->assertSame('UNAUTHENTICATED', $body['error']['code']);
    }

    public function testStateChangingRequestWithoutCsrfIsRejected(): void
    {
        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => 'test.admin',
            'password' => 'Admin-Pass-123!',
        ], $this->newCookieJar());

        $this->assertSame(403, $status);
        $this->assertSame('CSRF_TOKEN_INVALID', $body['error']['code']);
    }

    public function testMalformedJsonBodyIsRejected(): void
    {
        $jar = $this->newCookieJar();
        $token = $this->fetchCsrfToken($jar);

        [$status, $body] = $this->request('POST', '/auth/login', '{"identifier":', $jar, $token);

        $this->assertSame(400, $status);
        $this->assertSame('INVALID_JSON', $body['error']['code']);
    }

    public function testInactiveAccountCannotAuthenticate(): void
    {
        $jar = $this->newCookieJar();
        $token = $this->fetchCsrfToken($jar);

        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => 'test.inactive',
            'password' => 'Inactive-Pass-123!',
        ], $jar, $token);

        $this->assertSame(401, $status);
        $this->assertSame('ACCOUNT_INACTIVE', $body['error']['code']);
    }

    public function testStudentLoginReturnsStudentRole(): void
    {
        $jar = $this->newCookieJar();
        $token = $this->fetchCsrfToken($jar);

        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => '2026-99001',
            'password' => 'Student-Pass-123!',
        ], $jar, $token);

        $this->assertSame(200, $status);
        $this->assertSame('student', $body['data']['user']['role']);
        $this->assertSame('2026-99001', $body['data']['user']['identifier']);
    }

    public function testUnknownRouteReturns404(): void
    {
        [$status, $body] = $this->request('GET', '/definitely-missing');

        $this->assertSame(404, $status);
        $this->assertSame('ROUTE_NOT_FOUND', $body['error']['code']);
    }

    public function testDisallowedOriginIsRejected(): void
    {
        [$status, $body] = $this->request('GET', '/auth/csrf', null, null, null, [
            'Origin' => 'http://evil.example',
        ]);

        $this->assertSame(403, $status);
        $this->assertSame('ORIGIN_FORBIDDEN', $body['error']['code']);
    }

    public function testLoginRateLimitReturns429(): void
    {
        $jar = $this->newCookieJar();
        $token = $this->fetchCsrfToken($jar);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            [$status] = $this->request('POST', '/auth/login', [
                'identifier' => 'rate.limited',
                'password' => 'still-wrong',
            ], $jar, $token);
            $this->assertSame(401, $status);
        }

        [$status, $body] = $this->request('POST', '/auth/login', [
            'identifier' => 'rate.limited',
            'password' => 'still-wrong',
        ], $jar, $token);

        $this->assertSame(429, $status);
        $this->assertSame('RATE_LIMITED', $body['error']['code']);
    }
}

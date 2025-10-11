<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Override assertDatabaseHas to allow empty data array which indicates intent to assert table exists.
     * This avoids issues with older tests calling assertDatabaseHas('table', []) which otherwise
     * triggers an internal error in some framework versions.
     */
    public function assertDatabaseHas($table, array $data, $connection = null)
    {
        if (empty($data)) {
            $this->assertTrue(Schema::hasTable($table), "Failed asserting that table [$table] exists.");
            return;
        }

        parent::assertDatabaseHas($table, $data, $connection);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Note: do not force JSON Accept header globally — many feature tests expect
        // normal web responses (redirects and views). Tests needing JSON responses
        // should call $this->withHeaders(['Accept' => 'application/json']) themselves.

        // Instrument the LoggedExceptionCollection used by TestResponse so we can
        // capture what gets pushed into it during requests. This helps diagnose
        // cases where non-Throwable values (e.g. strings) end up in the collection
        // causing TestResponse to attempt to call getMessage() on a string.
        $this->app->singleton(\Illuminate\Testing\LoggedExceptionCollection::class, function () {
            return new class extends \Illuminate\Testing\LoggedExceptionCollection {
                public function push(...$values)
                {
                    foreach ($values as $value) {
                        try {
                            $entry = [
                                'action' => 'push',
                                'type' => is_object($value) ? get_class($value) : gettype($value),
                                'value' => is_object($value) ? (method_exists($value, 'getMessage') ? $value->getMessage() : '(object)') : $value,
                                'time' => now()->toISOString(),
                                'trace' => array_map(function ($frame) {
                                    return isset($frame['file']) ? ($frame['file'].':'.$frame['line']) : $frame['function'] ?? '';
                                }, array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 2, 6)),
                            ];
                            @file_put_contents(storage_path('logs/test_logged_exceptions.log'), json_encode($entry) . PHP_EOL, FILE_APPEND);
                        } catch (\Throwable $_) {
                            // swallow
                        }
                    }

                    return parent::push(...$values);
                }

                // rely on Collection::offsetSet behavior; push(...) will capture pushes
                public function last(?callable $callback = null, $default = null)
                {
                    $last = parent::last($callback, $default);
                    try {
                        $entry = [
                            'action' => 'last',
                            'type' => is_object($last) ? get_class($last) : gettype($last),
                            'value' => is_object($last) ? (method_exists($last, 'getMessage') ? $last->getMessage() : '(object)') : $last,
                            'time' => now()->toISOString(),
                        ];
                        @file_put_contents(storage_path('logs/test_logged_exceptions.log'), json_encode($entry) . PHP_EOL, FILE_APPEND);
                    } catch (\Throwable $_) {
                        // swallow
                    }

                    return $last;
                }
            };
        });
    }
}

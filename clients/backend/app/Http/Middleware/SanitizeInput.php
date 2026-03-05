<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Fields that should NOT be sanitized (e.g., passwords, HTML content)
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'description',
        'content',
        'body',
        'message',
        'notes',
        'internal_note',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $request->merge($this->sanitize($input));

        return $next($request);
    }

    /**
     * Recursively sanitize input data
     */
    protected function sanitize(array $data, string $prefix = ''): array
    {
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (in_array($key, $this->except)) {
                continue;
            }

            if (is_string($value)) {
                $data[$key] = strip_tags(trim($value));
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value, $fullKey);
            }
        }

        return $data;
    }
}

<?php
// app/Core/Kernel.php
// Verantwortlich für: ENV laden, Error-Handling aktivieren, Router konfigurieren und Requests dispatchen.

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;
use FastRoute;

final class Kernel
{
    private FastRoute\Dispatcher $dispatcher;

    public function __construct()
    {
        $this->bootstrapEnv();
        $this->registerErrorHandling();
        $this->dispatcher = $this->createRouter();
    }

    /**
     * Lädt .env (falls vorhanden) und setzt PHP-Fehlermodus gemäß APP_DEBUG.
     */
    private function bootstrapEnv(): void
    {
        $envPath = dirname(__DIR__, 2);
        if (is_file($envPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->safeLoad();
        }

        if ((getenv('APP_DEBUG') ?: 'false') === 'true') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Registriert Exception- und Error-Handler, die konsistente JSON-Antworten liefern.
     */
    private function registerErrorHandling(): void
    {
        set_exception_handler(function (\Throwable $e) {
            Response::error('unhandled_exception', $e->getMessage(), 500, [
                'exception' => get_class($e),
                'trace' => getenv('APP_DEBUG') === 'true' ? $e->getTrace() : null,
            ]);
        });

        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /**
     * Definiert die Routen und erstellt den Dispatcher.
     */
    private function createRouter(): FastRoute\Dispatcher
    {
        return FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            // Healthcheck (GET /api/health)
            $r->addRoute('GET', '/api/health', function() {
                return [
                    'code' => 'ok',
                    'message' => 'healthy',
                    'data' => [
                        'time' => date('c'),
                        'env'  => getenv('APP_ENV') ?: 'unknown',
                    ],
                ];
            });

            // Platzhalter für weitere Module (MVP-Routing wird später ergänzt)
            // $r->addRoute('POST', '/api/auth/login', [...]);
            // $r->addRoute('POST', '/api/auth/register', [...]);
        });
    }

    /**
     * Verarbeitet einen HTTP-Request anhand von Methode und URI.
     * Gibt ein Array zurück, das als JSON serialisiert wird.
     */
    public function handle(string $httpMethod, string $uri): array
    {
        // Query-String entfernen (FastRoute erwartet nur den Pfad)
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                return Response::make('not_found', 'Route nicht gefunden', null, 404);
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                return Response::make('method_not_allowed', 'Methode nicht erlaubt', ['allowed' => $allowedMethods], 405);
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                if (is_callable($handler)) {
                    $result = call_user_func_array($handler, [$vars]);
                    // Wenn Handler schon ein fertiges Response-Array zurückgibt, einfach durchreichen
                    if (is_array($result) && array_key_exists('_status', $result)) {
                        return $result;
                    }
                    // Ansonsten in Standard-Response einbetten
                    return Response::make('ok', 'success', $result, 200);
                }
                // Später können hier Controller-Strings o.ä. aufgelöst werden
                return Response::make('bad_handler', 'Ungültiger Handler', null, 500);
            default:
                return Response::make('internal_error', 'Unerwarteter Router-Status', null, 500);
        }
    }
}
<?php
// public/index.php
// Front Controller: Einstiegspunkt für alle HTTP-Anfragen.
// Lädt Autoload, initialisiert ENV/Kernel, behandelt Routing und gibt JSON-Antworten zurück.
//1
declare(strict_types=1);

use App\Core\Kernel;
use App\Core\Response;

require __DIR__ . '/../vendor/autoload.php';

// Kernel initialisieren (lädt .env, setzt Error-Handling, baut Router)
$kernel = new Kernel();

// Einfache CORS-Unterstützung (Basis, später als Middleware auslagern)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowedOrigins = explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: '*');
$allowAny = in_array('*', $allowedOrigins, true);

if ($allowAny || in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . ($allowAny ? '*' : $origin));
}
header('Access-Control-Allow-Methods: ' . (getenv('CORS_ALLOWED_METHODS') ?: 'GET,POST,PATCH,DELETE,OPTIONS'));
header('Access-Control-Allow-Headers: ' . (getenv('CORS_ALLOWED_HEADERS') ?: 'Content-Type,Authorization'));
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight sofort beantworten
    http_response_code(204);
    exit;
}

// Request an den Kernel/Router weiterreichen
try {
    // --- Pfad normalisieren, wenn Projekt in Unterordner liegt ---
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $baseDir = '/qevo-server'; // <-- bei dir Ordnername
    if (str_starts_with($uri, $baseDir)) {
        $uri = substr($uri, strlen($baseDir));
        if ($uri === '') $uri = '/';
    }

    // Dann den Kernel aufrufen
    $response = $kernel->handle($_SERVER['REQUEST_METHOD'], $uri);
    // $response sollte ein assoziatives Array sein, das Response::json versteht
    Response::json($response);
} catch (Throwable $e) {
    // Unerwarteter Fehler → standardisierte JSON-Fehlerantwort
    Response::error('internal_error', $e->getMessage(), 500, [
        'exception' => get_class($e),
        // In Produktion besser keine Traces ausgeben – hier nur im Debugfall:
        'trace' => getenv('APP_DEBUG') === 'true' ? $e->getTrace() : null,
    ]);
}
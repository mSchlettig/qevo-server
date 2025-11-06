<?php
// app/Core/Response.php
// Liefert konsistente JSON-Antworten für Erfolg und Fehlerfälle.

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /**
     * Standardisierte Erfolgs-/Fehlerantwort als Array.
     * Dieses Array wird in index.php per Response::json() ausgegeben.
     */
    public static function make(string $code, string $message, $data = null, int $status = 200): array
    {
        return [
            '_status' => $status,  // interner HTTP-Status für Response::json
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];
    }

    /**
     * Direkte JSON-Ausgabe und Beenden des Scripts.
     */
    public static function json(array $payload): void
    {
        $status = $payload['_status'] ?? 200;
        unset($payload['_status']);

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Hilfsmethode für Fehlerantworten.
     */
    public static function error(string $code, string $message, int $status = 400, array $extra = []): void
    {
        $payload = self::make($code, $message, $extra ?: null, $status);
        self::json($payload);
    }
}
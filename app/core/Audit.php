<?php

declare(strict_types=1);

namespace App\Core;

final class Audit
{
    public static function log(string $module, string $action, ?int $recordId = null, mixed $oldValues = null, mixed $newValues = null): void
    {
        try {
            $db = Database::connection();

            $stmt = $db->prepare(
                'INSERT INTO hris_audit_log (user_id, action, module, record_id, old_values, new_values, ip_address)
                 VALUES (:user_id, :action, :module, :record_id, :old_values, :new_values, :ip_address)'
            );

            $stmt->execute([
                'user_id' => Auth::id(),
                'action' => strtoupper($action),
                'module' => $module,
                'record_id' => $recordId,
                'old_values' => self::encode($oldValues),
                'new_values' => self::encode($newValues),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable) {
            // Never fail business actions because audit logging failed.
        }
    }

    private static function encode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : null;
    }
}

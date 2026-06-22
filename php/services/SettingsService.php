<?php
/**
 * Settings service — port of src/services/settings.service.js.
 * A single global row keyed 'app'. passcodeHash is never returned to clients.
 */
class SettingsService
{
    private const KEY = 'app';

    /** Ensures the singleton row exists and returns the raw DB row. */
    private function row(): array
    {
        $stmt = db()->prepare('SELECT * FROM settings WHERE `key` = :k');
        $stmt->execute(['k' => self::KEY]);
        $row = $stmt->fetch();
        if (!$row) {
            db()->prepare('INSERT INTO settings (`key`, passcode_enabled, preferences) VALUES (:k, 0, :p)')
                ->execute(['k' => self::KEY, 'p' => '{}']);
            $stmt->execute(['k' => self::KEY]);
            $row = $stmt->fetch();
        }
        return $row;
    }

    public function get(): array
    {
        return $this->serialize($this->row());
    }

    public function save(array $data): array
    {
        $row = $this->row();

        $passcodeEnabled = (int) $row['passcode_enabled'];
        $passcodeHash    = $row['passcode_hash']; // may be null
        $preferences     = $row['preferences'] ? (json_decode($row['preferences'], true) ?: []) : [];

        if (array_key_exists('passcodeEnabled', $data) && is_bool($data['passcodeEnabled'])) {
            $passcodeEnabled = $data['passcodeEnabled'] ? 1 : 0;
            if (!$data['passcodeEnabled']) {
                $passcodeHash = null; // disabling clears the stored hash
            }
        }

        if (!empty($data['passcode'])) {
            $passcodeHash    = password_hash($data['passcode'], PASSWORD_BCRYPT, ['cost' => 10]);
            $passcodeEnabled = 1;
        }

        if (isset($data['preferences']) && is_array($data['preferences'])) {
            $preferences = array_merge($preferences, $data['preferences']);
        }

        db()->prepare(
            'UPDATE settings SET passcode_enabled = :pe, passcode_hash = :ph, preferences = :pr WHERE `key` = :k'
        )->execute([
            'pe' => $passcodeEnabled,
            'ph' => $passcodeHash,
            'pr' => json_encode((object) $preferences, JSON_UNESCAPED_UNICODE),
            'k'  => self::KEY,
        ]);

        return $this->get();
    }

    /** Optional helper for future server-side passcode verification. */
    public function verifyPasscode(string $passcode): bool
    {
        $row = $this->row();
        if (empty($row['passcode_hash'])) {
            return false;
        }
        return password_verify($passcode, $row['passcode_hash']);
    }

    /** Public shape — never includes passcodeHash. */
    private function serialize(array $r): array
    {
        $prefs = $r['preferences'] ? json_decode($r['preferences'], true) : [];
        return [
            'id'              => (int) $r['id'],
            'key'             => $r['key'],
            'passcodeEnabled' => (bool) $r['passcode_enabled'],
            'preferences'     => (object) ($prefs ?: []),
            'createdAt'       => isset($r['created_at']) ? str_replace(' ', 'T', $r['created_at']) . '.000Z' : null,
            'updatedAt'       => isset($r['updated_at']) ? str_replace(' ', 'T', $r['updated_at']) . '.000Z' : null,
        ];
    }
}

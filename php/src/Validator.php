<?php
/**
 * Validation rules — direct port of src/validators/*. Each method returns an
 * array of { field, message } problems (empty = valid). The controller turns a
 * non-empty result into a 400 "Validation failed" ApiException, exactly like
 * the express-validator + validate.middleware pairing did.
 *
 * Note: these trim string inputs in place (like express-validator's .trim()).
 */
class Validator
{
    // Same phone pattern as the Node validator: starts with + or digit, then 5-19 of [digit space - ( )].
    private const PHONE_RE = '/^[+\d][\d\s\-()]{5,19}$/';

    private static function str(&$data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        $data[$key] = is_string($data[$key]) ? trim($data[$key]) : $data[$key];
        return is_string($data[$key]) ? $data[$key] : null;
    }

    public static function createContact(array &$data): array
    {
        $e = [];
        $name = self::str($data, 'name');
        if ($name === null || $name === '') {
            $e[] = ['field' => 'name', 'message' => 'Name is required'];
        } elseif (mb_strlen($name) > 120) {
            $e[] = ['field' => 'name', 'message' => 'Invalid value'];
        }

        $phone = self::str($data, 'phone');
        if ($phone === null || $phone === '') {
            $e[] = ['field' => 'phone', 'message' => 'Phone is required'];
        } elseif (!preg_match(self::PHONE_RE, $phone)) {
            $e[] = ['field' => 'phone', 'message' => 'Phone must be a valid number'];
        }

        self::optionalLen($data, 'location', 200, $e);
        self::optionalLen($data, 'city', 100, $e);
        self::optionalLen($data, 'businessType', 100, $e);
        self::optionalLen($data, 'notes', 1000, $e);
        return $e;
    }

    public static function updateContact(array &$data): array
    {
        $e = [];
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $name = self::str($data, 'name');
            if ($name === '') {
                $e[] = ['field' => 'name', 'message' => 'Name cannot be empty'];
            } elseif (mb_strlen($name) > 120) {
                $e[] = ['field' => 'name', 'message' => 'Invalid value'];
            }
        }
        if (array_key_exists('phone', $data) && $data['phone'] !== null) {
            $phone = self::str($data, 'phone');
            if (!preg_match(self::PHONE_RE, (string) $phone)) {
                $e[] = ['field' => 'phone', 'message' => 'Phone must be a valid number'];
            }
        }
        self::optionalLen($data, 'location', 200, $e);
        self::optionalLen($data, 'city', 100, $e);
        self::optionalLen($data, 'businessType', 100, $e);
        self::optionalLen($data, 'notes', 1000, $e);
        return $e;
    }

    /** Replaces param('id').isMongoId() — MySQL uses positive integer ids. */
    public static function idParam(string $id): array
    {
        if (!preg_match('/^[1-9]\d*$/', $id)) {
            return [['field' => 'id', 'message' => 'Invalid contact id']];
        }
        return [];
    }

    public static function search(array &$query): array
    {
        if (isset($query['q']) && is_string($query['q'])) {
            $query['q'] = trim($query['q']);
            if (mb_strlen($query['q']) > 200) {
                return [['field' => 'q', 'message' => 'Invalid value']];
            }
        }
        return [];
    }

    public static function saveSettings(array &$data): array
    {
        $e = [];
        if (array_key_exists('passcodeEnabled', $data) && !is_bool($data['passcodeEnabled'])) {
            $e[] = ['field' => 'passcodeEnabled', 'message' => 'passcodeEnabled must be boolean'];
        }
        if (array_key_exists('passcode', $data) && $data['passcode'] !== null) {
            $pc = $data['passcode'];
            if (!is_string($pc) || mb_strlen($pc) < 4 || mb_strlen($pc) > 12) {
                $e[] = ['field' => 'passcode', 'message' => 'Passcode must be 4-12 characters'];
            }
        }
        if (array_key_exists('preferences', $data) && $data['preferences'] !== null) {
            // a JSON object decodes to an associative array (or empty array)
            if (!is_array($data['preferences'])) {
                $e[] = ['field' => 'preferences', 'message' => 'preferences must be an object'];
            }
        }
        return $e;
    }

    private static function optionalLen(array &$data, string $key, int $max, array &$e): void
    {
        if (array_key_exists($key, $data) && $data[$key] !== null) {
            $v = self::str($data, $key);
            if ($v !== null && mb_strlen($v) > $max) {
                $e[] = ['field' => $key, 'message' => 'Invalid value'];
            }
        }
    }

    /** Throws a 400 if $errors is non-empty. */
    public static function assert(array $errors): void
    {
        if (!empty($errors)) {
            throw ApiException::badRequest('Validation failed', $errors);
        }
    }
}

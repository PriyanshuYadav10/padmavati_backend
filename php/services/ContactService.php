<?php
/**
 * Contact service — all DB access for contacts lives here (port of
 * src/services/contact.service.js). Keeps the controllers thin.
 */
class ContactService
{
    /** DB column -> JSON key. Columns not listed are not exposed. */
    private const COLUMNS = ['name', 'phone', 'location', 'city', 'business_type', 'notes'];

    /** Whitelisted sort fields (JSON name => column). */
    private const SORTABLE = [
        'createdAt'    => 'created_at',
        'updatedAt'    => 'updated_at',
        'name'         => 'name',
        'city'         => 'city',
        'businessType' => 'business_type',
    ];

    public function create(array $payload): array
    {
        $row = [
            'name'          => $payload['name'] ?? '',
            'phone'         => $payload['phone'] ?? '',
            'location'      => $payload['location'] ?? '',
            'city'          => $payload['city'] ?? '',
            'business_type' => $payload['businessType'] ?? '',
            'notes'         => $payload['notes'] ?? '',
        ];
        $sql = 'INSERT INTO contacts (name, phone, location, city, business_type, notes)
                VALUES (:name, :phone, :location, :city, :business_type, :notes)';
        $stmt = db()->prepare($sql);
        $stmt->execute($row);
        return $this->findById((int) db()->lastInsertId());
    }

    public function findAll(array $query): array
    {
        $page  = max(1, (int) ($query['page'] ?? 1));
        $limit = max(1, (int) ($query['limit'] ?? 50));
        $skip  = ($page - 1) * $limit;
        [$col, $dir] = $this->parseSort($query['sort'] ?? '-createdAt');

        $total = (int) db()->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

        $stmt = db()->prepare("SELECT * FROM contacts ORDER BY $col $dir LIMIT :lim OFFSET :off");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $skip, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map([$this, 'serialize'], $stmt->fetchAll());

        return [
            'items' => $items,
            'meta'  => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }

    public function findById(int $id): array
    {
        $stmt = db()->prepare('SELECT * FROM contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw ApiException::notFound('Contact not found');
        }
        return $this->serialize($row);
    }

    public function update(int $id, array $payload): array
    {
        $this->findById($id); // 404 if missing

        $map = [
            'name' => 'name', 'phone' => 'phone', 'location' => 'location',
            'city' => 'city', 'businessType' => 'business_type', 'notes' => 'notes',
        ];
        $sets = [];
        $bind = ['id' => $id];
        foreach ($map as $jsonKey => $col) {
            if (array_key_exists($jsonKey, $payload) && $payload[$jsonKey] !== null) {
                $sets[]      = "$col = :$col";
                $bind[$col]  = $payload[$jsonKey];
            }
        }
        if ($sets) {
            $sql = 'UPDATE contacts SET ' . implode(', ', $sets) . ' WHERE id = :id';
            db()->prepare($sql)->execute($bind);
        }
        return $this->findById($id);
    }

    public function remove(int $id): void
    {
        $this->findById($id); // 404 if missing
        db()->prepare('DELETE FROM contacts WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Free-text search across all fields (case-insensitive LIKE OR), replacing
     * the Mongo regex $or. Empty query returns everything, newest first.
     */
    public function search(?string $q): array
    {
        if ($q === null || trim($q) === '') {
            $rows = db()->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll();
            return array_map([$this, 'serialize'], $rows);
        }
        // escape LIKE wildcards so user input is treated literally
        $term = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($q)) . '%';
        // distinct placeholders per column — native prepares can't reuse one name
        $conds = [];
        $bind  = [];
        foreach (self::COLUMNS as $i => $c) {
            $conds[]        = "$c LIKE :q$i";
            $bind[":q$i"]   = $term;
        }
        $where = implode(' OR ', $conds);
        $stmt = db()->prepare("SELECT * FROM contacts WHERE $where ORDER BY created_at DESC");
        $stmt->execute($bind);
        return array_map([$this, 'serialize'], $stmt->fetchAll());
    }

    /** Map a DB row to the public JSON shape (camelCase + ISO dates). */
    private function serialize(array $r): array
    {
        return [
            'id'           => (int) $r['id'],
            'name'         => $r['name'],
            'phone'        => $r['phone'],
            'location'     => $r['location'] ?? '',
            'city'         => $r['city'] ?? '',
            'businessType' => $r['business_type'] ?? '',
            'notes'        => $r['notes'] ?? '',
            'createdAt'    => self::iso($r['created_at'] ?? null),
            'updatedAt'    => self::iso($r['updated_at'] ?? null),
        ];
    }

    /** "2024-01-02 03:04:05" (UTC) -> "2024-01-02T03:04:05.000Z" */
    private static function iso(?string $dt): ?string
    {
        if (!$dt) {
            return null;
        }
        return str_replace(' ', 'T', $dt) . '.000Z';
    }

    /** "-createdAt" => [created_at, DESC]; unknown fields fall back to created_at DESC. */
    private function parseSort(string $sort): array
    {
        $dir = 'ASC';
        if ($sort !== '' && $sort[0] === '-') {
            $dir  = 'DESC';
            $sort = substr($sort, 1);
        }
        $col = self::SORTABLE[$sort] ?? 'created_at';
        if (!isset(self::SORTABLE[$sort])) {
            $dir = 'DESC';
        }
        return [$col, $dir];
    }
}

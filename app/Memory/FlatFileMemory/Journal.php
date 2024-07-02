<?php declare(strict_types=1);

namespace App\Memory\FlatFileMemory;

use App\Exceptions\NotSupportedException;
use App\Utils\Arrays;
use PDO;

class Journal
{
    /**
     * @var string
     */
	private string $path;

    /**
     * @var PDO|null
     */
	private ?PDO $pdo = null;

    /**
     * @param string $folder
     */
    public function __construct(string $folder)
    {
        if (! extension_loaded('pdo_sqlite')) {
			throw new NotSupportedException('SQLiteJournal requires PHP extension pdo_sqlite which is not loaded.');
		}

		$this->path = $folder . DS . 'journal.dbm';
    }

	private function open(): void
	{
		if ($this->path !== ':memory:' && ! is_file($this->path)) {
			touch($this->path);
		}

		$this->pdo = new PDO('sqlite:' . $this->path);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('
			PRAGMA foreign_keys=ON;
			PRAGMA journal_mode = WAL;

            CREATE TABLE IF NOT EXISTS items (
                id INTEGER PRIMARY KEY,
                namespace TEXT NOT NULL,
                key TEXT NOT NULL,
                expiration INTEGER NULL
            );

            CREATE TABLE IF NOT EXISTS tags (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                item INTEGER NOT NULL,
                FOREIGN KEY (item) REFERENCES items (id) ON UPDATE CASCADE ON DELETE CASCADE
            );
		');
	}

    /**
     * @param string $namespace
     * @param string $key
     * @return object|null
     */
	public function load(string $namespace, string $key): ?object
    {
        if (! $this->pdo) {
			$this->open();
		}

        $statement = $this->pdo->prepare('SELECT * FROM items WHERE namespace = ? AND key = ? AND (expiration IS NULL OR expiration > ?)');
        $statement->execute([$namespace, $key, time()]);

        return $statement->fetchObject() ?: null;
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param int|null $expiration
     * @param array $tags
     */
	public function write(string $namespace, string $key, ?int $expiration, array $tags): void
	{
	    if (! $this->pdo) {
			$this->open();
		}

	    $this->pdo->exec('BEGIN');

	    $statement = $this->pdo->prepare('DELETE FROM items WHERE namespace = ? AND key = ?');
	    $statement->execute([$namespace, $key]);

	    $statement = $this->pdo->prepare('INSERT INTO items (namespace, key, expiration) VALUES (?, ?, ?)');
	    $statement->execute([$namespace, $key, $expiration]);

	    $this->pdo->exec('COMMIT');

	    if ($tags) {
	        $id = $this->pdo->lastInsertId();
	        $this->pdo->exec('BEGIN');

            foreach ($tags as $tag) {
                $statement = $this->pdo->prepare('INSERT INTO tags (name, item) VALUES (?, ?)');
	            $statement->execute([(string) $tag, $id]);
	        }

	        $this->pdo->exec('COMMIT');
        }
	}

    /**
     * @param string|null $namespace
     * @param string|null $key
     * @param array $tags
     * @return array
     */
	public function clean(?string $namespace, ?string $key = null, array $tags = []): array
	{
		if (! $this->pdo) {
			$this->open();
		}

		$args = [];
		$joins = [];
		$where = [];

		if ($namespace) {
		    $where[] = 'items.namespace = ?';
		    $args[] = $namespace;
        }

		if ($key) {
		    $where[] = 'items.key = ?';
		    $args[] = $key;
        }

		if ($tags) {
            foreach ($tags as $index => $tag) {
                $joins[] = 'INNER JOIN tags as tag' . $index . ' ON tag' . $index . '.item = items.id';
                $where[] = 'tag' . $index . '.name = ?';
                $args[] = $tag;
		    }
        }

		$statement = $this->pdo->prepare(
		    'SELECT DISTINCT items.id, items.namespace, items.key FROM items '
            . implode(' ', $joins)
            . ' WHERE '
            . implode(' AND ', $where)
        );

		$statement->execute($args);
		$results = $statement->fetchAll(PDO::FETCH_CLASS);

		if (empty($results)) {
		    return [];
        }

		$ids = [];
		$return = [];

        foreach ($results as $result) {
            $ids[] = $result->id;
            $return[] = (object) [
                'namespace' => $result->namespace,
                'key' => $result->key,
            ];
        }

        $this->pdo->exec('BEGIN');

        $sql = 'DELETE FROM items WHERE id IN (' . implode(',', Arrays::fill('?', count($ids))) . ')';
        $statement = $this->pdo->prepare($sql);
	    $statement->execute($ids);

		$this->pdo->exec('COMMIT');

		return $return;
	}
}

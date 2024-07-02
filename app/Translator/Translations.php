<?php declare(strict_types=1);

namespace App\Translator;

use App\Helpers\FilesHelper;
use App\Translator\Tracy\Panel;
use App\Utils\Arrays;
use Nette\Neon\Neon;
use Nette\Utils\Finder;

class Translations
{
    /**
     * @var string
     */
    private string $storage;

    /**
     * @var string
     */
    private string $hash = '';

    /**
     * @var array
     */
    private array $directories;

    /**
     * @var array
     */
    private array $files = [];

    /**
     * @var array|null
     */
    private ?array $translations = null;

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @var Panel|null
     */
	private ?Panel $tracyPanel;

    /**
     * @param string $storage
     * @param array $directories
     * @param bool $debug
     * @param Panel|null $tracyPanel
     */
    public function __construct(string $storage, array $directories, bool $debug, ?Panel $tracyPanel = null)
    {
        $this->storage = $storage;
        $this->directories = $directories;
        $this->debug = $debug;
        $this->tracyPanel = $tracyPanel;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $this->load();

        if (isset($this->translations[$key])) {
            return stripslashes($this->translations[$key]);
        }

        return null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->load();

        return array_key_exists($key, $this->translations);
    }

    /**
     * @return void
     */
    private function load(): void
    {
        if (! $this->translations) {
            $this->restore();
            $this->tracyPanel?->logTranslations($this->translations);
        }
    }

    /**
     * @return void
     */
    private function restore(): void
    {
        if (! file_exists($this->storage)) {
            $this->reload();

            return;
        }

        // load from storage
        $content = include $this->storage;

        $this->hash = $content['hash'];
        $this->files = $content['files'];
        $this->translations = $content['translations'];

        // new directory
        if ($content['directories'] !== $this->directories) {
            $this->reload();

            return;
        }

        // debug mode -> check files hash
        if ($this->debug && $this->hash !== FilesHelper::getModificationsHash(array_keys($this->files))) {
            $this->reload();
        }
    }

    /**
     * @return void
     */
    private function reload(): void
    {
        $this->files = [];
        $this->translations = [];

        $this->hash = FilesHelper::getModificationsHash(array_keys($this->files));
        $this->loadTranslations();
        $this->store();
    }

    /**
     * @return void
     */
    private function store(): void
    {
        $code = '<?php ' . PHP_EOL;
        $code .= 'return [' . PHP_EOL;
        $code .= '"hash" => "' . $this->hash . '",' . PHP_EOL;
        $code .= '"files" => ' . $this->generateStringPHPArrayCode($this->files) . ',' . PHP_EOL;
        $code .= '"directories" => ' . $this->generateStringPHPArrayCode($this->directories) . ',' . PHP_EOL;
        $code .= '"translations" => ' . $this->generateStringPHPArrayCode($this->translations) . ',' . PHP_EOL;
        $code .= '];';

        file_put_contents($this->storage, $code);
    }

    /**
     * @return void
     */
    private function loadTranslations(): void
    {
        $translations = [];

        foreach ($this->getFiles() as $path => $name) {
            $namespace = preg_replace('/\.(.*)/', '', $name);
            $locale = basename(dirname($path));
            $decoded = (array) Neon::decode(file_get_contents($path));

            $translations[$locale][$namespace] = Arrays::mergeTree($translations[$locale][$namespace] ?? [], $decoded);
        }

        $this->translations = Arrays::flattenKeys($translations);
    }

    /**
     * @return array
     */
    private function getFiles(): array
    {
        if (empty($this->files)) {
            $this->loadFiles();
        }

        return $this->files;
    }

    /**
     * @return void
     */
    private function loadFiles(): void
    {
        foreach (Finder::find('*.neon')->from(...$this->directories) as $file) {
            $this->files[$file->getPathname()] = $file->getFilename();
        }
    }

    /**
     * @param array $array
     * @return string
     */
    private function generateStringPHPArrayCode(array $array): string
    {
        $lines = [];
        foreach ($array as $property => $value) {
            $lines[] = $this->generateValuePHPCode($property) . ' => ' . $this->generateValuePHPCode($value) . ',';
        }
        $lines = implode(PHP_EOL, $lines);


        return '[' . PHP_EOL . $lines . PHP_EOL . ']';
    }

    /**
     * @param $value
     * @return string
     */
    private function generateValuePHPCode($value): string
    {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }

        return (string) $value;
    }
}

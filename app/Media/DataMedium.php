<?php declare(strict_types=1);

namespace App\Media;

use App\Exceptions\UnableToLoadDataException;
use App\Exceptions\ValidationException;
use App\Helpers\FileHelper;
use App\Http\GetRequest;
use App\Http\RequestException;
use App\Utils\FileSystem;
use finfo;
use Nette\Http\FileUpload;

class DataMedium
{
    /**
     * @var FileUpload|null
     */
    private ?FileUpload $file = null;

    /**
     * @var string|null
     */
    private ?string $url = null;

    /**
     * @var string|null
     */
    private ?string $path = null;

    /**
     * @var string|null
     */
    private ?string $data = null;

    /**
     * @var string|null
     */
    private ?string $mimeType = null;

    /**
     * @var string|null
     */
    private ?string $extension = null;

    /**
     * @var int|null
     */
    private ?int $size = null;

    private function __construct() {}

    /**
     * @param FileUpload $file
     * @return static
     */
    public static function fromFile(FileUpload $file): self
    {
        return (new self)->setFile($file);
    }

    /**
     * @param string $url
     * @return static
     */
    public static function fromUrl(string $url): self
    {
        return (new self)->setUrl($url);
    }

    /**
     * @param string $path
     * @return static
     */
    public static function fromPath(string $path): self
    {
        return (new self)->setPath($path);
    }

    /**
     * @param string $data
     * @return static
     */
    public static function fromData(string $data): self
    {
        return (new self)->setData($data);
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function update(callable $callback): self
    {
        if ($data = $callback($this)) {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @return FileUpload|null
     */
    public function getFile(): ?FileUpload
    {
        return $this->file;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        if (! $this->data) {
            if ($data = $this->load()) {
                $this->data = $data;
            } else {
                throw new UnableToLoadDataException('Failed to retrieve media data');
            }
        }

        return $this->data;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        if ($this->mimeType) {
            return $this->mimeType;
        }

        if ($this->file) {
            $this->mimeType = $this->file->getContentType();

            return $this->mimeType;
        }

        if (($data = $this->getData()) && $type = (new finfo(FILEINFO_MIME_TYPE))->buffer($data)) {
            $this->mimeType = $type;
        }

        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        if (! $this->extension) {
            $this->extension = FileHelper::mimeTypeToExt($this->getMimeType());
        }



        return $this->extension;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        if (! $this->size) {
            $this->size = strlen($this->getData());
        }

        return $this->size;
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        if ($this->path) {
            return basename($this->path);
        }

        if ($this->url) {
            return basename($this->url);
        }

        if ($this->file) {
            return basename($this->file->getUntrustedName());
        }

        return '';
    }

    /**
     * @param FileUpload $file
     * @return $this
     */
    private function setFile(FileUpload $file): self
    {
        if (! $file->isOk()) {
            throw new ValidationException('File is not OK');
        }

        $this->file = $file;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    private function setUrl(string $url): self
    {
        if (! str_starts_with($url, 'http')) {
            throw new ValidationException('Incorrect URL?');
        }

        $this->url = $url;

        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    private function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $data
     * @return $this
     */
    private function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string|null
     */
    private function load(): ?string
    {
        if ($this->file) {
            return $this->file->getContents();
        }

        if ($this->path) {
            return FileSystem::readSafe($this->path);
        }

        if ($this->url) {
            try {
                return (new GetRequest($this->url))->send()->getBody();
            } catch (RequestException) {
                return null;
            }
        }

        return null;
    }
}

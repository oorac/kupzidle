<?php declare(strict_types=1);

namespace App\Services\Deadpool;

use App\Helpers\UrlHelper;
use App\Models\Image as ImageModel;
use Nette\Utils\Image as NetteImage;

class Image
{
    /**
     * @var ImageModel
     */
    private ImageModel $source;

    /**
     * @var Processor
     */
//    private Processor $processor;

    /**
     * @var int|null
     */
    private ?int $width = null;

    /**
     * @var int|null
     */
    private ?int $height = null;

    /**
     * @var int
     */
    private int $quality = 90;

    /**
     * @var int|null
     */
    private ?int $format = null;

    /**
     * @var array
     */
    private array $flags = [];

    /**
     * @param ImageModel $source
     */
    public function __construct(ImageModel $source)
    {
        $this->source = $source;
//        $this->processor = $processor;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return ImageModel
     */
    public function getSource(): ImageModel
    {
        return $this->source;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Image
     */
    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Image
     */
    public function height(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     * @return Image
     */
    public function quality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFormat(): ?int
    {
        return $this->format;
    }

    /**
     * @return Image
     */
    public function jpeg(): self
    {
        $this->format = NetteImage::JPEG;

        return $this;
    }

    /**
     * @return Image
     */
    public function png(): self
    {
        $this->format = NetteImage::PNG;

        return $this;
    }

    /**
     * @return Image
     */
    public function gif(): self
    {
        $this->format = NetteImage::GIF;

        return $this;
    }

    /**
     * @return Image
     */
    public function webp(): self
    {
        $this->format = NetteImage::WEBP;

        return $this;
    }

    /**
     * @return int
     */
    public function getCompactedFlags(): int
    {
        return array_reduce($this->flags, static function ($a, $b) {
            return $a | $b;
        }, 0);
    }

    /**
     * @return Image
     */
    public function exact(): self
    {
        $this->flags[] = NetteImage::EXACT;

        return $this;
    }

    /**
     * @return Image
     */
    public function fill(): self
    {
        $this->flags[] = NetteImage::FILL;

        return $this;
    }

    /**
     * @return Image
     */
    public function fit(): self
    {
        $this->flags[] = NetteImage::FIT;

        return $this;
    }

    /**
     * @return Image
     */
    public function shrinkOnly(): self
    {
        $this->flags[] = NetteImage::SHRINK_ONLY;

        return $this;
    }

    /**
     * @return Image
     */
    public function stretch(): self
    {
        $this->flags[] = NetteImage::STRETCH;

        return $this;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->source->getStorage()->getResizeUrl($this);
    }
}

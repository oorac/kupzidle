<?php declare(strict_types=1);

namespace App\Services\Deadpool;

use App\Exceptions\NotFoundException;
use App\Models\Repositories\ImageRepository;

class Processor
{
    /**
     * @var ImageRepository
     */
    private ImageRepository $imageRepository;

    /**
     * @param ImageRepository $imageRepository
     */
    public function __construct(ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param array $arguments
     */
    public function processLocalRequest(array $arguments): void
    {
        $image = $this->imageRepository->findOneBy(['name' => $arguments['name']]);
        if (! $image) {
            throw new NotFoundException('Image `' . $arguments['name'] . '` not found');
        }

        $image->getStorage()->resize($arguments);
        exit;
    }
}

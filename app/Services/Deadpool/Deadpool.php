<?php declare(strict_types=1);

namespace App\Services\Deadpool;

use App\Models\Image;
use App\Services\Deadpool\Image as DeadpoolImage;

class Deadpool
{
    /**
     * @param Image $image
     * @return DeadpoolImage
     */
    public function image(Image $image): DeadpoolImage
    {
        return new DeadpoolImage($image);
    }
}

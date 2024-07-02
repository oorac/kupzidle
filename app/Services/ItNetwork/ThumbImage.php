<?php


namespace App\Services\ItNetwork;


class ThumbImage
{
    private $source;

    public function __construct($sourceImagePath)
    {
        $this->source = $sourceImagePath;
    }

    public function make_thumb($dest, $desired_width):bool {

        /* read the source image */
        $type = exif_imagetype($this->source);
        if($type == IMAGETYPE_JPEG)
            $source_image = imagecreatefromjpeg($this->source);
        if($type == IMAGETYPE_GIF)
            $source_image = imagecreatefromgif($this->source);
        if($type == IMAGETYPE_PNG)
            $source_image = imagecreatefrompng($this->source);
        if($type == IMAGETYPE_BMP)
            $source_image = imagecreatefrombmp($this->source);
        if(!$source_image)
            return false;

        $width = imagesx($source_image);
        $height = imagesy($source_image);

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);

        return true;
    }
}
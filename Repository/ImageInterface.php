<?php

namespace MageSuite\ImageResize\Repository;

interface ImageInterface
{
    /**
     * Gets original image content for specified path
     * @param string $path
     * @param bool $isFullImagePath
     * @return mixed
     */
    public function getOriginalImage($path, $isFullImagePath = false);

    /**
     * Saves resized image content to specified path
     * @param $path
     * @param $data
     * @return mixed
     */
    public function save($path, $data);
}

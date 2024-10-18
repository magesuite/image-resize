<?php

namespace MageSuite\ImageResize\Repository;

interface ImageInterface
{
    public function getOriginalImage(string $path, bool $isFullImagePath = false): string;

    public function save(string $path, $data): string;
}

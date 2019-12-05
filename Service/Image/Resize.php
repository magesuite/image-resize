<?php

namespace MageSuite\ImageResize\Service\Image;

class Resize
{
    const BACKGROUD_WHITE = 'white';
    const BACKGROUD_TRANSPARENT = 'transparent';

    const PNG_MIME_TYPE = 'image/png';

    protected $isFullImagePath = false;

    /**
     * @var \MageSuite\ImageResize\Repository\ImageInterface
     */
    protected $imageRepository;

    public function __construct(\MageSuite\ImageResize\Repository\ImageInterface $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    public function resize($configuration, $save = false)
    {
        $imageContents = $this->imageRepository->getOriginalImage($configuration['image_file'], $this->getIsFullImagePath());

        $image = new \Imagick();
        $image->readImageBlob($imageContents);

        $backgroundColor = $image->getImageMimeType() === self::PNG_MIME_TYPE ? self::BACKGROUD_TRANSPARENT : self::BACKGROUD_WHITE;

        $background = new \Imagick();
        $background->newImage($configuration['width'], $configuration['height'], $backgroundColor);

        $image->scaleImage($configuration['width'], $configuration['height'], true);

        $background->compositeImage(
            $image,
            \Imagick::COMPOSITE_DEFAULT,
            -($image->getImageWidth() - $configuration['width']) / 2,
            -($image->getImageHeight() - $configuration['height']) / 2
        );

        $background->setFilename($configuration['image_file']);

        if ($save) {
            $this->save($configuration['dest_path'], $background);
        }

        return $background;
    }

    public function save($requestUri, $image)
    {
        return $this->imageRepository->save($requestUri, (string)$image);
    }

    public function setIsFullImagePath($flag)
    {
        $this->isFullImagePath = $flag;
    }

    public function getIsFullImagePath()
    {
        return $this->isFullImagePath;
    }
}

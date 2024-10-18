<?php

namespace MageSuite\ImageResize\Service\Image;

class Resize
{
    const BACKGROUD_WHITE = 'white';
    const BACKGROUD_TRANSPARENT = 'transparent';
    const PNG_MIME_TYPE = 'image/png';
    const FORMAT_GIF = 'GIF';

    protected \MageSuite\ImageResize\Repository\ImageInterface $imageRepository;
    protected \MageSuite\ImageResize\Service\Image\Watermark $watermark;
    protected bool $isFullImagePath = false;

    public function __construct(
        \MageSuite\ImageResize\Repository\ImageInterface $imageRepository,
        \MageSuite\ImageResize\Service\Image\Watermark $watermark
    ) {
        $this->imageRepository = $imageRepository;
        $this->watermark = $watermark;
    }

    /**
     * @throws \ImagickException
     * @throws \MageSuite\ImageResize\Exception\EmptyImageLoaded
     */
    public function resize(array $configuration, bool $save = false): \Imagick
    {
        $imageContents = $this->imageRepository->getOriginalImage(
            $configuration['image_file'],
            $this->getIsFullImagePath()
        );

        if (empty($imageContents)) {
            throw new \MageSuite\ImageResize\Exception\EmptyImageLoaded();
        }

        $image = new \Imagick();
        $image->readImageBlob($imageContents);
        $colorspace = $image->getImageColorspace();

        if ($colorspace === \Imagick::COLORSPACE_CMYK || $colorspace === \Imagick::COLORSPACE_UNDEFINED) {
            $image->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
        }

        $format = $image->getImageFormat();

        if ($format == self::FORMAT_GIF) {
            $background = $this->resizeGifImage($image, $configuration);
        } else {
            $background = $this->resizeImage($image, $configuration);
        }

        $background->setFilename($configuration['image_file']);

        $this->watermark->apply($background, $configuration);

        if ($save) {
            $this->save($configuration['dest_path'], $background);
        }

        return $background;
    }

    /**
     * @throws \ImagickException
     */
    protected function resizeImage(\Imagick $originalImage, array $configuration): \Imagick
    {
        $backgroundColor = self::BACKGROUD_WHITE;

        if ($originalImage->getImageMimeType() === self::PNG_MIME_TYPE) {
            $backgroundColor = self::BACKGROUD_TRANSPARENT;
        }

        $background = new \Imagick();
        $background->newImage(
            $configuration['width'],
            $configuration['height'],
            $backgroundColor
        );
        $originalImage->scaleImage(
            $configuration['width'],
            $configuration['height'],
            true
        );
        $background->compositeImage(
            $originalImage,
            \Imagick::COMPOSITE_DEFAULT,
            -(int)(($originalImage->getImageWidth() - $configuration['width']) / 2),
            -(int)(($originalImage->getImageHeight() - $configuration['height']) / 2)
        );

        return $background;
    }

    /**
     * @throws \ImagickException
     */
    protected function resizeGifImage(\Imagick $originalImage, array $configuration)
    {
        $originalImage = $originalImage->coalesceImages();

        do {
            $originalImage->resizeImage(
                $configuration['width'],
                $configuration['height'],
                \Imagick::FILTER_BOX,
                1
            );
        } while ($originalImage->nextImage());

        return $originalImage->deconstructImages();
    }

    public function save(string $requestUri, \Imagick $image): string
    {
        $imageContent = (string)$image;

        try {
            $format = $image->getImageFormat();

            if ($format == self::FORMAT_GIF) {
                $imageContent = $image->getImagesBlob();
            }
        } catch (\ImagickException $e) {
            ;// do nothing
        }

        return $this->imageRepository->save($requestUri, $imageContent);
    }

    public function setIsFullImagePath(bool $flag): self
    {
        $this->isFullImagePath = $flag;
        return $this;
    }

    public function getIsFullImagePath(): bool
    {
        return $this->isFullImagePath;
    }
}

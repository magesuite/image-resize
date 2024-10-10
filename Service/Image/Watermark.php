<?php
declare(strict_types=1);

namespace MageSuite\ImageResize\Service\Image;

class Watermark
{
    protected \MageSuite\ImageResize\Repository\ImageInterface $imageRepository;
    protected \MageSuite\ImageResize\Model\WatermarkConfiguration $configuration;

    public function __construct(
        \MageSuite\ImageResize\Repository\ImageInterface $imageRepository,
        \MageSuite\ImageResize\Model\WatermarkConfiguration $configuration
    ) {
        $this->imageRepository = $imageRepository;
        $this->configuration = $configuration;
    }

    public function apply(\Imagick $originalImage, array $configuration): void
    {
        $watermarkConfiguration = $configuration['watermark'] ?? '';
        if (empty($watermarkConfiguration)) {
            return;
        }
        $this->configuration->decode($watermarkConfiguration);

        $watermark = $this->createWatermark();
        if ($watermark === null) {
            return;
        }

        $watermark = $this->processWatermark($originalImage, $watermark);
        list($x, $y) = $this->getWatermarkPosition($originalImage, $watermark);
        $originalImage->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
        $watermark->destroy();
    }

    protected function createWatermark(): ?\Imagick
    {
        try {
            $watermarkFileContent = $this->imageRepository->getOriginalImage($this->configuration->getImage(), true);
        } catch (\MageSuite\ImageResize\Exception\OriginalImageNotFound $e) {
            return null;
        }

        $watermark = new \Imagick();
        $watermark->readImageBlob($watermarkFileContent);
        $watermark->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
        $watermark->setImageBackgroundColor('none');
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $this->configuration->getOpacityAsFloat(), \Imagick::CHANNEL_ALPHA);
        $watermark->scaleImage($this->configuration->getWidth(), $this->configuration->getHeight());

        return $watermark;
    }

    protected function getWatermarkPosition(\Imagick $originalImage, \Imagick $watermark): array
    {
        $x = $y = 0;

        switch ($this->configuration->getPosition()) {
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_LEFT:
                $x = $this->configuration->getOffsetX();
                $y = $this->configuration->getOffsetY();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_RIGHT:
                $x = $originalImage->getImageWidth() - $watermark->getImageWidth() - $this->configuration->getOffsetX();
                $y = $this->configuration->getOffsetY();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_LEFT:
                $y = $originalImage->getImageHeight() - $watermark->getImageHeight() - $this->configuration->getOffsetY();
                $x = $this->configuration->getOffsetX();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT:
                $x = $originalImage->getImageWidth() - $watermark->getImageWidth() - $this->configuration->getOffsetX();
                $y = $originalImage->getImageHeight() - $watermark->getImageHeight() - $this->configuration->getOffsetY();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_CENTER:
                $x = (int)(($originalImage->getImageWidth() - $watermark->getImageWidth()) / 2);
                $y = (int)(($originalImage->getImageHeight() - $watermark->getImageHeight()) / 2);
                break;
        }

        return [$x, $y];
    }

    protected function processWatermark(\Imagick $originalImage, \Imagick $watermarkImage): \Imagick
    {
        switch ($this->configuration->getPosition()) {
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_STRETCH:
                $watermarkImage->scaleImage($originalImage->getImageWidth(), $originalImage->getImageHeight());
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TILE:
                $tiledImage = new \Imagick();
                $tiledImage->newImage($originalImage->getImageWidth(), $originalImage->getImageHeight(), 'none');
                $tiledImage->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
                $watermarkImage = $tiledImage->textureImage($watermarkImage);
                $tiledImage->destroy();
                break;
        }

        return $watermarkImage;
    }
}

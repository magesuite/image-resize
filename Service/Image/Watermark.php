<?php
declare(strict_types=1);

namespace MageSuite\ImageResize\Service\Image;

// @phpcs:disable MageSuite.TooMany.MethodArguments
class Watermark
{
    protected \MageSuite\ImageResize\Repository\ImageInterface $imageRepository;

    public function __construct(
        \MageSuite\ImageResize\Repository\ImageInterface $imageRepository
    ) {
        $this->imageRepository = $imageRepository;
    }

    public function apply(\Imagick $originalImage, array $configuration): void
    {
        $watermarkConfiguration = $configuration['watermark'] ?? '';
        if (empty($watermarkConfiguration)) {
            return;
        }
        $config = new \MageSuite\ImageResize\Model\WatermarkConfiguration();
        $config->decrypt($watermarkConfiguration);

        $watermark = $this->createWatermark($config);
        if ($watermark === null) {
            return;
        }

        $watermark = $this->processWatermark($originalImage, $watermark, $config);
        list($x, $y) = $this->getWatermarkPosition($originalImage, $watermark, $config);
        $originalImage->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
        $watermark->destroy();
    }

    protected function createWatermark(\MageSuite\ImageResize\Model\WatermarkConfiguration $config): ?\Imagick
    {
        try {
            $watermarkFileContent = $this->imageRepository->getOriginalImage('/watermark/' . $config->getImage());
        } catch (\MageSuite\ImageResize\Exception\OriginalImageNotFound $e) {
            return null;
        }

        $watermark = new \Imagick();
        $watermark->readImageBlob($watermarkFileContent);
        $watermark->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);
        $watermark->setImageBackgroundColor('none');
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $config->getOpacityAsFloat(), \Imagick::CHANNEL_ALPHA);
        $watermark->scaleImage($config->getWidth(), $config->getHeight());

        return $watermark;
    }

    protected function getWatermarkPosition(\Imagick $originalImage, \Imagick $watermark, \MageSuite\ImageResize\Model\WatermarkConfiguration $config): array
    {
        $x = $y = 0;

        switch ($config->getPosition()) {
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_RIGHT:
                $x = $originalImage->getImageWidth() - $watermark->getImageWidth();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_LEFT:
                $y = $originalImage->getImageHeight() - $watermark->getImageHeight();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT:
                $x = $originalImage->getImageWidth() - $watermark->getImageWidth();
                $y = $originalImage->getImageHeight() - $watermark->getImageHeight();
                break;
            case \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_CENTER:
                $x = (int)(($originalImage->getImageWidth() - $watermark->getImageWidth()) / 2);
                $y = (int)(($originalImage->getImageHeight() - $watermark->getImageHeight()) / 2);
                break;
        }

        return [$x, $y];
    }

    protected function processWatermark(\Imagick $originalImage, \Imagick $watermarkImage, \MageSuite\ImageResize\Model\WatermarkConfiguration $config): \Imagick
    {
        switch ($config->getPosition()) {
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

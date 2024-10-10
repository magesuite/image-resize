<?php
declare(strict_types=1);

namespace MageSuite\ImageResize\Model\Encoder;

class WatermarkEncoder
{
    public const PACK_FORMAT = 'CCVVvVVVV';
    public const UNPACK_FORMAT = 'CformatVersion/Cposition/VwatermarkWidth/VwatermarkHeight/vopacity/VoffsetX/VoffsetY/Vfilesize/VimagePathLength';
    public const FORMAT_VERSION = 0;

    public const POSITION_MAPPING = [
        0 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_LEFT,
        1 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_RIGHT,
        2 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_LEFT,
        3 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_BOTTOM_RIGHT,
        4 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_CENTER,
        5 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_STRETCH,
        6 => \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TILE
    ];

    public function encode(\MageSuite\ImageResize\Model\WatermarkConfiguration $configuration): string
    {
        $packedData = pack(
            self::PACK_FORMAT,
            self::FORMAT_VERSION,
            $this->getPositionCode($configuration->getPosition()),
            $configuration->getWidth(),
            $configuration->getHeight(),
            $this->mapValue($configuration->getOpacity(), 0, 100, 0, 255),
            $configuration->getOffsetX(),
            $configuration->getOffsetY(),
            $configuration->getFilesize(),
            strlen($configuration->getImage())
        );

        return $this->base64encode($packedData . $configuration->getImage());
    }

    public function decode(\MageSuite\ImageResize\Model\WatermarkConfiguration $configuration, string $data): \MageSuite\ImageResize\Model\WatermarkConfiguration
    {
        $decodedData = $this->base64decode($data);

        try {
            $unpackedData = unpack(self::UNPACK_FORMAT, $decodedData); // phpcs:ignore
        } catch (\Exception $e) {
            throw new \MageSuite\ImageResize\Exception\WatermarkException('Unable to unpack watermark data');
        }

        if ($unpackedData['formatVersion'] !== self::FORMAT_VERSION) {
            throw new \MageSuite\ImageResize\Exception\WatermarkException('Unsupported format version: ' . self::FORMAT_VERSION);
        }

        $image = substr($decodedData, 28);
        if (strlen($image) !== $unpackedData['imagePathLength']) {
            throw new \MageSuite\ImageResize\Exception\WatermarkException('Invalid watermark image path');
        }

        $configuration->setPosition($this->getPositionName($unpackedData['position']));
        $configuration->setWidth($unpackedData['watermarkWidth']);
        $configuration->setHeight($unpackedData['watermarkHeight']);
        $configuration->setOpacity($this->mapValue($unpackedData['opacity'], 0, 255, 0, 100));
        $configuration->setOffsetX($unpackedData['offsetX']);
        $configuration->setOffsetY($unpackedData['offsetY']);
        $configuration->setFilesize($unpackedData['filesize']);
        $configuration->setImage($image);

        return $configuration;
    }

    protected function getPositionCode(?string $position): int
    {
        $positionMapping = array_flip(self::POSITION_MAPPING);

        return $positionMapping[$position] ?? 0;
    }

    protected function getPositionName(int $positionCode): string
    {
        return self::POSITION_MAPPING[$positionCode] ?? \Magento\Framework\Image\Adapter\AbstractAdapter::POSITION_TOP_LEFT;
    }

    protected function mapValue(int $value, int $fromMin, int $fromMax, int $toMin, int $toMax): int // phpcs:ignore
    {
        $rangeRatio = ($toMax - $toMin) / ($fromMax - $fromMin);
        $scaledValue = round(($value - $fromMin) * $rangeRatio);

        return $toMin + (int)$scaledValue;
    }

    protected function base64encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64decode(string $data): ?string
    {
        $decodedData = base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); // phpcs:ignore

        if ($decodedData === false) {
            return null;
        }

        return $decodedData;
    }
}

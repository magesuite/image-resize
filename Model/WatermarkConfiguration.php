<?php
declare(strict_types=1);

namespace MageSuite\ImageResize\Model;

class WatermarkConfiguration extends \Magento\Framework\DataObject
{
    public const KEY_IMAGE = 'image';
    public const KEY_POSITION = 'position';
    public const KEY_OPACITY = 'opacity';
    public const KEY_WIDTH = 'width';
    public const KEY_HEIGHT = 'height';
    public const KEY_OFFSET_X = 'offset_x';
    public const KEY_OFFSET_Y = 'offset_y';
    public const KEY_FILE_SIZE = 'file_size';

    public const SIZE_SEPARATOR = 'x';
    public const VALIDATION_KEYS = [
        self::KEY_IMAGE,
        self::KEY_POSITION,
        self::KEY_OPACITY,
        self::KEY_WIDTH,
        self::KEY_HEIGHT,
        self::KEY_FILE_SIZE,
    ];

    protected \MageSuite\ImageResize\Model\Encoder\WatermarkEncoder $watermarkEncoder;

    public function __construct(
        \MageSuite\ImageResize\Model\Encoder\WatermarkEncoder $watermarkEncoder,
        $data = []
    ) {
        parent::__construct($data);
        $this->watermarkEncoder = $watermarkEncoder;
    }

    public function getImage(): ?string
    {
        return $this->getData(self::KEY_IMAGE);
    }

    public function setImage(?string $image): self
    {
        return $this->setData(self::KEY_IMAGE, $image);
    }

    public function getPosition(): ?string
    {
        return $this->getData(self::KEY_POSITION);
    }

    public function setPosition(?string $position): self
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    public function getOpacity(): int
    {
        return (int)$this->getData(self::KEY_OPACITY);
    }

    public function setOpacity($opacity): self
    {
        return $this->setData(self::KEY_OPACITY, $opacity);
    }

    public function getWidth(): int
    {
        return (int)$this->getData(self::KEY_WIDTH);
    }

    public function setWidth($width): self
    {
        return $this->setData(self::KEY_WIDTH, $width);
    }

    public function getHeight(): int
    {
        return (int)$this->getData(self::KEY_HEIGHT);
    }

    public function setHeight($height): self
    {
        return $this->setData(self::KEY_HEIGHT, $height);
    }

    public function setOffsetX(?int $offset): self
    {
        return $this->setData(self::KEY_OFFSET_X, $offset);
    }

    public function setOffsetY(?int $offset): self
    {
        return $this->setData(self::KEY_OFFSET_Y, $offset);
    }

    public function getOffsetX(): int
    {
        return (int)$this->getData(self::KEY_OFFSET_X);
    }

    public function getOffsetY(): int
    {
        return (int)$this->getData(self::KEY_OFFSET_Y);
    }

    public function setFilesize(int $filesize): self
    {
        return $this->setData(self::KEY_FILE_SIZE, $filesize);
    }

    public function getFilesize(): int
    {
        return (int)$this->getData(self::KEY_FILE_SIZE);
    }

    public function setSize(string $size): self
    {
        list($width, $height) = explode(self::SIZE_SEPARATOR, $size . self::SIZE_SEPARATOR);
        $this->setWidth((int)$width);
        $this->setHeight((int)$height);

        return $this;
    }

    public function encode(): string
    {
        return $this->watermarkEncoder->encode($this);
    }

    public function decode(string $data): self
    {
        return $this->watermarkEncoder->decode($this, $data);
    }

    public function getOpacityAsFloat(): float
    {
        return $this->getOpacity() / 100;
    }

    public function isValid(): bool
    {
        $filledData = array_filter($this->getData());
        $filledKeys = array_intersect_key($filledData, array_flip(self::VALIDATION_KEYS));
        
        return count($filledKeys) === count(self::VALIDATION_KEYS);
    }

    public function __toString(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        return $this->encode();
    }
}

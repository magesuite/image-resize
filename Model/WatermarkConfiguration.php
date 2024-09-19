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
    public const SERIALIZATION_SEPARATOR = '|';
    public const SIZE_SEPARATOR = 'x';

    public const SERIALIZATION_KEYS = [
        self::KEY_IMAGE,
        self::KEY_POSITION,
        self::KEY_OPACITY,
        self::KEY_WIDTH,
        self::KEY_HEIGHT
    ];

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

    public function getOpacity(): ?int
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

    public function setSize(string $size): self
    {
        list($width, $height) = explode(self::SIZE_SEPARATOR, $size . self::SIZE_SEPARATOR);
        $this->setWidth((int)$width);
        $this->setHeight((int)$height);

        return $this;
    }

    public function encrypt(): string
    {
        $data = [];
        foreach (self::SERIALIZATION_KEYS as $key) {
            $data[] = $this->getData($key);
        }

        return bin2hex(implode(self::SERIALIZATION_SEPARATOR, $data));
    }

    public function decrypt(string $data): self
    {
        $data = (string)hex2bin($data);
        $values = explode(self::SERIALIZATION_SEPARATOR, $data);

        foreach (self::SERIALIZATION_KEYS as $index => $key) {
            $this->setData($key, $values[$index] ?? null);
        }
        return $this;
    }

    public function getOpacityAsFloat(): float
    {
        return $this->getOpacity() / 100;
    }

    public function isValid(): bool
    {
        $filledKeys = array_filter($this->getData());
        return count($filledKeys) === count(self::SERIALIZATION_KEYS);
    }

    public function __toString(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        return $this->encrypt();
    }
}

<?php

namespace MageSuite\ImageResize\Repository;

// phpcs:disable Magento2.Functions.DiscouragedFunction,Generic.PHP.NoSilencedErrors.Discouraged
class File implements ImageInterface
{
    protected ?string $mediaDirectoryPath;

    public function __construct()
    {
        $this->mediaDirectoryPath = BP . '/pub/media';
    }

    public function setMediaDirectoryPath(string $path): void
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Folder %s does not exist", $path));
        }

        $this->mediaDirectoryPath = $path;
    }

    /**
     * @throws \MageSuite\ImageResize\Exception\OriginalImageNotFound
     */
    public function getOriginalImage(string $path, bool $isFullImagePath = false): string
    {
        $imagePath = $isFullImagePath ? '/' . $path : '/catalog/product' . $path;

        $contents = @file_get_contents($this->mediaDirectoryPath . $imagePath);

        if ($contents === false) {
            throw new \MageSuite\ImageResize\Exception\OriginalImageNotFound();
        }

        return $contents;
    }

    public function save(string $path, $data): string
    {
        $path = $this->mediaDirectoryPath . '/' . $path;

        $targetDirectory = $this->normalizePath(dirname($path));

        if (!file_exists($targetDirectory)) {
            @mkdir($targetDirectory, 0777, true);
        }

        file_put_contents($path, (string)$data);

        return $path;
    }

    private function normalizePath($path): string
    {
        return array_reduce(explode('/', $path), function ($a, $b) {
            if ($a === 0) {
                $a = '/';
            }
            if ($b === '' || $b === '.') {
                return $a;
            }
            if ($b === '..') {
                return dirname($a);
            }
            return preg_replace("/\/+/", "/", "$a/$b");
        }, 0);
    }
}
// phpcs:enable

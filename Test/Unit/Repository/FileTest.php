<?php

namespace MageSuite\ImageResize\Test\Unit\Repository;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    protected $assetsDirectoryPath;

    /**
     * @var \MageSuite\ImageResize\Repository\File
     */
    protected $fileRepository;

    public function setUp(): void
    {
        $this->fileRepository = new \MageSuite\ImageResize\Repository\File();

        $this->assetsDirectoryPath = realpath(__DIR__ . '/../assets');

        $this->fileRepository->setMediaDirectoryPath($this->assetsDirectoryPath);

        $this->cleanUpThumbnailsDirectory();
    }

    public function tearDown(): void
    {
        $this->cleanUpThumbnailsDirectory();
    }

    public function testItGetsFileContentsProperly()
    {
        $this->assertEquals('existing_file_contents', $this->fileRepository->getOriginalImage('/existing_file'));
    }

    public function testItThrowsExceptionWhenOriginalImageWasNotFound()
    {
        $this->expectException(\MageSuite\ImageResize\Exception\OriginalImageNotFound::class);

        $this->fileRepository->getOriginalImage('/not_existing_file');
    }

    public function testItSavesFileContentsProperly()
    {
        $this->fileRepository->save('catalog/product/thumbnail/500x500/test', 'test_data');

        $targetFilePath = $this->assetsDirectoryPath . '/catalog/product/thumbnail/500x500/test';

        $this->assertTrue(file_exists($targetFilePath));
        $this->assertEquals('test_data', file_get_contents($targetFilePath));
    }

    protected function cleanUpThumbnailsDirectory()
    {
        if (file_exists($this->assetsDirectoryPath . '/catalog/product/thumbnail')) {
            $this->deleteDirectory($this->assetsDirectoryPath . '/catalog/product/thumbnail');
        }
    }

    public function deleteDirectory($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

<?php
declare(strict_types=1);

namespace MageSuite\ImageResize\Test\Unit\Model\Encoder;

class WatermarkEncoderTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\ImageResize\Model\Encoder\WatermarkEncoder $watermarkEncoder;

    public function setUp(): void
    {
        $this->watermarkEncoder = new \MageSuite\ImageResize\Model\Encoder\WatermarkEncoder();
    }
    public function testItEncodesWatermarkProperly()
    {
        $watermark = new \MageSuite\ImageResize\Model\WatermarkConfiguration($this->watermarkEncoder);
        $watermark->setImage('/catalog/product/watermark/watermark.png');
        $watermark->setWidth(100);
        $watermark->setHeight(100);
        $watermark->setOpacity(50);
        $watermark->setOffsetX(10);
        $watermark->setOffsetY(10);
        $watermark->setFilesize(1234);
        $watermark->setPosition('top-right');

        $this->assertEquals('AAFkAAAAZAAAAIAACgAAAAoAAADSBAAAKAAAAC9jYXRhbG9nL3Byb2R1Y3Qvd2F0ZXJtYXJrL3dhdGVybWFyay5wbmc', $watermark->encode());
    }

    public function testItDecodesWatermarkProperly()
    {
        $encodedWatermark = 'AAFkAAAAZAAAAIAACgAAAAoAAADSBAAAKAAAAC9jYXRhbG9nL3Byb2R1Y3Qvd2F0ZXJtYXJrL3dhdGVybWFyay5wbmc';
        $watermark = new \MageSuite\ImageResize\Model\WatermarkConfiguration($this->watermarkEncoder);
        $watermark->decode($encodedWatermark);

        $this->assertEquals('/catalog/product/watermark/watermark.png', $watermark->getImage());
        ;
        $this->assertEquals(100, $watermark->getWidth());
        $this->assertEquals(100, $watermark->getHeight());
        $this->assertEquals(50, $watermark->getOpacity());
        $this->assertEquals(10, $watermark->getOffsetX());
        $this->assertEquals(10, $watermark->getOffsetY());
        $this->assertEquals(1234, $watermark->getFilesize());
        $this->assertEquals('top-right', $watermark->getPosition());

        $this->assertEquals($encodedWatermark, $watermark->encode());
    }

    public function testDecodingInvalidData()
    {
        $watermark = new \MageSuite\ImageResize\Model\WatermarkConfiguration($this->watermarkEncoder);
        $this->expectException(\MageSuite\ImageResize\Exception\WatermarkException::class);
        $watermark->decode('invalid_data');
    }
}

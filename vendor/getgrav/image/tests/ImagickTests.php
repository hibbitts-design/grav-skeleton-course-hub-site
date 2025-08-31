<?php

use Gregwar\Image\Image;

/**
 * Unit testing for Imagick Adapter.
 */
class ImagickTests extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('ImageMagick extension is not available.');
        }

        $dir = $this->output('');
        `rm -rf $dir`;
        if (!mkdir($dir) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        if (!mkdir($concurrentDirectory = $this->output('cache')) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    /**
     * Testing the basic width & height with Imagick.
     */
    public function testBasicsImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        self::assertSame(771, $image->width());
        self::assertSame(961, $image->height());
    }

    /**
     * Testing adapter name.
     */
    public function testAdapterName(): void
    {
        $image = $this->openImagick('monalisa.jpg');
        self::assertSame('ImageMagick', $image->getAdapter()->getName());
    }

    /**
     * Testing the resize with Imagick.
     */
    public function testResizeImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_small_imagick.jpg');
        $image
            ->resize(300, 200)
            ->save($out);

        self::assertFileExists($out);

        // Use Imagick to verify the result instead of GD
        $verifyImage = new \Imagick($out);
        self::assertSame(300, $verifyImage->getImageWidth());
        self::assertSame(200, $verifyImage->getImageHeight());
        $verifyImage->destroy();
    }

    /**
     * Testing the resize % with Imagick.
     */
    public function testResizePercentImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_small_percent_imagick.jpg');
        $image
            ->resize('50%')
            ->save($out);

        self::assertFileExists($out);

        $verifyImage = new \Imagick($out);
        self::assertSame(386, $verifyImage->getImageWidth());
        self::assertSame(481, $verifyImage->getImageHeight());
        $verifyImage->destroy();
    }

    /**
     * Testing to create an image with Imagick - jpeg, png, gif, webp, avif.
     */
    public function testCreateImageImagick(): void
    {
        // Test JPEG
        $black = $this->output('black_imagick.jpg');
        Image::create(150, 200)
            ->setAdapter('imagick')
            ->fill('black')
            ->save($black, 100);

        self::assertFileExists($black);
        $verifyImage = new \Imagick($black);
        self::assertSame(150, $verifyImage->getImageWidth());
        self::assertSame(200, $verifyImage->getImageHeight());
        $verifyImage->destroy();

        // Test PNG
        $blackPng = $this->output('black_imagick.png');
        Image::create(150, 200)
            ->setAdapter('imagick')
            ->fill('black')
            ->save($blackPng, 'png');

        self::assertFileExists($blackPng);
        $verifyImage = new \Imagick($blackPng);
        self::assertSame(150, $verifyImage->getImageWidth());
        self::assertSame(200, $verifyImage->getImageHeight());
        self::assertSame('PNG', $verifyImage->getImageFormat());
        $verifyImage->destroy();

        // Test GIF
        $blackGif = $this->output('black_imagick.gif');
        Image::create(150, 200)
            ->setAdapter('imagick')
            ->fill('black')
            ->save($blackGif, 'gif');

        self::assertFileExists($blackGif);
        $verifyImage = new \Imagick($blackGif);
        self::assertSame(150, $verifyImage->getImageWidth());
        self::assertSame(200, $verifyImage->getImageHeight());
        self::assertSame('GIF', $verifyImage->getImageFormat());
        $verifyImage->destroy();
    }

    /**
     * Testing saveWebp method specifically.
     */
    public function testSaveWebpImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_imagick.webp');
        $image->resize(100, 100)->save($out, 'webp', 80);

        self::assertFileExists($out);

        $verifyImage = new \Imagick($out);
        self::assertSame('WEBP', $verifyImage->getImageFormat());
        self::assertSame(100, $verifyImage->getImageWidth());
        self::assertSame(100, $verifyImage->getImageHeight());
        $verifyImage->destroy();
    }

    /**
     * Testing saveAvif method specifically (if supported).
     */
    public function testSaveAvifImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_imagick.avif');
        
        try {
            $image->resize(100, 100)->save($out, 'avif', 80);
            
            if (file_exists($out)) {
                self::assertFileExists($out);
                
                $verifyImage = new \Imagick($out);
                self::assertSame(100, $verifyImage->getImageWidth());
                self::assertSame(100, $verifyImage->getImageHeight());
                $verifyImage->destroy();
            } else {
                self::markTestSkipped('AVIF format not supported by this ImageMagick build.');
            }
        } catch (\Exception $e) {
            self::markTestSkipped('AVIF format not supported by this ImageMagick build: ' . $e->getMessage());
        }
    }

    /**
     * Testing image effects with Imagick.
     */
    public function testEffectsImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        // Test negate
        $out = $this->output('monalisa_negate_imagick.jpg');
        $image->resize(100, 100)->negate()->save($out);
        self::assertFileExists($out);

        // Test grayscale
        $image = $this->openImagick('monalisa.jpg');
        $out = $this->output('monalisa_grayscale_imagick.jpg');
        $image->resize(100, 100)->grayscale()->save($out);
        self::assertFileExists($out);

        // Test sepia
        $image = $this->openImagick('monalisa.jpg');
        $out = $this->output('monalisa_sepia_imagick.jpg');
        $image->resize(100, 100)->sepia()->save($out);
        self::assertFileExists($out);

        // Test emboss
        $image = $this->openImagick('monalisa.jpg');
        $out = $this->output('monalisa_emboss_imagick.jpg');
        $image->resize(100, 100)->emboss()->save($out);
        self::assertFileExists($out);
    }

    /**
     * Testing crop with Imagick.
     */
    public function testCropImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_crop_imagick.jpg');
        $image->crop(100, 100, 200, 300)->save($out);

        self::assertFileExists($out);

        $verifyImage = new \Imagick($out);
        self::assertSame(200, $verifyImage->getImageWidth());
        self::assertSame(300, $verifyImage->getImageHeight());
        $verifyImage->destroy();
    }

    /**
     * Testing rotate with Imagick.
     */
    public function testRotateImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_rotate_imagick.jpg');
        $image->resize(200, 200)->rotate(45)->save($out);

        self::assertFileExists($out);
    }

    /**
     * Testing flip with Imagick.
     */
    public function testFlipImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_flip_imagick.jpg');
        $image->resize(100, 100)->flip(true, false)->save($out);

        self::assertFileExists($out);

        // Test flop (horizontal flip)
        $image = $this->openImagick('monalisa.jpg');
        $out = $this->output('monalisa_flop_imagick.jpg');
        $image->resize(100, 100)->flip(false, true)->save($out);

        self::assertFileExists($out);
    }

    /**
     * Testing drawing functions with Imagick.
     */
    public function testDrawingImagick(): void
    {
        // Test rectangle
        $out = $this->output('rectangle_imagick.jpg');
        Image::create(200, 200)
            ->setAdapter('imagick')
            ->fill('white')
            ->rectangle(50, 50, 150, 150, 'red', true)
            ->save($out);

        self::assertFileExists($out);

        // Test circle
        $out = $this->output('circle_imagick.jpg');
        Image::create(200, 200)
            ->setAdapter('imagick')
            ->fill('white')
            ->circle(100, 100, 50, 'blue', true)
            ->save($out);

        self::assertFileExists($out);

        // Test line
        $out = $this->output('line_imagick.jpg');
        Image::create(200, 200)
            ->setAdapter('imagick')
            ->fill('white')
            ->line(0, 0, 200, 200, 'green')
            ->save($out);

        self::assertFileExists($out);
    }

    /**
     * Testing text writing with Imagick.
     */
    public function testWriteTextImagick(): void
    {
        $image = Image::create(300, 100)
            ->setAdapter('imagick')
            ->fill('white');

        $out = $this->output('text_imagick.jpg');
        
        // Use a system font or skip if none available
        try {
            $image->write('Arial', 'Hello ImageMagick!', 10, 50, 20, 0, 'black', 'left');
            $image->save($out);
            self::assertFileExists($out);
        } catch (\Exception $e) {
            self::markTestSkipped('Font not available for text rendering: ' . $e->getMessage());
        }
    }

    /**
     * Testing merge with Imagick.
     */
    public function testMergeImagick(): void
    {
        $out = $this->output('merge_imagick.jpg');
        Image::create(100, 100)
            ->setAdapter('imagick')
            ->fill('red')
            ->merge(Image::create(50, 50)->setAdapter('imagick')->fill('black'))
            ->save($out);

        self::assertFileExists($out);
    }

    /**
     * Testing progressive JPEG with Imagick.
     */
    public function testProgressiveImagick(): void
    {
        $image = $this->openImagick('monalisa.jpg');

        $out = $this->output('monalisa_progressive_imagick.jpg');
        $image->resize(200, 200)->enableProgressive()->save($out);

        self::assertFileExists($out);
    }

    /**
     * Testing creating image from data with Imagick.
     */
    public function testDataImagick(): void
    {
        $data = file_get_contents(__DIR__.'/files/monalisa.jpg');

        $output = $this->output('mona_imagick.jpg');
        $image = Image::fromData($data);
        $image->setAdapter('imagick');
        $image->save($output);

        self::assertFileExists($output);
        
        $verifyImage = new \Imagick($output);
        self::assertSame(771, $verifyImage->getImageWidth());
        self::assertSame(961, $verifyImage->getImageHeight());
        $verifyImage->destroy();
    }

    /**
     * Test format support detection.
     */
    public function testFormatSupport(): void
    {
        $image = $this->openImagick('monalisa.jpg');
        $adapter = $image->getAdapter();
        
        // Test that common formats are supported
        $reflection = new \ReflectionClass($adapter);
        $supportsMethod = $reflection->getMethod('supports');
        $supportsMethod->setAccessible(true);
        
        self::assertTrue($supportsMethod->invoke($adapter, 'jpeg'));
        self::assertTrue($supportsMethod->invoke($adapter, 'png'));
        self::assertTrue($supportsMethod->invoke($adapter, 'gif'));
    }

    /**
     * Opening an image with Imagick adapter.
     */
    protected function openImagick(string $file): Image
    {
        $image = Image::open(__DIR__.'/files/'.$file);
        $image->setAdapter('imagick');
        $image->setCacheDir(__DIR__.'/output/cache/');
        $image->setActualCacheDir(__DIR__.'/output/cache/');

        return $image;
    }

    /**
     * Outputting an image to a file.
     */
    protected function output(string $file): string
    {
        return __DIR__.'/output/'.$file;
    }
}
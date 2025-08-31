<?php

use Gregwar\Image\Image;

/**
 * Comparison tests between GD and Imagick adapters.
 */
class AdapterComparisonTests extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
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
     * Test that both adapters report the same image dimensions.
     */
    public function testDimensionsConsistency(): void
    {
        $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
        $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

        self::assertSame($imageGd->width(), $imageImagick->width());
        self::assertSame($imageGd->height(), $imageImagick->height());
    }

    /**
     * Test that both adapters produce the same resized dimensions.
     */
    public function testResizeConsistency(): void
    {
        $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
        $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

        $outGd = $this->output('monalisa_resize_gd.jpg');
        $outImagick = $this->output('monalisa_resize_imagick.jpg');

        $imageGd->resize(300, 200)->save($outGd);
        $imageImagick->resize(300, 200)->save($outImagick);

        self::assertFileExists($outGd);
        self::assertFileExists($outImagick);

        // Verify dimensions using each adapter's native functions
        $gdResource = imagecreatefromjpeg($outGd);
        self::assertSame(300, imagesx($gdResource));
        self::assertSame(200, imagesy($gdResource));

        if (extension_loaded('imagick')) {
            $imagickResource = new \Imagick($outImagick);
            self::assertSame(300, $imagickResource->getImageWidth());
            self::assertSame(200, $imagickResource->getImageHeight());
            $imagickResource->destroy();
        }
    }

    /**
     * Test that both adapters handle crop operations consistently.
     */
    public function testCropConsistency(): void
    {
        $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
        $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

        $outGd = $this->output('monalisa_crop_gd.jpg');
        $outImagick = $this->output('monalisa_crop_imagick.jpg');

        $imageGd->crop(100, 100, 200, 300)->save($outGd);
        $imageImagick->crop(100, 100, 200, 300)->save($outImagick);

        self::assertFileExists($outGd);
        self::assertFileExists($outImagick);

        // Verify dimensions
        $gdResource = imagecreatefromjpeg($outGd);
        self::assertSame(200, imagesx($gdResource));
        self::assertSame(300, imagesy($gdResource));

        if (extension_loaded('imagick')) {
            $imagickResource = new \Imagick($outImagick);
            self::assertSame(200, $imagickResource->getImageWidth());
            self::assertSame(300, $imagickResource->getImageHeight());
            $imagickResource->destroy();
        }
    }

    /**
     * Test that both adapters handle image creation consistently.
     */
    public function testCreateImageConsistency(): void
    {
        $imageGd = Image::create(150, 200)->setAdapter('gd')->fill('red');
        $imageImagick = Image::create(150, 200)->setAdapter('imagick')->fill('red');

        $outGd = $this->output('created_gd.jpg');
        $outImagick = $this->output('created_imagick.jpg');

        $imageGd->save($outGd);
        $imageImagick->save($outImagick);

        self::assertFileExists($outGd);
        self::assertFileExists($outImagick);

        // Verify dimensions
        $gdResource = imagecreatefromjpeg($outGd);
        self::assertSame(150, imagesx($gdResource));
        self::assertSame(200, imagesy($gdResource));

        if (extension_loaded('imagick')) {
            $imagickResource = new \Imagick($outImagick);
            self::assertSame(150, $imagickResource->getImageWidth());
            self::assertSame(200, $imagickResource->getImageHeight());
            $imagickResource->destroy();
        }
    }

    /**
     * Test that both adapters support the same basic formats.
     */
    public function testFormatSupportConsistency(): void
    {
        $formats = ['jpeg', 'png', 'gif'];
        
        foreach ($formats as $format) {
            $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
            $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

            $outGd = $this->output("monalisa_gd.$format");
            $outImagick = $this->output("monalisa_imagick.$format");

            try {
                $imageGd->resize(100, 100)->save($outGd, $format);
                $imageImagick->resize(100, 100)->save($outImagick, $format);

                self::assertFileExists($outGd, "GD failed to save $format format");
                self::assertFileExists($outImagick, "Imagick failed to save $format format");
            } catch (\Exception $e) {
                self::fail("Format $format should be supported by both adapters: " . $e->getMessage());
            }
        }
    }

    /**
     * Test WebP support for both adapters.
     */
    public function testWebpSupportConsistency(): void
    {
        $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
        $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

        $outGd = $this->output('monalisa_gd.webp');
        $outImagick = $this->output('monalisa_imagick.webp');

        try {
            $imageGd->resize(100, 100)->save($outGd, 'webp', 80);
            $gdSupportsWebp = file_exists($outGd);
        } catch (\Exception $e) {
            $gdSupportsWebp = false;
        }

        try {
            $imageImagick->resize(100, 100)->save($outImagick, 'webp', 80);
            $imagickSupportsWebp = file_exists($outImagick);
        } catch (\Exception $e) {
            $imagickSupportsWebp = false;
        }

        if (!$gdSupportsWebp && !$imagickSupportsWebp) {
            self::markTestSkipped('Neither adapter supports WebP format in this environment');
        }

        if ($gdSupportsWebp) {
            self::assertFileExists($outGd, 'GD should support WebP');
        }

        if ($imagickSupportsWebp) {
            self::assertFileExists($outImagick, 'Imagick should support WebP');
        }
    }

    /**
     * Test that both adapters handle data creation consistently.
     */
    public function testDataCreationConsistency(): void
    {
        $data = file_get_contents(__DIR__.'/files/monalisa.jpg');

        $imageGd = Image::fromData($data)->setAdapter('gd');
        $imageImagick = Image::fromData($data)->setAdapter('imagick');

        self::assertSame($imageGd->width(), $imageImagick->width());
        self::assertSame($imageGd->height(), $imageImagick->height());
    }

    /**
     * Test that both adapters handle guessType consistently.
     */
    public function testGuessTypeConsistency(): void
    {
        $files = ['monalisa.jpg', 'monalisa.png', 'monalisa.gif'];

        foreach ($files as $file) {
            $imageGd = $this->openWithAdapter($file, 'gd');
            $imageImagick = $this->openWithAdapter($file, 'imagick');

            self::assertSame($imageGd->guessType(), $imageImagick->guessType(), 
                "GuessType should be consistent for $file");
        }
    }

    /**
     * Test that both adapters handle percentage resizing consistently.
     */
    public function testPercentageResizeConsistency(): void
    {
        $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
        $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

        $outGd = $this->output('monalisa_50percent_gd.jpg');
        $outImagick = $this->output('monalisa_50percent_imagick.jpg');

        $imageGd->resize('50%')->save($outGd);
        $imageImagick->resize('50%')->save($outImagick);

        self::assertFileExists($outGd);
        self::assertFileExists($outImagick);

        $gdResource = imagecreatefromjpeg($outGd);
        $expectedWidth = 386; // 50% of 771
        $expectedHeight = 481; // 50% of 961

        self::assertSame($expectedWidth, imagesx($gdResource));
        self::assertSame($expectedHeight, imagesy($gdResource));

        if (extension_loaded('imagick')) {
            $imagickResource = new \Imagick($outImagick);
            self::assertSame($expectedWidth, $imagickResource->getImageWidth());
            self::assertSame($expectedHeight, $imagickResource->getImageHeight());
            $imagickResource->destroy();
        }
    }

    /**
     * Test basic effects work on both adapters.
     */
    public function testBasicEffectsConsistency(): void
    {
        $effects = ['negate', 'grayscale'];

        foreach ($effects as $effect) {
            $imageGd = $this->openWithAdapter('monalisa.jpg', 'gd');
            $imageImagick = $this->openWithAdapter('monalisa.jpg', 'imagick');

            $outGd = $this->output("monalisa_{$effect}_gd.jpg");
            $outImagick = $this->output("monalisa_{$effect}_imagick.jpg");

            $imageGd->resize(100, 100)->$effect()->save($outGd);
            $imageImagick->resize(100, 100)->$effect()->save($outImagick);

            self::assertFileExists($outGd, "GD failed to apply $effect effect");
            self::assertFileExists($outImagick, "Imagick failed to apply $effect effect");
        }
    }

    /**
     * Opening an image with specific adapter.
     */
    protected function openWithAdapter(string $file, string $adapter): Image
    {
        $image = Image::open(__DIR__.'/files/'.$file);
        $image->setAdapter($adapter);
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
<?php

namespace Gregwar\Image\Adapter;

use Gregwar\Image\Image;
use Gregwar\Image\ImageColor;
use Gregwar\Image\Utils\FileUtils;

class Imagick extends Common
{
    /**
     * @var \Imagick
     */
    protected $resource;

    public function __construct()
    {
        parent::__construct();

        if (!extension_loaded('imagick')) {
            throw new \RuntimeException('You need to install ImageMagick PHP Extension to use this library');
        }
    }

    public function __destruct()
    {
        if ($this->resource instanceof \Imagick) {
            $this->resource->destroy();
        }
    }

    /**
     * Gets the name of the adapter.
     *
     * @return string
     */
    public function getName()
    {
        return 'ImageMagick';
    }

    /**
     * Image width.
     *
     * @return int
     */
    public function width()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return $this->resource->getImageWidth();
    }

    /**
     * Image height.
     *
     * @return int
     */
    public function height()
    {
        if (null === $this->resource) {
            $this->init();
        }

        return $this->resource->getImageHeight();
    }

    /**
     * Save the image as a gif.
     *
     * @return $this
     */
    public function saveGif($file)
    {
        $this->resource->setImageFormat('gif');
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * Save the image as a png.
     *
     * @return $this
     */
    public function savePng($file)
    {
        $this->resource->setImageFormat('png');
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * Save the image as a jpeg.
     *
     * @return $this
     */
    public function saveJpeg($file, $quality)
    {
        $this->resource->setImageFormat('jpeg');
        $this->resource->setImageCompressionQuality($quality);
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * Save the image as a webp.
     *
     * @return $this
     */
    public function saveWebp($file, $quality)
    {
        $this->resource->setImageFormat('webp');
        $this->resource->setImageCompressionQuality($quality);
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * Save the image as a avif.
     *
     * @return $this
     */
    public function saveAvif($file, $quality)
    {
        $this->resource->setImageFormat('avif');
        $this->resource->setImageCompressionQuality($quality);
        $this->resource->writeImage($file);

        return $this;
    }

    /**
     * Crops the image.
     *
     * @param int $x      the top-left x position of the crop box
     * @param int $y      the top-left y position of the crop box
     * @param int $width  the width of the crop box
     * @param int $height the height of the crop box
     *
     * @return $this
     */
    public function crop($x, $y, $width, $height)
    {
        $this->resource->cropImage($width, $height, $x, $y);
        $this->resource->setImagePage(0, 0, 0, 0);

        return $this;
    }

    /**
     * Fills the image background to $bg if the image is transparent.
     *
     * @param int $background background color
     *
     * @return $this
     */
    public function fillBackground($background = 0xffffff)
    {
        $width = $this->width();
        $height = $this->height();
        
        $backgroundColor = new \ImagickPixel();
        $backgroundColor->setColor($this->getImagickColor($background));
        
        $newImage = new \Imagick();
        $newImage->newImage($width, $height, $backgroundColor);
        $newImage->compositeImage($this->resource, \Imagick::COMPOSITE_OVER, 0, 0);
        
        $this->resource->destroy();
        $this->resource = $newImage;

        return $this;
    }

    /**
     * Negates the image.
     *
     * @return $this
     */
    public function negate()
    {
        $this->resource->negateImage(false);

        return $this;
    }

    /**
     * Changes the brightness of the image.
     *
     * @param int $brightness the brightness
     *
     * @return $this
     */
    public function brightness($brightness)
    {
        $this->resource->modulateImage(100 + $brightness, 100, 100);

        return $this;
    }

    /**
     * Contrasts the image.
     *
     * @param int $contrast the contrast [-100, 100]
     *
     * @return $this
     */
    public function contrast($contrast)
    {
        $this->resource->contrastImage($contrast > 0);

        return $this;
    }

    /**
     * Apply a grayscale level effect on the image.
     *
     * @return $this
     */
    public function grayscale()
    {
        $this->resource->setImageType(\Imagick::IMGTYPE_GRAYSCALE);

        return $this;
    }

    /**
     * Emboss the image.
     *
     * @return $this
     */
    public function emboss()
    {
        $this->resource->embossImage(0, 1);

        return $this;
    }

    /**
     * Smooth the image.
     *
     * @param int $p value between [-10,10]
     *
     * @return $this
     */
    public function smooth($p)
    {
        $this->resource->blurImage(abs($p), abs($p));

        return $this;
    }

    /**
     * Sharps the image.
     *
     * @return $this
     */
    public function sharp()
    {
        $this->resource->sharpenImage(0, 1);

        return $this;
    }

    /**
     * Edges the image.
     *
     * @return $this
     */
    public function edge()
    {
        $this->resource->edgeImage(1);

        return $this;
    }

    /**
     * Colorize the image.
     *
     * @param int $red   value in range [-255, 255]
     * @param int $green value in range [-255, 255]
     * @param int $blue  value in range [-255, 255]
     *
     * @return $this
     */
    public function colorize($red, $green, $blue)
    {
        $color = sprintf('rgb(%d,%d,%d)', $red, $green, $blue);
        $this->resource->colorizeImage($color, 1);

        return $this;
    }

    /**
     * apply sepia to the image.
     *
     * @return $this
     */
    public function sepia()
    {
        $this->resource->sepiaToneImage(80);

        return $this;
    }

    /**
     * Merge with another image.
     *
     * @param Image $other
     * @param int   $x
     * @param int   $y
     * @param int   $width
     * @param int   $height
     *
     * @return $this
     */
    public function merge(Image $other, $x = 0, $y = 0, $width = null, $height = null)
    {
        $other = clone $other;
        $other->init();
        $other->applyOperations();
        
        $otherResource = $other->getAdapter()->getResource();
        
        if ($width !== null && $height !== null) {
            $otherResource->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
        }
        
        $this->resource->compositeImage($otherResource, \Imagick::COMPOSITE_OVER, $x, $y);

        return $this;
    }

    /**
     * Rotate the image.
     *
     * @param float $angle
     * @param int   $background
     *
     * @return $this
     */
    public function rotate($angle, $background = 0xffffff)
    {
        $backgroundColor = new \ImagickPixel();
        $backgroundColor->setColor($this->getImagickColor($background));
        
        $this->resource->rotateImage($backgroundColor, $angle);

        return $this;
    }

    /**
     * Fills the image.
     *
     * @param int $color
     * @param int $x
     * @param int $y
     *
     * @return $this
     */
    public function fill($color = 0xffffff, $x = 0, $y = 0)
    {
        $fillColor = new \ImagickPixel();
        $fillColor->setColor($this->getImagickColor($color));
        
        $this->resource->floodfillPaintImage($fillColor, 0, $fillColor, $x, $y, false);

        return $this;
    }

    /**
     * write text to the image.
     *
     * @param string $font
     * @param string $text
     * @param int    $x
     * @param int    $y
     * @param int    $size
     * @param int    $angle
     * @param int    $color
     * @param string $align
     */
    public function write($font, $text, $x = 0, $y = 0, $size = 12, $angle = 0, $color = 0x000000, $align = 'left')
    {
        $draw = new \ImagickDraw();
        
        $textColor = new \ImagickPixel();
        $textColor->setColor($this->getImagickColor($color));
        
        $draw->setFillColor($textColor);
        $draw->setFont($font);
        $draw->setFontSize($size);
        
        if ($align !== 'left') {
            $metrics = $this->resource->queryFontMetrics($draw, $text);
            
            if ($align === 'center') {
                $x -= $metrics['textWidth'] / 2;
            } elseif ($align === 'right') {
                $x -= $metrics['textWidth'];
            }
        }
        
        $this->resource->annotateImage($draw, $x, $y, $angle, $text);

        return $this;
    }

    /**
     * Draws a rectangle.
     *
     * @param int  $x1
     * @param int  $y1
     * @param int  $x2
     * @param int  $y2
     * @param int  $color
     * @param bool $filled
     *
     * @return $this
     */
    public function rectangle($x1, $y1, $x2, $y2, $color, $filled = false)
    {
        $draw = new \ImagickDraw();
        
        $drawColor = new \ImagickPixel();
        $drawColor->setColor($this->getImagickColor($color));
        
        if ($filled) {
            $draw->setFillColor($drawColor);
        } else {
            $draw->setStrokeColor($drawColor);
            $draw->setFillOpacity(0);
        }
        
        $draw->rectangle($x1, $y1, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Draws a rounded rectangle.
     *
     * @param int  $x1
     * @param int  $y1
     * @param int  $x2
     * @param int  $y2
     * @param int  $radius
     * @param int  $color
     * @param bool $filled
     *
     * @return $this
     */
    public function roundedRectangle($x1, $y1, $x2, $y2, $radius, $color, $filled = false)
    {
        $draw = new \ImagickDraw();
        
        $drawColor = new \ImagickPixel();
        $drawColor->setColor($this->getImagickColor($color));
        
        if ($filled) {
            $draw->setFillColor($drawColor);
        } else {
            $draw->setStrokeColor($drawColor);
            $draw->setFillOpacity(0);
        }
        
        $draw->roundRectangle($x1, $y1, $x2, $y2, $radius, $radius);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Draws a line.
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $color
     *
     * @return $this
     */
    public function line($x1, $y1, $x2, $y2, $color = 0x000000)
    {
        $draw = new \ImagickDraw();
        
        $drawColor = new \ImagickPixel();
        $drawColor->setColor($this->getImagickColor($color));
        
        $draw->setStrokeColor($drawColor);
        $draw->line($x1, $y1, $x2, $y2);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Draws an ellipse.
     *
     * @param int  $cx
     * @param int  $cy
     * @param int  $width
     * @param int  $height
     * @param int  $color
     * @param bool $filled
     *
     * @return $this
     */
    public function ellipse($cx, $cy, $width, $height, $color = 0x000000, $filled = false)
    {
        $draw = new \ImagickDraw();
        
        $drawColor = new \ImagickPixel();
        $drawColor->setColor($this->getImagickColor($color));
        
        if ($filled) {
            $draw->setFillColor($drawColor);
        } else {
            $draw->setStrokeColor($drawColor);
            $draw->setFillOpacity(0);
        }
        
        $draw->ellipse($cx, $cy, $width / 2, $height / 2, 0, 360);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     * Draws a circle.
     *
     * @param int  $cx
     * @param int  $cy
     * @param int  $r
     * @param int  $color
     * @param bool $filled
     *
     * @return $this
     */
    public function circle($cx, $cy, $r, $color = 0x000000, $filled = false)
    {
        return $this->ellipse($cx, $cy, $r * 2, $r * 2, $color, $filled);
    }

    /**
     * Draws a polygon.
     *
     * @param array $points
     * @param int   $color
     * @param bool  $filled
     *
     * @return $this
     */
    public function polygon(array $points, $color, $filled = false)
    {
        $draw = new \ImagickDraw();
        
        $drawColor = new \ImagickPixel();
        $drawColor->setColor($this->getImagickColor($color));
        
        if ($filled) {
            $draw->setFillColor($drawColor);
        } else {
            $draw->setStrokeColor($drawColor);
            $draw->setFillOpacity(0);
        }
        
        $coordinates = array();
        for ($i = 0; $i < count($points); $i += 2) {
            $coordinates[] = array('x' => $points[$i], 'y' => $points[$i + 1]);
        }
        
        $draw->polygon($coordinates);
        $this->resource->drawImage($draw);

        return $this;
    }

    /**
     *  {@inheritdoc}
     */
    public function flip($flipVertical, $flipHorizontal)
    {
        if ($flipVertical) {
            $this->resource->flipImage();
        }
        
        if ($flipHorizontal) {
            $this->resource->flopImage();
        }

        return $this;
    }

    /**
     * Opens the image.
     */
    protected function openGif($file)
    {
        if (FileUtils::safeExists($file) && filesize($file)) {
            $this->resource = new \Imagick($file);
        } else {
            $this->resource = false;
        }
    }

    protected function openJpeg($file)
    {
        if (FileUtils::safeExists($file) && filesize($file)) {
            $this->resource = new \Imagick($file);
        } else {
            $this->resource = false;
        }
    }

    protected function openPng($file)
    {
        if (FileUtils::safeExists($file) && filesize($file)) {
            $this->resource = new \Imagick($file);
        } else {
            $this->resource = false;
        }
    }

    protected function openWebp($file)
    {
        if (FileUtils::safeExists($file) && filesize($file)) {
            $this->resource = new \Imagick($file);
        } else {
            $this->resource = false;
        }
    }

    protected function openAvif($file)
    {
        if (FileUtils::safeExists($file) && filesize($file)) {
            $this->resource = new \Imagick($file);
        } else {
            $this->resource = false;
        }
    }

    /**
     * Creates an image.
     */
    protected function createImage($width, $height)
    {
        $this->resource = new \Imagick();
        $this->resource->newImage($width, $height, new \ImagickPixel('transparent'));
        $this->resource->setImageFormat('png');
    }

    /**
     * Creating an image using $data.
     */
    protected function createImageFromData($data)
    {
        $this->resource = new \Imagick();
        $this->resource->readImageBlob($data);
    }

    /**
     * Resizes the image to an image having size of $target_width, $target_height, using
     * $new_width and $new_height and padding with $bg color.
     */
    protected function doResize($bg, int $target_width, int $target_height, int $new_width, int $new_height)
    {
        $this->resource->resizeImage($new_width, $new_height, \Imagick::FILTER_LANCZOS, 1);
        
        if ($target_width !== $new_width || $target_height !== $new_height) {
            $newImage = new \Imagick();
            
            if ($bg !== 'transparent') {
                $backgroundColor = new \ImagickPixel();
                $backgroundColor->setColor($this->getImagickColor($bg));
                $newImage->newImage($target_width, $target_height, $backgroundColor);
            } else {
                $newImage->newImage($target_width, $target_height, new \ImagickPixel('transparent'));
            }
            
            $x = (int) (($target_width - $new_width) / 2);
            $y = (int) (($target_height - $new_height) / 2);
            
            $newImage->compositeImage($this->resource, \Imagick::COMPOSITE_OVER, $x, $y);
            
            $this->resource->destroy();
            $this->resource = $newImage;
        }

        return $this;
    }

    /**
     * Gets the color of the $x, $y pixel.
     */
    protected function getColor($x, $y)
    {
        $pixel = $this->resource->getImagePixelColor($x, $y);
        $colors = $pixel->getColor();
        
        return ($colors['r'] << 16) | ($colors['g'] << 8) | $colors['b'];
    }

    /**
     * Convert color to ImageMagick format.
     *
     * @param int|string $color
     * @return string
     */
    protected function getImagickColor($color)
    {
        if (is_string($color)) {
            return $color;
        }
        
        if ($color === 'transparent') {
            return 'transparent';
        }
        
        $red = ($color >> 16) & 0xFF;
        $green = ($color >> 8) & 0xFF;
        $blue = $color & 0xFF;
        
        return sprintf('rgb(%d,%d,%d)', $red, $green, $blue);
    }

    /**
     * {@inheritdoc}
     */
    public function enableProgressive()
    {
        $this->resource->setImageInterlaceScheme(\Imagick::INTERLACE_PLANE);

        return $this;
    }

    /**
     * Does this adapter supports type ?
     *
     * @param string $type
     * @return bool
     */
    protected function supports($type)
    {
        try {
            // Create a temporary Imagick instance to query formats
            $imagick = new \Imagick();
            $formats = $imagick->queryFormats(strtoupper($type));
            $imagick->destroy();
            return !empty($formats);
        } catch (\Exception $e) {
            return false;
        }
    }
}

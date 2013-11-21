<?php
namespace Qi\Media;

/**
 * The Image class is used to open, manipulate, and save Image media using the GD2 library.
 * 
 * Example:
 * 
 * import("org.transio.framework.media.Image");
 * try {
 *     $image = new Image("/path/to/image.jpg");
 *     $image->resizeCrop(640, 480);
 *     $image->addOverlay("/path/to/watermark.png", 0, 0, 100, 100, 50); // x=0, y=0. w=100, h=100, alpha=50
 *     $image->save("/path/to/output.jpg", MimeType::JPG, 80);
 *     $image->close();
 * } catch (Exception $e) {
 *     print("Exception encountered: " . $e->getMessage());
 * }
 */
class Image
{
    const TRANSPARENT = 0x00;
    const WHITE = 0x01;
    const BLACK = 0x02;
    
    
    private $image = null;
    private $path = null;
    
    /**
     * Constructor
     * @param $path String[optional] the local path of the image file from which to create the Image object
     */
    public function __construct($path=null)
    {
        if (!is_null($path)) {
            if (get_class($path) == "File") {
                $path = $path->getPath();
            }
            $this->path = $path;
            if ($this->open($path)) {
                return true;
            }
            return false;
        }
        return false;
      }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
      }

    /**
     * Opens an image file
     * @param $filePath String the local file path of the image file to open
     */
    public function open($filePath)
    {
            try {
                $imageInfo = getimagesize($filePath);
            } catch (Exception $e) {
                
            }
        switch (strtolower($imageInfo['mime'])) {
            case MimeType::BMP:
                if (imagetypes() && IMG_BMP) {
                    $this->image = @imagecreatefrombmp($filePath);
                } else {
                    throw new Exception("BMP images are not supported.");
                }
                break;
            case MimeType::GIF:
                if (imagetypes() && IMG_GIF) {
                    $this->image = @imagecreatefromgif($filePath);
                } else {
                    throw new Exception("GIF images are not supported.");
                }
                break;
            case MimeType::JPG:
            case MimeType::JPEG:
                if (imagetypes() && IMG_JPG) {
                    $this->image = @imagecreatefromjpeg($filePath);
                } else {
                    throw new Exception("JPG images are not supported.");
                }
                break;
            case MimeType::PNG:
                if (imagetypes() && IMG_PNG) {
                    $this->image = @imagecreatefrompng($filePath);
                } else {
                    throw new Exception("JPG images are not supported.");
                }
                break;
            default:
                throw new Exception("File type not supported.");
                break;
        }
    }

    public function getResource()
    {
        return $this->image;
    }
    
    /**
     * Gets the width of the loaded image resource (or 0 if no resource is loaded)
     * @return int the width of the image
     */
    public function getWidth()
    {
        return is_resource($this->image) ? imagesx($this->image) : 0;
    }
    
    /**
     * Gets the height of the loaded image resource (or 0 if no resource is loaded)
     * @return int the height of the image
     */
    public function getHeight()
    {
        return is_resource($this->image) ? imagesy($this->image) : 0;
    }

    /**
     * Resizes the image to the specified dimensions, using the larger dimension
     * (proportinately) as the scale measure.  As such, the smaller dimension may 
     * end up smaller than the specified dimensions.
     * 
     * @param $newWidth int The max width of the image
     * @param $newHeight int The max height of the image
     * @param $scaleUp Boolean If set to true, this function will resize the image up in size
     */
    public function resize($newWidth, $newHeight, $bg=self::WHITE, $scaleUp=false)
    {
        if (!is_resource($this->image)) {
            throw new Exception("Invalid image resource supplied.");
        }

        $width = $this->getWidth();
        $height = $this->getHeight();

        if ($width > $newWidth || $height > $newHeight || $scaleUp) {
            $scale = min($newWidth/$width, $newHeight/$height);
            $w = round($width * $scale);
            $h = round($height * $scale);
            
            // Create a new image resource for the resized image
            $resizedImage = self::generateCanvas($w, $h, $bg);
            
            // Copy and scale the original image into the resized one
            imagecopyresampled($resizedImage, $this->image, 0, 0, 0, 0, $w, $h, $width, $height);

            // Set the local link to the new resized image
            $this->image = &$resizedImage;

            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Resizes the image to the specified dimensions, cropping the excess from the larger
     * dimension if necessary (e.g. if one dimension is proportionally larger than that of
     * the specified output size).  Crops evenly from the left and right sides (centered).
     * 
     * @param $newWidth int The final width of the image
     * @param $newHeight int The final height of the image
     * @param $scaleUp Boolean If set to true, this function will resize the image up in size
     */
    public function resizeCrop($newWidth, $newHeight, $cropAlignment=Alignment::CENTER_MIDDLE, $bg=self::WHITE)
    {
        if (!is_resource($this->image)) {
            throw new Exception("Invalid image resource supplied.");
        }

        $width = $this->getWidth();
        $height = $this->getHeight();

        $scale = max($newWidth/$width, $newHeight/$height);
        $w = round($width * $scale);
        $h = round($height * $scale);
        
        // Determine X positioning
        switch (floor($cropAlignment / 10)) {
            case Alignment::LEFT:
                $x = 0;
                break;
            case Alignment::RIGHT:
                $x = round($newWidth-$w);
                break;
            case Alignment::CENTER:
            default:
                $x = round(($newWidth-$w)/2);
                break;
        }
        
        // Determine Y positioning
        switch ($cropAlignment % 10) {
            case Alignment::TOP:
                $y = 0;
                break;
            case Alignment::BOTTOM:
                $y = round($newHeight-$h);
                break;
            case Alignment::MIDDLE:
            default:
                $y = round(($newHeight-$h)/2);
                break;
        }

        // Create a new image resource for the resized image
        $resizedImage = self::generateCanvas($newWidth, $newHeight, $bg);    

        // Copy, scale, and center the original image into the resized one
        imagecopyresampled($resizedImage, $this->image, $x, $y, 0, 0, $w, $h, $width, $height);

        // Set the local link to the new resized image
        $this->image = &$resizedImage;

        // Successfully resized
        return true;
    }
    
    /**
     * Create a new empty image canvas of specified width, height, and background color
     * @return 
     * @param $w int
     * @param $h int
     * @param $bg int (color constant)
     */
    public static function generateCanvas($w, $h, $bg)
    {
        $img = imagecreatetruecolor($w, $h);
        switch ($bg) {
            case self::TRANSPARENT:
                break;
            case self::WHITE:
                $bg = imagecolorallocate($img, 255, 255, 255);  
                imagefilledrectangle($img, 0, 0, $w, $h, $bg);    
                break;
            case self::BLACK:
                $bg = imagecolorallocate($img, 0, 0, 0);  
                imagefilledrectangle($img, 0, 0, $w, $h, $bg);    
                break;
        }
        return $img;
    }
    
    /**
     * Adds an overlay to the existing image.  Examples of use are watermarks and decorative borders.
     * It is suggested to use a PNG for the overlay if you want transparency.
     * 
     * @param $overlayImagePath Object
     * @param $x Object[optional] The x position of the overlay image.  Defaults to 0
     * @param $y Object[optional] The y position of the overlay image. Defaults to 0
     * @param $width Object[optional] The width of the overlay image.  Defaults to original size.
     * @param $height Object[optional] The height of the overlay image.  Defaults to original size.
     */
    public function addOverlay($overlayImagePath, $x=0, $y=0, $width=null, $height=null, $alpha=100)
    {
        if (!is_resource($this->image)) {
            throw new Exception("Invalid image resource supplied.");
        }
        
        // Get the overlay image
        $overlayImage = new Image($overlayImagePath);
        
        // Resize the overlay if necessary
        if (!is_null($width) && !is_null($height) && $width > 0 && $height > 0) {
            $overelayImage->resizeCrop($width, $height);
        } else {
            $width = $overlayImage->getWidth();
            $height = $overlayImage->getHeight();
        }

        // Merge the overlay onto the local image
        //imagecopymerge($this->image, $overlayImage->getResource(), $x, $y, 0, 0, $width, $height, $alpha);

        $frame = imagecreatetruecolor($this->getWidth(),$this->getHeight()); 
        imagecopyresampled($frame, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight()); 
        imagecopy($frame, $overlayImage->getResource(), $x, $y, 0, 0, $width, $height); 
        $this->image = $frame;
        
        // Close the overlay image
        $overlayImage->close();
    }
    
    
    /**
     * Saves the image to the specified location in the specified format and quality
     * 
     * @param $newPath String[optional]
     * @param $mimeTypeEnum String[optional]
     * @param $quality int[optional]
     */
    public function save($newPath=null, $mimeTypeEnum=MimeType::JPG, $quality=80)
    {
        if (!is_resource($this->image)) {
            throw new Exception("Invalid image resource supplied.");
        }
        
        $path = $newPath ? $newPath : $this->path;

        switch ($mimeTypeEnum) {
            case MimeType::BMP:
                imagebmp($this->image, $path, $quality);
                break;
            case MimeType::GIF:
                imagegif($this->image, $path, $quality);
                break;
            case MimeType::JPG:
            case MimeType::JPEG:
                imagejpeg($this->image, $path, $quality);
                break;
            case MimeType::PNG:
                imagepng($this->image, $path, $quality);
                break;
            default:
                break;
        }
    }
    
    /**
     * Closes the image file
     */
    public function close()
    {
        unset($this->image);
    }
}
    

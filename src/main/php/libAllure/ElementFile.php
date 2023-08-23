<?php

namespace libAllure;

class ElementFile extends Element
{
    private $imageResource = null;

    public $isImage = true;
    public $destinationDir = '/tmp/';
    public $destinationFilename = 'unnamed';

    public $tempDir = '/tmp/libAllureImageUploads/';

    private $tempName = null;

    public $imageMaxW = 80;
    public $imageMaxH = 80;

    public $autoResize = true;

    public function getFilename()
    {
        return $this->destinationFilename;
    }

    public function setMaxImageBounds($imageMaxW, $imageMaxH)
    {
        $this->imageMaxW = $imageMaxW;
        $this->imageMaxH = $imageMaxH;
    }

    public function wasAnythingUploaded()
    {
        if (sizeof($_FILES) == 0 || empty($_FILES[$this->name]['tmp_name'])) {
            return false;
        } else {
            return true;
        }
    }

    public function validateInternals()
    {
        if (!$this->wasAnythingUploaded()) {
            return;
        }

        if (!@is_uploaded_file($_FILES[$this->name]['tmp_name'])) {
            $this->setValidationError('Got an object which is not a file.');
        }

        if ($this->isImage) {
            if (!@getimagesize($_FILES[$this->name]['tmp_name'])) {
                $this->setValidationError('Cannot interpret that as an image.');
            }
        }

        $this->moveFileToTemp();

        if ($this->isImage) {
            $this->validateImage();
        }
    }

    public function setDestination($dir, $filename)
    {
        $this->destinationDir = $dir;
        $this->destinationFilename = $filename;
    }

    private function moveFileToTemp()
    {
        $this->tempName = tempnam($this->tempDir, uniqid());
        $mov = @move_uploaded_file($_FILES[$this->name]['tmp_name'], $this->tempName);

        if (!$mov) {
            throw new \Exception('Could not move uploaded file: ' . $this->tempName);
        }
    }

    private function validateImage()
    {
        $type = exif_imagetype($this->tempName);

        if ($type == IMAGETYPE_JPEG) {
            $this->imageResource = imagecreatefromjpeg($this->tempName);
        } elseif ($type == IMAGETYPE_GIF) {
            $this->imageResource = imagecreatefromgif($this->tempName);
        } elseif ($type == IMAGETYPE_PNG) {
            $this->imageResource = imagecreatefrompng($this->tempName);
        } else {
            $this->setValidationError("Unsupported file type.");
            return;
        }

        if (imagesx($this->imageResource) > $this->imageMaxW || imagesy($this->imageResource) > $this->imageMaxH) {
            if ($this->autoResize) {
                $this->imageResource = self::resizeImage($this->imageResource, $this->imageMaxW, $this->imageMaxH);
            } else {
                $this->setValidationError('Image too big, images may up to ' . $this->imageMaxW . 'x' . $this->imageMaxH . ' pixels, that was ' . imagesx($this->imageResource) . 'x' . imagesy($this->imageResource) . ' pixels.');
            }
        }
    }

    public static function resizeImage($srcImage, $finW, $finH)
    {
        $imageResized = imagecreatetruecolor($finW, $finH);
        imagealphablending($srcImage, false);
        imageinterlace($srcImage, true);

        imagealphablending($imageResized, false);
        imageinterlace($imageResized, true);

        $srcX = 0;
        $srcY = 0;
        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);

        $offsetX = 0;
        $offsetY = 0;

        $srcRatio = $srcW / $srcH;
        $dstRatio = $finW / $finH;

        if ($srcRatio < $dstRatio) {
            $dstW = $finH * $srcRatio;
            $dstH = $finH;
            $offsetX = ($finW - $dstW) / 2;
        } else {
            $dstW = $finW;
            $dstH = $finW * $srcRatio;
            $offsetY = ($finH - $dstH) / 2;
        }

        $backgroundColor = imagecolorallocate($imageResized, 255, 255, 255);
        imagefill($imageResized, 0, 0, $backgroundColor);

        imagecopyresampled($imageResized, $srcImage, $offsetX, $offsetY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

        return $imageResized;
    }

    /*
    private function checkUploadSuccess() {
        $uploadStatus = $_FILES[$this->name]['error'];

        if ($uploadStatus !== UPLOAD_ERR_OK) {
            throw new Exception('Upload of file failed.');
        }
    }
    */

    public function saveJpg()
    {
        if (!$this->wasAnythingUploaded()) {
            return;
        }
        echo 'saving to ' . $this->destinationDir . $this->destinationFilename;

        imagejpeg($this->imageResource, $this->destinationDir . DIRECTORY_SEPARATOR . $this->destinationFilename);
    }

    public function savePng()
    {
        if (!$this->wasAnythingUploaded()) {
            return;
        }

        imagealphablending($this->imageResource, true);
        imagesavealpha($this->imageResource, true);
        imagepng($this->imageResource, $this->destinationDir . DIRECTORY_SEPARATOR . $this->destinationFilename);
    }

    public function render()
    {
        return sprintf('<input name = "%s" type = "file" />', $this->name);
    }
}

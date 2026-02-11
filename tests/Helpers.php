<?php

declare(strict_types=1);

/**
 * Create a test JPEG image using GD and return raw bytes.
 */
function createTestJpeg(int $width = 100, int $height = 60): string
{
    $img = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);

    ob_start();
    imagejpeg($img, null, 90);
    $data = ob_get_clean();
    imagedestroy($img);

    return $data;
}

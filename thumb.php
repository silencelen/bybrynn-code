<?php
/**
 * Generate and cache a thumbnail for a WebP image using GD.
 *
 * @param string $origPath Absolute filesystem path to the high-res .webp file.
 * @param int    $maxW     Fixed width of the thumbnail (height is auto-scaled).
 * @return string          Web-accessible URL (relative to document root) of the thumbnail.
 */
function get_thumb(string $origPath, int $maxW): string
{
    // 1. Compute paths and names
    $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
    // Directory for thumbnails: /art/images/thumb under document root
    $thumbDir = $docRoot . DIRECTORY_SEPARATOR . 'art'
                 . DIRECTORY_SEPARATOR . 'images'
                 . DIRECTORY_SEPARATOR . 'thumb';

    // Ensure the directory exists
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    // Filename without extension
    $base      = pathinfo($origPath, PATHINFO_FILENAME);
    // e.g. "foo_290.webp"
    $thumbName = sprintf('%s_%d.webp', $base, $maxW);
    $thumbFs   = $thumbDir . DIRECTORY_SEPARATOR . $thumbName;
    // Convert filesystem path to URL path
    $thumbUrl  = str_replace($docRoot, '', $thumbFs);

    // 2. If thumbnail doesn’t exist, create it
    if (!file_exists($thumbFs) && file_exists($origPath)) {
        // Get original dimensions
        list($origW, $origH) = getimagesize($origPath);

        // Calculate new dimensions for fixed width, preserve aspect ratio
        $ratio  = $maxW / $origW;
        $newW   = $maxW;
        $newH   = (int)($origH * $ratio);

        // Create a blank canvas
        $thumb = imagecreatetruecolor($newW, $newH);

        // Load the original WebP
        $src   = imagecreatefromwebp($origPath);

        // Copy & resize
        imagecopyresampled(
            $thumb,
            $src,
            0, 0,  // dest x/y
            0, 0,  // src x/y
            $newW, $newH,
            $origW, $origH
        );

        // Save the thumbnail as WebP (quality 80%)
        imagewebp($thumb, $thumbFs, 80);

        // Free resources
        imagedestroy($src);
        imagedestroy($thumb);
    }

    return $thumbUrl;
}

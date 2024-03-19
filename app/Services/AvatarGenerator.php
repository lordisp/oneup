<?php

namespace App\Services;

class AvatarGenerator
{
    protected $firstName;

    protected $lastName;

    public function __construct(public int $size = 32, bool $fromDisk = false)
    {
        $this->firstName = auth()->user()->firstName;
        $this->lastName = auth()->user()->lastName;
    }

    public function generate(): string
    {
        $initials = substr($this->firstName, 0, 1).substr($this->lastName, 0, 1);

        $bgColor = $this->generateColor();

        $textColor = $this->getContrastColor($bgColor);

        return '<svg class="rounded-full" width="'.$this->size.'" height="'.$this->size.'" style="background-color: '.$bgColor.';">
                    <text x="'.($this->size / 2).'" y="'.($this->size / 2).'" class="text-md font-normal" text-anchor="middle" dominant-baseline="central" fill="'.$textColor.'">'.$initials.'</text>
                </svg>';
    }

    /**
     * Generates a unique color based on the initials.
     */
    protected function generateColor(): string
    {
        $hash = md5($this->firstName[0].$this->lastName[0]);

        $hex = substr($hash, 0, 6);

        return '#'.$hex;
    }

    protected function getContrastColor($color): string
    {
        $rgb = $this->hexToRgb($color);
        $contrastBlack = $this->getContrastRatio($rgb, [0, 0, 0]);
        $contrastWhite = $this->getContrastRatio($rgb, [255, 255, 255]);

        if ($contrastBlack > $contrastWhite) {
            return '#000000';
        } else {
            return '#ffffff';
        }
    }

    protected function hexToRgb($color): bool|array
    {
        $color = ltrim($color, '#');

        switch (strlen($color)) {
            case 6:
                [$r, $g, $b] = [$color[0].$color[1], $color[2].$color[3], $color[4].$color[5]];
                break;
            case 3:
                [$r, $g, $b] = [$color[0].$color[0], $color[1].$color[1], $color[2].$color[2]];
                break;
            default:
                return false;
        }

        return [hexdec($r), hexdec($g), hexdec($b)];
    }

    protected function getContrastRatio($rgb1, $rgb2): float
    {
        $l1 = $this->getRelativeLuminance($rgb1);
        $l2 = $this->getRelativeLuminance($rgb2);
        $contrast = ($l1 > $l2) ? ($l1 + 0.05) / ($l2 + 0.05) : ($l2 + 0.05) / ($l1 + 0.05);

        return round($contrast, 2);
    }

    protected function getRelativeLuminance($rgb): float
    {
        $r = $this->convertToSrgb($rgb[0] / 255);
        $g = $this->convertToSrgb($rgb[1] / 255);
        $b = $this->convertToSrgb($rgb[2] / 255);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    protected function convertToSrgb($c): float|object|int
    {
        if ($c <= 0.03928) {
            return $c / 12.92;
        } else {
            return pow((($c + 0.055) / 1.055), 2.4);
        }
    }
}

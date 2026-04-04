<?php

namespace App\Mason;

use App\Mason\Bricks\CallToAction;
use App\Mason\Bricks\FeatureGrid;
use App\Mason\Bricks\Hero;
use App\Mason\Bricks\Image;
use App\Mason\Bricks\RichText;

class BrickCollection
{
    /**
     * @return array<class-string<\Awcodes\Mason\Brick>>
     */
    public static function make(): array
    {
        return [
            Hero::class,
            RichText::class,
            Image::class,
            FeatureGrid::class,
            CallToAction::class,
        ];
    }
}

<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Artist;
use App\Entity\Release;
use App\Entity\Track;
use App\Random\Random;
use Faker\Generator;

final class TrackFactory
{

    public const EDIT_TYPES = [
        'original' => 'Original Mix',
        'radio' => 'Radio Edit',
        'instrumental' => 'Instrumental',
        'preview' => 'Preview',
        'cover' => 'Cover',
        'remix' => 'Remix',
        'mix' => 'Mix',
    ];

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(string $isrc, Release $release): Track
    {
        $track = new Track();
        $track->setMusicRelease($release);

        $mix = Random::int(0, 100) > 95;

        $duration = $mix ? Random::int(600, 7210) : Random::int(10, 600);

        $track->setDuration($duration);
        $edit = $mix ? 'mix' : $this->faker->randomElement(Track::EDIT_TYPES);

        $track->setEdit($edit);
        $name = \sprintf('%s (%s)', $this->randomTrackName($this->faker), self::EDIT_TYPES[$edit]);
        $track->setTitle($name);
        $track->setIsrc($isrc);

        return $track;
    }

    private function randomTrackName(Generator $faker): string
    {
        $parts = Random::int(0, 5);
        $name = $this->randomNamePart($faker);
        while ($parts > 0) {
            if (Random::int(0, 100) > 95) {
                $name .= ($faker->boolean ? ' ' : '') . $faker->randomElement([
                        Random::int(1, 99),
                        '\'',
                        '@',
                        ' $$$',
                    ]);
            }
            $name .= ' ' . $this->randomNamePart($faker);
            --$parts;
        }
        return $name;
    }

    private function randomNamePart(Generator $faker): string
    {
        $possibilities = [
            'monthName',
            'colorName',
            'firstName',
            'lastName',
            'safeColorName',
            'domainName',
        ];

        $part = Random::int(0, \count($possibilities) - 1);
        $namePart = $possibilities[$part];
        return \ucfirst($faker->$namePart);
    }

}
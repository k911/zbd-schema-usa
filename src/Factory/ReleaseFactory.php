<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\MusicLabel;
use App\Entity\Release;
use App\Random\Random;
use Faker\Generator;

final class ReleaseFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(int $upc, MusicLabel $label): Release
    {
        $release = new Release();

        $usdCents = Random::int(6, 100) * 50;
        $release->setOriginalPrice($usdCents);
        $release->setReleasedAt(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween($label->getCreationDate())
        ));

        $release->setMusicLabel($label);
        $release->setUpc($upc);
        $cLine = \sprintf('%s (c) %s', $label->getName(), $release->getReleasedAt()->format('YYYY'));
        $release->setCLine($cLine);

        if ($this->faker->boolean) {
            $release->setPLine(\sprintf('%s (p) %s', $this->faker->company, $release->getReleasedAt()->format('YYYY')));
        } else {
            $release->setPLine($cLine);
        }

        $release->setType($this->faker->randomElement([
            'single', 'album', 'digital',
        ]));

        $release->setName($this->randomReleaseName($this->faker));

        return $release;
    }

    private function randomReleaseName(Generator $faker): string
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

        return \ucfirst($faker->$possibilities[Random::int(0, \count($possibilities) - 1)]);
    }

}
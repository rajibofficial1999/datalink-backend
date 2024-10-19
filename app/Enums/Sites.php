<?php

namespace App\Enums;

enum Sites: string
{
    case EROS_ADS = 'eros';
    case MEGAPERSONALS = 'mega';
    case PRIVATE_DELIGHTS = 'pd';
    case SKIPTHEGAMES = 'skip';
    case TRYST = 'tryst';

    public function details(): array
    {
        return match ($this) {
            self::EROS_ADS => [
                'name' => "eros",
                'image' => asset('assets/sites/eros.png'),
                'redirect_url' => "https://www.erosads.com/home",
            ],

            self::MEGAPERSONALS => [
                'name' => "megapersonals",
                'image' => asset('assets/sites/mega.jpg'),
                'redirect_url' => "https://megapersonals.eu/",
            ],

            self::PRIVATE_DELIGHTS => [
                'name' => "privatedelights",
                'image' => null,
                'redirect_url' => "https://privatedelights.ch/",
            ],

            self::SKIPTHEGAMES => [
                'name' => "skipthegames",
                'image' => asset('assets/sites/skip.png'),
                'redirect_url' => "https://skipthegames.com/",
            ],

            self::TRYST => [
                'name' => "tryst",
                'image' => asset('assets/sites/tryst.png'),
                'redirect_url' => 'https://tryst.link/',
            ]
        };
    }

    public static function findByValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }

    public static function findByName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->details()['name'] === $name) {
                return $case;
            }
        }
        return null;
    }
}

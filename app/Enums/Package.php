<?php

namespace App\Enums;

enum Package: string
{
    case STARTER = "starter";
    case STANDARD = "standard";
    case PREMIUM = "premium";

    public function details(): array
    {
        return match ($this) {
            self::STARTER => [
                'name' => "starter",
                'role' => "user",
                'price' => 3000,
                'team' => null,
                'custom_domain' => false,
                'sites' => [
                    Sites::MEGAPERSONALS->value,
                    Sites::SKIPTHEGAMES->value,
                ]
            ],

            self::STANDARD => [
                'name' => "standard",
                'role' => "admin",
                'price' => 5000,
                'team' => 3,
                'custom_domain' => true,
                'sites' => [
                    Sites::MEGAPERSONALS->value,
                    Sites::SKIPTHEGAMES->value,
                    Sites::PRIVATE_DELIGHTS->value,
                    Sites::EROS_ADS->value,
                ]
            ],

            self::PREMIUM => [
                'name' => "premium",
                'role' => "admin",
                'price' => 10000,
                'team' => 20,
                'custom_domain' => true,
                'sites' => [
                    Sites::MEGAPERSONALS->value,
                    Sites::SKIPTHEGAMES->value,
                    Sites::PRIVATE_DELIGHTS->value,
                    Sites::EROS_ADS->value,
                    Sites::TRYST->value
                ]
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

    public static function allDetails(): array
    {
        return array_map(fn($package) => $package->details(), self::cases());
    }
}

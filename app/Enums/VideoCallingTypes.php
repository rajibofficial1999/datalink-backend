<?php

namespace App\Enums;

enum VideoCallingTypes: string
{
    case DUO = 'google_duo';
    case TEXT_NOW = 'textnow';
    case APP_TIME = 'apptime';
    case WHATS_APP = 'whatsapp';
    case FACE_TIME = 'facetime';

    public function details(): array
    {
        return match ($this) {
            self::DUO => [
                'name' => "google_duo",
                'color' => "#1A73E8",
                'image' => asset("assets/video-calling/google_duo.svg"),
            ],

            self::TEXT_NOW => [
                'name' => "textnow",
                'color' => "#8363D0",
                'image' => asset("assets/video-calling/textnow.png"),
            ],

            self::APP_TIME => [
                'name' => "apptime",
                'color' => "#00BE70",
                'image' => asset("assets/video-calling/face.png"),
            ],

            self::WHATS_APP => [
                'name' => "whatsapp",
                'color' => "#00BE70",
                'image' => asset("assets/video-calling/whatsapp.png"),
            ],

            self::FACE_TIME => [
                'name' => "facetime",
                'color' => "#00BE70",
                'image' => asset("assets/video-calling/face.png"),
            ],
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
}

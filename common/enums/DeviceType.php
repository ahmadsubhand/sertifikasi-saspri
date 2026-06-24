<?php

namespace common\enums;

class DeviceType
{
    const ANDROID = 'android';
    const IOS = 'ios';
    const WEB = 'web';

    public static function list()
    {
        return [
            self::ANDROID => 'Android',
            self::IOS => 'IOS',
            self::WEB => 'Web',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}
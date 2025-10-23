<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
        'options'
    ];

    protected $casts = [
        'options' => 'array'
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue($key, $value, $description = null, $type = 'text')
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type
            ]
        );
    }

    /**
     * Get font family setting
     */
    public static function getFontFamily()
    {
        return self::getValue('font_family', 'Segoe UI');
    }

    /**
     * Set font family setting
     */
    public static function setFontFamily($fontFamily)
    {
        return self::setValue('font_family', $fontFamily, 'Font chữ hệ thống', 'select');
    }

    /**
     * Get background color setting
     */
    public static function getBackgroundColor()
    {
        return self::getValue('background_color', '#f8f9fa');
    }

    /**
     * Set background color setting
     */
    public static function setBackgroundColor($backgroundColor)
    {
        return self::setValue('background_color', $backgroundColor, 'Màu nền hệ thống', 'color');
    }
}

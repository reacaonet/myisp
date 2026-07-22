<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], [
            'value' => $value,
            'type' => $type,
            'group' => $group,
        ]);
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}

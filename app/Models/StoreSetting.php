<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getMany(array $keys): array
    {
        $rows = static::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->all();

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $rows[$key] ?? null;
        }

        return $result;
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => (string) $key],
                ['value' => $value === null ? null : (string) $value]
            );
        }
    }
}

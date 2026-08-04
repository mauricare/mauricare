<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareOption extends Model
{
    protected $fillable = ['category', 'value', 'label', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public static function values(string $category, bool $activeOnly = true): array
    {
        return static::query()
            ->where('category', $category)
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('label')
            ->pluck('value')->all();
    }

    public static function grouped(bool $activeOnly = true): array
    {
        $options = static::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('label')
            ->get(['id', 'category', 'value', 'label', 'sort_order', 'is_active']);

        return [
            'care_types' => $options->where('category', 'care_type')->values(),
            'carer_types' => $options->where('category', 'carer_type')->values(),
        ];
    }
}

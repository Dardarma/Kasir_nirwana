<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Satuan extends Model
{
    protected $table = 'satuan';

    public $timestamps = false;

    protected $fillable = ['nama', 'parent_id', 'faktor', 'is_satuan_terkecil'];

    public static function dasarStok(string $nama): array
    {
        $unit = self::with('parent')->where('nama', $nama)->firstOrFail();

        return [$unit->parent?->nama ?? $unit->nama, (float) ($unit->faktor ?? 1)];
    }

    public static function untukStok(string $nama): array
    {
        $base = self::where('nama', $nama)->firstOrFail();

        return [$base->nama => 1.0] + $base->children()->pluck('faktor', 'nama')
            ->map(fn ($factor) => (float) $factor)->all();
    }

    public static function konversiStok(): array
    {
        return self::with('parent')->get()->mapWithKeys(fn (self $unit) => [
            $unit->nama => [
                'dasar' => $unit->parent?->nama ?? $unit->nama,
                'faktor' => (float) ($unit->faktor ?? 1),
            ],
        ])->all();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

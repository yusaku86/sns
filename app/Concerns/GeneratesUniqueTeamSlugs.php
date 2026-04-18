<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * チームスラッグの一意生成を提供するトレイト。
 */
trait GeneratesUniqueTeamSlugs
{
    /**
     * チーム名からユニークなスラッグを生成する。
     * 同名スラッグが存在する場合は末尾に連番サフィックスを付与する。
     *
     * @param  string  $name  チーム名
     * @param  string|int|null  $excludeId  自身の更新時に除外するチームID
     * @return string 生成されたスラッグ
     */
    protected static function generateUniqueTeamSlug(string $name, string|int|null $excludeId = null): string
    {
        $defaultSlug = Str::slug($name);

        $query = static::withTrashed()
            ->where(function ($query) use ($defaultSlug) {
                $query->where('slug', $defaultSlug)
                    ->orWhere('slug', 'like', $defaultSlug.'-%');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existingSlugs = $query->pluck('slug');

        $maxSuffix = $existingSlugs
            ->map(function (string $slug) use ($defaultSlug): ?int {
                if ($slug === $defaultSlug) {
                    return 0;
                } elseif (preg_match('/^'.preg_quote($defaultSlug, '/').'-(\d+)$/', $slug, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter(fn (?int $suffix) => $suffix !== null)
            ->max() ?? 0;

        return $existingSlugs->isEmpty()
            ? $defaultSlug
            : $defaultSlug.'-'.($maxSuffix + 1);
    }
}

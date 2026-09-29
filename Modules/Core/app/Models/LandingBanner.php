<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Services\TenantContext;

class LandingBanner extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'subtitle',
        'badge',
        'image',
        'link_url',
        'link_label',
        'highlight',
        'title_font_size',
        'title_color',
        'subtitle_font_size',
        'subtitle_color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'title_font_size' => 'integer',
        'subtitle_font_size' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $banner) {
            if ($banner->company_id === null) {
                $banner->company_id = TenantContext::companyId();
            }
        });
    }

    /**
     * Query ancorada na empresa atual, salvo navegacao cross-tenant.
     */
    public static function scoped(?Builder $query = null): Builder
    {
        $query ??= static::query();

        if (! TenantContext::isCrossTenant()) {
            $query->where('company_id', TenantContext::companyId());
        }

        return $query;
    }

    public static function findScoped(int $id): ?self
    {
        return static::scoped()->find($id);
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scoped()->findOrFail($id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

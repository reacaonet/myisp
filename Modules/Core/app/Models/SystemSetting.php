<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Core\Services\TenantContext;

class SystemSetting extends Model
{
    protected $fillable = ['company_id', 'key', 'value', 'type', 'group'];

    /** @var array<string, mixed> */
    protected static array $cache = [];

    /** @var array<string, array<string, mixed>> */
    protected static array $groupCache = [];

    protected static function boot(): void
    {
        parent::boot();

        // Template da raiz (company_id NULL) so e gravado pela franqueadora;
        // franquia sobrescreve apenas o que quiser.
        static::creating(function (self $setting) {
            if ($setting->company_id === null) {
                $setting->company_id = self::targetCompanyId();
            }
        });

        static::saved(fn () => self::forget());
        static::deleted(fn () => self::forget());
    }

    public static function get(string $key, $default = null)
    {
        $companyId = TenantContext::companyId();

        $cacheKey = $companyId.'|'.$key;

        if (array_key_exists($cacheKey, self::$cache)) {
            return self::$cache[$cacheKey];
        }

        $value = null;

        if ($companyId) {
            $value = static::query()
                ->where('company_id', $companyId)
                ->where('key', $key)
                ->value('value');
        }

        $value ??= static::query()
            ->whereNull('company_id')
            ->where('key', $key)
            ->value('value');

        return self::$cache[$cacheKey] = $value ?? $default;
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'general'): void
    {
        $companyId = self::targetCompanyId();

        static::query()->updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        self::forget();
    }

    /**
     * Grupo mesclado: template da raiz + overrides da compania atual.
     *
     * @return array<string, mixed>
     */
    public static function getGroup(string $group): array
    {
        $companyId = TenantContext::companyId();

        $cacheKey = $companyId.'|'.$group;

        if (isset(self::$groupCache[$cacheKey])) {
            return self::$groupCache[$cacheKey];
        }

        $values = static::query()
            ->whereNull('company_id')
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();

        if ($companyId) {
            $values = array_merge($values, static::query()
                ->where('company_id', $companyId)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->all());
        }

        return self::$groupCache[$cacheKey] = $values;
    }

    /** A chave tem valor proprio da compania (em vez de herdar da raiz)? */
    public static function isOverridden(string $key): bool
    {
        $companyId = TenantContext::companyId();

        if (! $companyId) {
            return false;
        }

        return static::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->exists();
    }

    /**
     * Atualiza o valor na companhia atual, criando o override com o mesmo
     * type/group do template quando ainda nao existe.
     */
    public static function putValue(string $key, $value): void
    {
        $companyId = self::targetCompanyId();

        $template = static::query()->whereNull('company_id')->where('key', $key)->first();

        $updated = static::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->update(['value' => $value]);

        if ($updated === 0) {
            static::query()->create([
                'company_id' => $companyId,
                'key' => $key,
                'value' => $value,
                'type' => $template?->type ?? 'text',
                'group' => $template?->group ?? 'general',
            ]);
        }

        self::forget();
    }

    /**
     * Lista deduplicada com o valor efetivo por chave (override da companhia
     * por cima do template da raiz), com a flag is_overridden.
     *
     * @return Collection<int, object>
     */
    public static function effective(?string $group = null)
    {
        $companyId = TenantContext::companyId();

        $template = static::query()->whereNull('company_id')
            ->when($group, fn ($q) => $q->where('group', $group))
            ->get()
            ->keyBy('key');

        $own = $companyId
            ? static::query()->where('company_id', $companyId)
                ->when($group, fn ($q) => $q->where('group', $group))
                ->get()
                ->keyBy('key')
            : collect();

        $keys = $template->keys()->merge($own->keys())->unique()->values();

        return $keys->map(function (string $key) use ($template, $own) {
            $row = $own->get($key) ?? $template->get($key);

            return (object) [
                'key' => $key,
                'value' => $own->get($key)?->value ?? $template->get($key)?->value,
                'type' => $row?->type ?? 'text',
                'group' => $row?->group ?? 'general',
                'is_overridden' => $own->has($key),
            ];
        })->values();
    }

    public static function forget(): void
    {
        self::$cache = [];
        self::$groupCache = [];
    }

    /**
     * company_id de destino na escrita: raiz grava o template compartilhado,
     * franquia grava o proprio override.
     */
    public static function targetCompanyId(): ?int
    {
        $company = TenantContext::company();

        if (! $company || $company->isRoot()) {
            return null;
        }

        return $company->id;
    }
}

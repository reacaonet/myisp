<?php

namespace Modules\Core\Models\Concerns;

trait HasAvatar
{
    /**
     * URL publica do avatar. Segue a convencao dos demais uploads do projeto
     * (banners e logo), que gravam no disco `public` e sao servidos pelo symlink
     * `storage`. Retorna null para cair no bloco de iniciais na interface.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    public function initials(): string
    {
        $name = trim((string) $this->name);

        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $first = mb_substr($parts[0], 0, 1);

        if (count($parts) > 1) {
            return mb_strtoupper($first.mb_substr((string) end($parts), 0, 1));
        }

        return mb_strtoupper($first);
    }
}

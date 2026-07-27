<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function permissions()
    {
        return $this->hasMany(GroupPermission::class, 'group_id');
    }

    public function users()
    {
        return $this->hasMany(\Modules\Core\Models\User::class, 'user_group_id');
    }

    public function hasPermission(string $key): bool
    {
        return $this->permissions()
            ->where('permission_key', $key)
            ->where('granted', true)
            ->exists();
    }

    public static function GROUP_DEFINITIONS(): array
    {
        return [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'tecnico' => 'Tecnico',
            'operador' => 'Operador',
        ];
    }
}

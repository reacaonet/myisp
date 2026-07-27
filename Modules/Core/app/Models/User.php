<?php

namespace Modules\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'user_group_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id');
    }

    public function hasPermission(string $key): bool
    {
        if (!$this->group) {
            return false;
        }

        if ($this->group->slug === 'superadmin') {
            return true;
        }

        return $this->group->hasPermission($key);
    }

    public function setGroupId($groupId)
    {
        $group = UserGroup::find($groupId);
        if ($group) {
            $this->user_group_id = $group->id;
            $this->role = $group->slug;
        }
        return $this;
    }
}

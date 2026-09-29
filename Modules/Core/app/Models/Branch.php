<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Concerns\BelongsToTenant;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }


    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function isMatrix(): bool
    {
        return $this->parent_id === null;
    }
}

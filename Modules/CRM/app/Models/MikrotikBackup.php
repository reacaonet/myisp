<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Concerns\BelongsToTenant;

class MikrotikBackup extends Model
{
    use BelongsToTenant;

    protected $fillable = ['company_id', 'server_id', 'filename', 'content', 'file_size', 'type'];

    public function server()
    {
        return $this->belongsTo(MikrotikServer::class);
    }
}

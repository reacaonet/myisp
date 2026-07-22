<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikBackup extends Model
{
    protected $fillable = ['server_id', 'filename', 'content', 'file_size', 'type'];

    public function server()
    {
        return $this->belongsTo(MikrotikServer::class);
    }
}

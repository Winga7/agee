<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleClass extends Model
{
    protected $table = 'module_classes';

    protected $fillable = [
        'module_id',
        'class_group'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}

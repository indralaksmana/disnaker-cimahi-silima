<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigTypes extends Model
{
    protected $table = 'config_types';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

    public function config()
    {
        return $this->hasMany('\App\Model\Config', 'type_id');
    }
}

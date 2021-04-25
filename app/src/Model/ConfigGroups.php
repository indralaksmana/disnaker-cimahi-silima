<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigGroups extends Model
{
    protected $table = 'config_groups';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'page_name'
    ];

    public function config()
    {
        return $this->hasMany('\App\Model\Config', 'group_id');
    }
}

<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigAppModel extends Model
{
    protected $table = 'app_config';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'val'
    ];
}

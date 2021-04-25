<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LulusanModel extends Model
{
    protected $table = 'lulusan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function lulusan()
    {
        return $this->hasMany('\App\Model\PencariKerjaModel', 'lulusan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JurusanModel extends Model
{
    protected $table = 'jurusan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function jurusan()
    {
        return $this->hasMany('\App\Model\PencariKerjaModel', 'jurusan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}


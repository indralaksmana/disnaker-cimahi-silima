<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KursusPelatihanModel extends Model
{
    protected $table = 'master_kursuspelatihan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function jurusan()
    {
        return $this->hasMany('\App\Model\PencariKerjaModel', 'pelatihan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

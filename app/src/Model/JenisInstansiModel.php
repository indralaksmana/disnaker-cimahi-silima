<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JenisInstansiModel extends Model
{
    protected $table = 'master_jenis_instansi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_instansi',
    ];

    public function pemerintah()
    {
        return $this->hasMany('\App\Model\PemerintahModel', 'jenis_instansi_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BidangPekerjaanModel extends Model
{
    protected $table = 'master_bidang_kerja';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function bidangkerja()
    {
        return $this->hasMany('\App\Model\PencariKerjaModel', 'bidang_kerja_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

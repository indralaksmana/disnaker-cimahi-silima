<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FasilitasK3Model extends Model
{
    protected $table = 'fasilitas_k3';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function fasilitask3()
    {
        return $this->belongsToMany('\App\Model\DataPerusahaanModel', 'fasilitas_perusahaan_k3', 'data_perusahaan_id', 'fpk3_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

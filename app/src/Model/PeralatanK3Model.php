<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PeralatanK3Model extends Model
{
    protected $table = 'peralatan_k3';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
    ];

    public function statuspemilikanposts()
    {
        return $this->hasMany('\App\Model\CompaniesProfile', 'peralatan_k3_id');
    }
    public function perusahaan()
    {
        return $this->belongsTo('App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }

    public function nama()
    {
        $query =  $this->select('nama')->get()->pluck('nama');
        return $query;
    }
}

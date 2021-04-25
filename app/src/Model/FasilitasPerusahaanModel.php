<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FasilitasPerusahaanModel extends Model
{
    protected $table = 'fasilitas_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id'
    ];
    public function dataperusahaan()
    {
        return $this->belongsTo('App\Model\DataPerusahaanModel', 'data_perusahaan_id', 'id');
    }

    public function fasilitask3()
    {
        return $this->belongsTo('\App\Model\FasilitasK3Model', 'fasilitas_k3_id', 'id');
    }
    public function kesejahteraan()
    {
        return $this->belongsTo('\App\Model\KesejahteraanModel', 'kesejahteraan_id', 'id');
    }


}

<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KesejahteraanModel extends Model
{
    protected $table = 'kesejahteraan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function kesejahteraan()
    {
        return $this->belongsToMany('\App\Model\DataPerusahaanModel', 'fasilitas_perusahaan_kesejahteraan', 'data_perusahaan_id', 'fkesejahteraan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}

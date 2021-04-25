<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FasilitasPerusahaanKesejahteraanModel extends Model
{
    protected $table = 'fasilitas_perusahaan_kesejahteraan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'data_perusahaan_id',
        'fkesejahteraan_id'
    ];
}

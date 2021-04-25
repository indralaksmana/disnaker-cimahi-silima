<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProdukJasaPerusahaanModel extends Model
{
    protected $table = 'perusahaan_produk_dan_jasa';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
        'produ_jasa'
    ];
}

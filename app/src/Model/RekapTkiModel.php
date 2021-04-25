<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class RekapTkiModel extends Model
{
    protected $table = 'rekap_tki';
    protected $primaryKey = 'id';
    protected $fillable = [
    ];

    public function pendidikanterakhir()
    {
        return $this->belongsTo('\App\Model\JenisPendidikanModel', 'pendidikan_terakhir_id');
    }
    public function provinsi()
    {
        return $this->belongsTo('\App\Model\Daerah', 'provinsi_id');
    }
    public function kabupatenkota()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kabupatenkota_id');
    }
    public function kecamatan()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kecamatan_id');
    }
    public function kelurahan()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kelurahan_id');
    }
}

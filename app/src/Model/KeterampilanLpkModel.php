<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KeterampilanLpkModel extends Model
{
    protected $table = 'keterampilan_lpk';
    protected $primaryKey = 'id';
    protected $fillable = [
        'k_id',
        'lpk_id'
    ];

    public function lpk()
    {
        return $this->belongsTo('\App\Model\LpkModel', 'lpk_id', 'id');
    }
    public function jenisketerampilan()
    {
        return $this->belongsTo('\App\Model\KeterampilanModel', 'k_id', 'id');
    }
}

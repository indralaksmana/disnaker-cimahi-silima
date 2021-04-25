<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KeterampilanModel extends Model
{
    protected $table = 'master_keterampilan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'slug'
    ];

    public function keterampilanlpk()
    {
        return $this->hasMany('\App\Model\KeterampilanLpkModel', 'k_id');
    }

}

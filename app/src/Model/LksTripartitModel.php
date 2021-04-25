<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LksTripartitModel extends Model
{
    protected $table = 'lks_tripartit';
    protected $primaryKey = 'id';
    protected $fillable = [

    ];

    public function susunananggota()
    {
        return $this->hasMany('\App\Model\LksTripartitSusunanAnggotaModel', 'lks_tripartit_id', 'id');
    }
    public function sidang()
    {
        return $this->hasMany('\App\Model\LksTripartitSidangModel', 'lks_tripartit_id', 'id');
    }
}

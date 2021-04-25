<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SpektrumKeahlian extends Model
{
    protected $table = 'master_spektrum_keahlian';
    public $timestamps = false;

    public function jurusanbkk()
    {
        return $this->hasMany('\App\Model\JurusanBkkModel', 'sk_id');
    }
}

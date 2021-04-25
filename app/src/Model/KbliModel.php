<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KbliModel extends Model
{
    protected $table = 'kbli';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_klui',
    ];

    public function dataperusahaan()
    {
        return $this->hasMany('\App\Model\KbliModel', 'kbli_id');
    }

    public function no_klui()
    {
        $query =  $this->select('no_kbli')->get()->pluck('no_kbli');
        return $query;
    }
}

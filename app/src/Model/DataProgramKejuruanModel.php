<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DataProgramKejuruanModel extends Model
{
    protected $table = 'c_pp_data_bkk_program_kejuruan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'bkk_id',
        'tahun'
    ];

    public function pp()
    {
        return $this->hasMany('\App\Model\BkkModel', 'id', 'bkk_id');
    }
}

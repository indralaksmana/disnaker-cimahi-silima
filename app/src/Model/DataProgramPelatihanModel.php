<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DataProgramPelatihanModel extends Model
{
    protected $table = 'data_program_pelatihan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'lpk_id'
    ];

    public function pp()
    {
        return $this->hasMany('\App\Model\LpkModel', 'id', 'lpk_id');
    }
}

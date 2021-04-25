<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProgramStudiModel extends Model
{
    protected $table = 'master_program_studi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'slug'
    ];

    public function psdikti()
    {
        return $this->hasMany('\App\Model\ProgramStudiDiktiModel', 'ps_id');
    }

}


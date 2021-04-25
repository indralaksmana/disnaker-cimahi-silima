<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProgramStudiDiktiModel extends Model
{
    protected $table = 'program_studi_dikti';
    protected $primaryKey = 'id';
    protected $fillable = [
        'ps_id',
        'pt_id'
    ];

    public function dikti()
    {
        return $this->belongsTo('\App\Model\PerguruanTinggiModel', 'pt_id', 'id');
    }
    public function program()
    {
        return $this->belongsTo('\App\Model\ProgramStudiModel', 'ps_id', 'id');
    }
}

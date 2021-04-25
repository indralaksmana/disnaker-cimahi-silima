<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProgramPensiunModel extends Model
{
    protected $table = 'program_pensiun';
    protected $primaryKey = 'id';
    protected $fillable = [
        'data_perusahaan_id',
        'ppensiun_id'
    ];
}

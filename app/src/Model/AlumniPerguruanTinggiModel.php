<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AlumniPerguruanTinggiModel extends Model
{
    protected $table = 'c_pp_alumni_pt';
    protected $primaryKey = 'id';
    protected $fillable = [];
    public $timestamps = false;
}

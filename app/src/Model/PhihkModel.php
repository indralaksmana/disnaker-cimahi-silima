<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhihkModel extends Model
{
    protected $table = 'phihk';
    protected $primaryKey = 'id';
    protected $fillable = [
        'data_perusahaan_id',
        'phihk_id'
    ];
}

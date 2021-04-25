<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PhioModel extends Model
{
    protected $table = 'phio';
    protected $primaryKey = 'id';
    protected $fillable = [
        'data_perusahaan_id',
        'phio_id'
    ];
}

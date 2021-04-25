<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IPKModel extends Model
{
    protected $table = 'ipk';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];

}

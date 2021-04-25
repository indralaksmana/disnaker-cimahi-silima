<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IPKJPBulanModel extends Model
{
    protected $table = 'ipk_jp_perbulan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'ipk_jp_id',
    ];
    public function ipkjenispendidikan()
    {
        return $this->belongsTo('App\Model\IPKJenisPendidikanModel', 'ipk_jp_id', 'id');
    }
}

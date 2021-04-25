<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JurusanBkkModel extends Model
{
    protected $table = 'jurusan_bkk';
    protected $primaryKey = 'id';
    protected $fillable = [
        'sk_id',
        'bkk_id'
    ];

    public function bkk()
    {
        return $this->belongsTo('\App\Model\BkkModel', 'bkk_id', 'id');
    }
    public function sk()
    {
        return $this->belongsTo('\App\Model\SpektrumKeahlian', 'sk_id', 'id');
    }
}

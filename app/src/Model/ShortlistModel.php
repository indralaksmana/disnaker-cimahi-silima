<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ShortlistModel extends Model
{
    protected $table = 'b_pencaker_shortlist';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
        'pencaker_id',
    ];

    public function pencaker()
    {
        return $this->belongsTo('\App\Model\BkolPencariKerja', 'pencaker_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\users', 'user_id');
    }
}

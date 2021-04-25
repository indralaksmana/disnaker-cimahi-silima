<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanDaftarPesertaBalasan extends Model
{
    protected $table = 'pelatihan_daftar_peserta_balasan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'comment_id',
        'reply',
        'status'
    ];


    public function comment()
    {
        return $this->belongsTo('\App\Model\PelatihanDaftarPeserta', 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo('\App\Model\users', 'user_id');
    }
}

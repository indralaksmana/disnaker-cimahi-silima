<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KompetensiPosts extends Model
{
    protected $table = 'b_resume_kompetensi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'publish_at',
        'status'
    ];


    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }

    public function kompetensi()
    {
        return $this->hasOne('\App\Model\JenisKompetensiModel', 'id', 'jenis_kompetensi_id');
    }
}

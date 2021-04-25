<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanDaftarPeserta extends Model
{
    protected $table = 'c_pp_pelatihan_daftar_peserta';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'post_id',
        'status'
    ];

    public function replies()
    {
        return $this->hasMany('\App\Model\PelatihanPostsReplies', 'comment_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo('\App\Model\PelatihanPosts', 'post_id');
    }

    public function approvedBalasan()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPesertaBalasan', 'comment_id', 'id')->where('status', 1);
    }

    public function pendingBalasan()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPesertaBalasan', 'comment_id', 'id')->where('status', 0);
    }

    public function userpelatihan()
    {
        return $this->belongsTo('\App\Model\users', 'user_id');
    }
    public function userpemerintah()
    {
        return $this->belongsTo('\App\Model\PemerintahModel', 'user_id');
    }
}

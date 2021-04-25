<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanPosts extends Model
{
    protected $table = 'c_pp_pelatihan_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'slug',
        'content',
        'featured_image',
        'video_provider',
        'video_id',
        'publish_at',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo('\App\Model\PelatihanCategories', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany('\App\Model\PelatihanTags', 'c_pp_pelatihan_posts_tags', 'post_id', 'tag_id');
    }

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }

    public function peserta()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPeserta', 'post_id', 'id');
    }

    // public function approvedPendaftar()
    // {
    //     return $this->hasMany('\App\Model\PelatihanDaftarPeserta', 'post_id', 'id')->where('status', 1);
    // }
    public function PendaftarPelatihan()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPeserta', 'post_id', 'id');
    }

    public function pendingPendaftar()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPeserta', 'post_id', 'id')->where('status', 0);
    }
    public function laporan()
    {
        return $this->hasOne('\App\Model\PelatihanLaporanAkhirModel', 'pelatihan_id', 'id');
    }



}

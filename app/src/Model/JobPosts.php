<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobPosts extends Model
{
    protected $table = 'b_job_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'jobtitle',
        'description',
        'salary',
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
        return $this->belongsTo('\App\Model\JobCategories', 'category_id');
    }

    public function provinsi()
    {
        return $this->belongsTo('\App\Model\Daerah', 'provinsi_id');
    }
    public function kabupatenkota()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kabupatenkota_id');
    }
    public function jabatan()
    {
        return $this->belongsTo('\App\Model\GolonganJabatanModel', 'jabatan_id');
    }
    public function gaji()
    {
        return $this->belongsTo('\App\Model\GajiModel', 'gaji_id');
    }
    public function pendidikanterakhir()
    {
        return $this->belongsTo('\App\Model\JenisPendidikanModel', 'pendidikan_terakhir_id');
    }
    public function jurusanpendidikan()
    {
        return $this->belongsTo('\App\Model\JenisPendidikanModel', 'jurusan_pendidikan_id');
    }


    public function tags()
    {
        return $this->belongsToMany('\App\Model\JobTags', 'b_job_posts_tags', 'post_id', 'tag_id');
    }

    public function pelamar()
    {
        return $this->hasMany('\App\Model\JobPostsApply', 'post_id', 'id');
    }



    public function replies()
    {
        return $this->hasManyThrough(
            '\App\Model\JobPostsReplies',
            '\App\Model\JobPostsApply',
            'post_id',
            'comment_id',
            'id'
        );
    }

    public function approvedComments()
    {
        return $this->hasMany('\App\Model\JobPostsApply', 'post_id', 'id')->where('status', 0);
    }

    public function pendingComments()
    {
        return $this->hasMany('\App\Model\JobPostsApply', 'post_id', 'id')->where('status', 0);
    }

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
    public function authorCompanies()
    {
        //User antara job post sama bkolperusahaan
        return $this->hasOne('\App\Model\BkolPerusahaan', 'user_id', 'user_id');
    }
    public function pemerintah()
    {
        return $this->belongsTo('\App\Model\PemerintahModel', 'user_id');
    }
}

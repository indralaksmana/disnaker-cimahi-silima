<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProdukHukumPosts extends Model
{
    protected $table = 'produkhukum_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'judulprodukhukum',
        'tentang',
        'disahkan',
        'diundangkan',
        'slug',
        'uplad_file',
        'publish_at',
        'status'
    ];

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}

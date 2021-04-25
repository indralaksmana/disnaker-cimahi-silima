<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AgendaPosts extends Model
{
    protected $table = 'd_agenda_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'agendatitle',
        'slug',
        'content',
        'featured_image',
        'publish_at',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo('\App\Model\AgendaCategories', 'category_id');
    }

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}

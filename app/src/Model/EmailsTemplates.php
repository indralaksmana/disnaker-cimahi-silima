<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmailsTemplates extends Model
{
    protected $table = 'emails_templates';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'subject',
        'html',
        'plain_text',
        'placeholders'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StandardModel;

/**
 * @property bigIncrements $id 
 * @property string $title 
 * @property longtext $content 
 * @property enum $type 
 * @property string $category 
 * @property enum $link_source 
 * @property string $link_url 
 * @property dateTime $date 

 */
class Notifications extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, Notifications_H; 

    protected $table = 'notifications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 
        'content', 
        'type', 
        'category', 
        'link_source', 
        'link_url', 
        'date',
        'created_by',
        'created_at',
        'created_ip',
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'title'=> 'string', 
        'content'=> 'string', 
        'type'=> 'string', 
        'category'=> 'string', 
        'link_source'=> 'string', 
        'link_url'=> 'string', 
        'date'=> 'string',
    ];

    // automatic appends attributes
    protected $appends = [
        // 'log_data', // default log data, from StandardModel
        'link'
    ];

    /** Search Relations
    *   for availability searching by relation data, needed in StandardRepo
    */
    public function searchRelations() {
        try {
            return $this->bindSearchRelation([

            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    public function getLinkAttribute ($data = null) {
        $res = null;

        $link_source = $this->link_source;
        $link_url = $this->link_url;
            
        if ($res) return $res;
        else {
            if ($link_source == 'api') return env("API_URL").$link_url;
            elseif ($link_source == 'frontend') return env("UI_URL").$link_url;
            else return $link_url;
        }

    }

    /* Relations : belongsTo */



    /* Relations : hasMany */



    /* Relations : morphTo */



}        
        
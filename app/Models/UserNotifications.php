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
 * @property unsignedBigInteger $user_id 
 * @property boolean $is_read 
 * @property unsignedBigInteger $notification_id 

 */
class UserNotifications extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, UserNotifications_H; 

    protected $table = 'user_notifications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 
        'is_read', 
        'notification_id'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'user_id'=> 'integer', 
        'is_read'=> 'boolean', 
        'notification_id'=> 'integer'
    ];

    // automatic appends attributes
    protected $appends = [
        'log_data' // default log data, from StandardModel
        // 'your_attr'
    ];

    /** Search Relations
    *   for availability searching by relation data, needed in StandardRepo
    */
    public function searchRelations() {
        try {
            return $this->bindSearchRelation([
            'User' => 'Users',
            'Notification' => 'Notifications',

            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    /* Relations : belongsTo */

    public function User() {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withTrashed();
    }

    public function Notification() {
        return $this->belongsTo(Notifications::class, 'notification_id', 'id')->withTrashed();
    }



    /* Relations : hasMany */



    /* Relations : morphTo */



}        
        
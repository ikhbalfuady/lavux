<?php

namespace App\Models;

// Helper & Supporting Model
trait UserNotifications_H
{



    /* See details format on StandardModel->filterListBase() */
    public function filterList () {
        $overide = [

            [
                'id'        => 'id',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('bigIncrements')),
            ],
            [
                'id'        => 'user_id',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('unsignedBigInteger')),
                'sources'   => 'users',
                'key_value' => 'id',
                'key_label' => 'id',
            ],
            [
                'id'        => 'is_read',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('boolean')),
            ],
            [
                'id'        => 'notification_id',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('unsignedBigInteger')),
                'sources'   => 'notifications',
                'key_value' => 'id',
                'key_label' => 'id',
            ],
        ];
        $res = $this->mergeFilterList(
            $this->filterListBase(), // from "app/Traits/StandardModel"
            $overide // you can overide previous value into real value you need 
        );
        return $res;
       
    }
    
}

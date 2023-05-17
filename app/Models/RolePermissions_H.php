<?php

namespace App\Models;

// Helper & Supporting Model
trait RolePermissions_H
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
                'id'        => 'permission_id',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('unsignedBigInteger')),
                'sources'   => 'permissions',
                'key_value' => 'id',
                'key_label' => 'id',
            ],
            [
                'id'        => 'role_id',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('unsignedBigInteger')),
                'sources'   => 'roles',
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

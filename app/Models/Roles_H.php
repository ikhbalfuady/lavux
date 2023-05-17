<?php

namespace App\Models;

// Helper & Supporting Model
trait Roles_H
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
                'id'        => 'role_group_id',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('unsignedBigInteger')),
                'sources'   => 'role-groups',
                'key_value' => 'id',
                'key_label' => 'id',
            ],
            [
                'id'        => 'name',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'slug',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
        ];
        $res = $this->mergeFilterList(
            $this->filterListBase(), // from "app/Traits/StandardModel"
            $overide // you can overide previous value into real value you need 
        );
        return $res;
       
    }
    
}

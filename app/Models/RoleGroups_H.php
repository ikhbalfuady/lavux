<?php

namespace App\Models;

// Helper & Supporting Model
trait RoleGroups_H
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
                'id'        => 'name',
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

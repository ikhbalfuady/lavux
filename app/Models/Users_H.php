<?php

namespace App\Models;

/* Helper & Supporting Model
    use $this->DB('table') for make raw query
*/
trait Users_H {

    public function filterList () {
        $overide = [
            [
                'id'        => 'hobbies',
                'name'      => 'Hobbies',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('json')),
                'multiple'  => true,
            ],
        ];
        $res = $this->mergeFilterList(
            $this->filterListBase(), // from "app/Traits/StandardModel"
            $overide // you can overide previous value into real value you need 
        );
        return $res;
       
    }
    
    
}

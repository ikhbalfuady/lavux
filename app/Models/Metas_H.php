<?php

namespace App\Models;

// Helper & Supporting Model
trait Metas_H
{

    public function EnumType() {
        return ['string', 'double', 'integer', 'array', 'boolean', 'date'];
    }


    /* See details format on StandardModel->filterListBase() */
    public function filterList () {
        $overide = [

            [
                'id'        => 'id',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('bigIncrements')),
            ],
            [
                'id'        => 'type',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('enum')),
                'options'   => $this->EnumType(),
            ],
            [
                'id'        => 'slug',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'name',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'value',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('text')),
            ],
            [
                'id'        => 'description',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('text')),
            ],
            [
                'id'        => 'remarks',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('text')),
            ],
        ];
        $res = $this->mergeFilterList(
            $this->filterListBase(), // from "app/Traits/StandardModel"
            $overide // you can overide previous value into real value you need 
        );
        return $res;
       
    }
    
}

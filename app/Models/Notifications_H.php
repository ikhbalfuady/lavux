<?php

namespace App\Models;

// Helper & Supporting Model
trait Notifications_H
{

    public function EnumType() {
        return ['system','direct'];
    }
    public function EnumLinkSource() {
        return ['frontend','api','external'];
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
                'id'        => 'title',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'content',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('longtext')),
            ],
            [
                'id'        => 'type',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('enum')),
                'options'   => $this->EnumType(),
            ],
            [
                'id'        => 'category',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'link_source',
                'type'      => 'select',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('enum')),
                'options'   => $this->EnumLinkSource(),
            ],
            [
                'id'        => 'link_url',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('string')),
            ],
            [
                'id'        => 'date',
                'type'      => 'input',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('dateTime')),
            ],
        ];
        $res = $this->mergeFilterList(
            $this->filterListBase(), // from "app/Traits/StandardModel"
            $overide // you can overide previous value into real value you need 
        );
        return $res;
       
    }
    
}

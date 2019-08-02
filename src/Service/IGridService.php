<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;


interface IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params);

    /**
     * @param $params
     * @return array
     */
    public function list($params);
}
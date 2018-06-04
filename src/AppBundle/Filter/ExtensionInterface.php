<?php

namespace AppBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

use Nord\ElasticsearchBundle\Search\Query\QueryBuilder;

use Nord\ElasticsearchBundle\Search\Query\Compound\BoolQuery;

interface ExtensionInterface
{
    public function applyToCollection(Request $request, QueryBuilder $queryBuilder, BoolQuery $query);
}

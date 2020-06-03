<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

use Nord\ElasticsearchBundle\Search\Query\QueryBuilder;

use Nord\ElasticsearchBundle\Search\Query\Compound\BoolQuery;


use AppBundle\Filter\ExtensionInterface;

class SearchFilterExtension implements ExtensionInterface
{

    public function applyToCollection(Request $request, QueryBuilder $queryBuilder, BoolQuery $query)
    {
        if ($param = $request->query->get('search')) {
            $query->addMust(
                $queryBuilder->createQueryStringQuery()
                    ->setQuery('"' . strtolower($param) . '"'));
        }
    }

}

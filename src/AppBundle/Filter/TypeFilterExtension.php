<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

use Nord\ElasticsearchBundle\Search\Query\QueryBuilder;

use Nord\ElasticsearchBundle\Search\Query\Compound\BoolQuery;


use AppBundle\Filter\ExtensionInterface;

class TypeFilterExtension implements ExtensionInterface
{

    public function applyToCollection(Request $request, QueryBuilder $queryBuilder, BoolQuery $query)
    {
        $query->addMust(
            $queryBuilder->createExistsQuery()
                ->setField('*.@type')
        );
    }

}

<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

use Nord\ElasticsearchBundle\Search\Query\QueryBuilder;

use Nord\ElasticsearchBundle\Search\Query\Compound\BoolQuery;


use AppBundle\Filter\ExtensionInterface;

class LanguageFilterExtension implements ExtensionInterface
{

    public function applyToCollection(Request $request, QueryBuilder $queryBuilder, BoolQuery $query)
    {
        if ($language = $request->query->get('language')) {
            $query->addMust(
                $queryBuilder->createQueryStringQuery()
                    ->setFields(["*.@language"])
                    ->setQuery($language)
            );
        }
    }

}

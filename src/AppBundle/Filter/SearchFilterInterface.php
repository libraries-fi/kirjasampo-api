<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

use Nord\ElasticsearchBundle\Search\Query\QueryBuilder;

class SearchFilterInterface implements FilterInterface
{

    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
    
    }

    public function applyWithQuery(Request $request, QueryBuilder $queryBuilder)
    {
      $query = $queryBuilder->createBoolQuery();
      if ($param = $request->query->get('search')) {
          $query->addMust(
              $queryBuilder->createQueryStringQuery()
                  ->setQuery('"' . strtolower($param) . '"'));
      }

      if ($language = $request->query->get('language')) {
          $query->addMust(
              $queryBuilder->createQueryStringQuery()
                  ->setFields(["*.@language"])
                  ->setQuery($language)
          );
      }

      $query->addMust(
          $queryBuilder->createExistsQuery()
              ->setField('*.@type')
      );

      return $query;
    }

    public function getDescription(string $resourceClass) : array
    {
        $description = [];
        return $description;
    }
}

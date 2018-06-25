<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class SearchFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
      $parameterName = $queryNameGenerator->generateParameterName($property);
      $queryBuilder
            ->andWhere(sprintf('REGEXP(o.%s, :%s) = 1', 'content', $parameterName))
            ->setParameter($parameterName, $value);
    }

    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->properties as $property => $strategy) {
            $description['search'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => $strategy],
            ];
        }

        return $description;
    }
}

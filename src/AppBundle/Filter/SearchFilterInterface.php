<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Serializer\Filter\FilterInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchFilterInterface implements FilterInterface
{

    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {

    }

    public function getDescription(string $resourceClass) : array
    {
        $description = [];
        return $description;
    }
}

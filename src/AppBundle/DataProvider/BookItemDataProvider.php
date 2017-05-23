<?php

namespace AppBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use AppBundle\Entity\Book;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;

final class BookItemDataProvider implements ItemDataProviderInterface
{

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if (Book::class !== $resourceClass) {
            throw new ResourceClassNotSupportedException();
        }

        $client  = ClientBuilder::create()->build();
        $service = new ElasticsearchService($client);

        $queryBuilder = $service->createQueryBuilder();

        $query = $queryBuilder->createTermQuery()
                              ->setField('_id')
                              ->setValue($id);

        $search = $service->createSearch()
                          ->setIndex('books')
                          ->setType('book')
                          ->setQuery($query)
                          ->setSize(1)
                          ->setPage(1);

        $result = $service->execute($search);

        return $result['hits']['hits'];
    }
}

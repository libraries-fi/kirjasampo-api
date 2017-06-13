<?php

namespace AppBundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\Request;

final class DocumentCollectionDataProvider implements CollectionDataProviderInterface
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ElasticsearchService
     */
    private $service;

    /**
     * DocumentCollectionDataProvider constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $client        = ClientBuilder::create()->build();
        $this->service = new ElasticsearchService($client);
    }

    /**
     * @param string      $resourceClass
     * @param string|null $operationName
     *
     * @return mixed
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $queryBuilder = $this->service->createQueryBuilder();

        // TODO: Construct the query here.
        $query = $queryBuilder->createBoolQuery();

        $search = $this->service->createSearch()
                                ->setIndex('books')
                                ->setType('book')
                                ->setQuery($query)
                                ->setSize(1)
                                ->setPage(1);

        $result = $this->service->execute($search);

        return $result['hits']['hits'];
    }
}

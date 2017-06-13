<?php

namespace AppBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\Request;

final class DocumentItemDataProvider implements ItemDataProviderInterface
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
     * DocumentItemDataProvider constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $client        = ClientBuilder::create()->build();
        $this->service = new ElasticsearchService($client);
    }

    /**
     * @param string      $resourceClass
     * @param int|string  $id
     * @param string|null $operationName
     * @param array       $context
     *
     * @return array
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {

        $queryBuilder = $this->service->createQueryBuilder();

        $query = $queryBuilder->createBoolQuery()
                              ->addMust(
                                  $queryBuilder->createMatchQuery()
                                               ->setField('_id')
                                               ->setValue($id));

        $search = $this->service->createSearch()
                                ->setIndex('books')
                                ->setType('book')
                                ->setQuery($query)
                                ->setSize(1)
                                ->setPage(1);

        $result = $this->service->execute($search);

        return [new Document($result['hits']['hits'])];
    }

    /**
     * @param string      $resourceClass
     * @param             $id
     * @param string|null $operationName
     * @param array       $context
     *
     * @return mixed
     */
    public function searchItems(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->service->createQueryBuilder();

        // TODO: Construct the search query here.
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

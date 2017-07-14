<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\RequestStack;

final class DocumentItemDataProvider implements ItemDataProviderInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ElasticsearchService
     */
    private $service;

    /**
     * DocumentItemDataProvider constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

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
                                ->setIndex('kirjasampo')
                                ->setType('item')
                                ->setQuery($query)
                                ->setSize(1)
                                ->setPage(1);

        $result = $this->service->execute($search);

        //return [new Document($result['hits']['hits'])];

        return $result['hits']['hits'];
    }

    /**
     * @param string      $resourceClass
     * @param             $id
     * @param string|null $operationName
     * @param array       $context
     *
     * @return mixed
     */
    public function searchItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $queryBuilder = $this->service->createQueryBuilder();

        //$request = $this->requestStack->getCurrentRequest();

        // TODO: Construct the search query here.
        $query = $queryBuilder->createBoolQuery();

        $search = $this->service->createSearch()
                                ->setIndex('kirjasampo')
                                ->setType('item')
                                ->setQuery($query)
                                ->setSize(1)
                                ->setPage(1);

        $result = $this->service->execute($search);

        return $result['hits']['hits'];
    }

}

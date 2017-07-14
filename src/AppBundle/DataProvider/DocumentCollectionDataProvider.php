<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;

final class DocumentCollectionDataProvider implements CollectionDataProviderInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $resourceMetadataFactory;

    /**
     * @var String
     */
    private $paginationItemsPerPage;

    /**
     * @var ElasticsearchService
     */
    private $service;

    /**
     * DocumentCollectionDataProvider constructor.
     * @param RequestStack $requestStack
     * @param ResourceMetadataFactoryInterface $resourceMetadataFactory
     * @param Int $paginationItemsPerPage
     */
    public function __construct(RequestStack $requestStack, ResourceMetadataFactoryInterface $resourceMetadataFactory, $paginationItemsPerPage)
    {
        $this->requestStack = $requestStack;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->paginationItemsPerPage = $paginationItemsPerPage;

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
        $request = $this->requestStack->getCurrentRequest();

        $queryBuilder = $this->service->createQueryBuilder();

        $query = $queryBuilder->createBoolQuery();

        $search = $this->service->createSearch()
                                ->setIndex('kirjasampo')
                                ->setType('item')
                                ->setQuery($query)
                                ->setSize((int)$this->getItemsPerPage($resourceClass))
                                ->setPage((int)$request->query->get('page'));

        $result = $this->service->execute($search);

        return $this->convertResult($result);
    }

    public function searchCollection()
    {
        $request = $this->requestStack->getCurrentRequest();

        $queryBuilder = $this->service->createQueryBuilder();

        $query = $queryBuilder->createBoolQuery();

        $search = $this->service->createSearch()
            ->setIndex('kirjasampo')
            ->setType('item')
            ->setQuery($query)
            ->setSize(50)
            ->setPage(1);

        $result = $this->service->execute($search);

        return $this->convertResult($result);
    }

    /**
     * Convert the elasticsearch results to Documents
     *
     * @param $result
     * @return array
     */
    private function convertResult($result)
    {
        $entities = [];

        foreach ($result['hits']['hits'] as $resultItem) {
            $entities[] = new Document($resultItem['_id'], $resultItem['_source']);
        }

        return $entities;
    }

    /**
     * Get items per page count
     *
     * @param string $resourceClass
     * @return Int
     */
    private function getItemsPerPage($resourceClass)
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $paginationItemsPerPage = $resourceMetadata->getAttribute('pagination_items_per_page');

        return ($paginationItemsPerPage > 0) ? $paginationItemsPerPage : $this->paginationItemsPerPage;
    }

}

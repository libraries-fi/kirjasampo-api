<?php

namespace AppBundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\RequestStack;

use AppBundle\Filter\SearchFilterInterface;

use AppBundle\Filter\SearchFilterExtension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;

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

    private $searchExtensions;

    /**
     * DocumentCollectionDataProvider constructor.
     * @param RequestStack $requestStack
     * @param ResourceMetadataFactoryInterface $resourceMetadataFactory
     * @param Int $paginationItemsPerPage
     */
    public function __construct(RequestStack $requestStack, ResourceMetadataFactoryInterface $resourceMetadataFactory, $paginationItemsPerPage, $searchExtensions)
    {
        $this->requestStack = $requestStack;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->paginationItemsPerPage = $paginationItemsPerPage;

        $client = ClientBuilder::create()->build();
        $this->client = $client;
        $this->service = new ElasticsearchService($client);

        $this->searchExtensions = $searchExtensions;
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
        $query = $this->service->createQueryBuilder()->createBoolQuery();

        foreach ($this->searchExtensions as $extension) {
            $extension->applyToCollection($request, $this->service->createQueryBuilder(), $query);
        }

        $search = $this->service->createSearch()
            ->setIndex('kirjasampo')
            ->setType('item')
            ->setQuery($query)
            ->setSize((int) $this->getItemsPerPage($resourceClass))
            ->setPage((int) $request->query->get('page'));

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
            if (isset($resultItem['_source']['@type'])) {
                $resultItem['_source']['@contentType'] = $resultItem['_source']['@type'];
            }

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

        $request = $this->requestStack->getCurrentRequest();

        if ($param = $request->query->get('itemsPerPage')) {
            $paginationItemsPerPage = $param;
        }

        return ($paginationItemsPerPage > 0) ? $paginationItemsPerPage : $this->paginationItemsPerPage;
    }

}

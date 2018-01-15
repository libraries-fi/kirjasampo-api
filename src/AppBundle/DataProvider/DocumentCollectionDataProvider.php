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
        $this->client = $client;
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
        // $json = '{
        //     "query": {
        //         "query_string": {
        //             "fields": ["*.@language"],
        //             "query": "hi"
        //         }
        //     }
        // }';
        // $params = [
        //     'body' => $json,
        // ];
        // return $this->client->search($params);
        $request = $this->requestStack->getCurrentRequest();

        $queryBuilder = $this->service->createQueryBuilder();
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


        $search = $this->service->createSearch()
                                ->setIndex('kirjasampo')
                                ->setType('item')
                                ->setQuery($query)
                                ->setSize((int)$this->getItemsPerPage($resourceClass))
                                ->setPage((int)$request->query->get('page'));

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

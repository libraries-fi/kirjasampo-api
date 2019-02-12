<?php

namespace AppBundle\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;
use Nord\ElasticsearchBundle\ElasticsearchService;
use Symfony\Component\HttpFoundation\RequestStack;

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

        $client = ClientBuilder::create()->build();
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

        $query->addMust(
            $queryBuilder->createExistsQuery()
                ->setField('*.@type')
        );

        $search = $this->service->createSearch()
            ->setIndex('kirjasampo')
            ->setType('item')
            ->setQuery($query);

        $result = $this->service->execute($search);
        $sliceArguments = $this->customPagination($resourceClass);
        $hits = array_slice($this->fetchRelatedDocuments($result)['hits']['hits'], $sliceArguments['from'], $sliceArguments['to']);

        $result['hits']['hits'] = $hits;

        return $this->convertResult($result);
    }

    /**
     * Fetching related resources
     *
     * @param $data
     * @return mixed
     */
    public function fetchRelatedDocuments($data)
    {
        $prefix = array(
            "ketjutettu_asiasana", "worldPlace", "sivuUrl", "hasReview", "eSampo", "tekija", "manifests_in",
            "manifests_in_part", "kansikuva", "tiedostoUrl", "ilmestymisvuosi", "inverseProperty", "kaantaja",
            "sarjaInstanssi", "palkintosarja", "hasAward"
        );

        $params = [
            'body' => [
                'docs' => [

                ]
            ]
        ];

        $relatedIDs = array();
        //collect id's of related resources
        for ($i = 0; $i < count($data['hits']['hits']); $i++) {
            $source = $data['hits']['hits'][$i]['_source'];
            foreach ($source as $key => $value) {
                $splittedByHash = explode('#', $key);
                $splittedBySlash = explode('/', $key);
                if (in_array(end($splittedByHash), $prefix) || in_array(end($splittedBySlash), $prefix)) {
                    foreach ($value as $item) {
                        if (array_key_exists('@id', $item)) {
                            array_push($relatedIDs, $item['@id']);
                        }
                    }
                }
            }
        }
        $relatedIDs = array_unique($relatedIDs);
        foreach ($relatedIDs as $id) {
            array_push($params['body']['docs'], [
                '_index' => 'kirjasampo',
                '_type' => 'item',
                '_id' => $id
            ]);
        }

        //make only one mget to fetching data
        if (!empty($params['body']['docs'])) {
            $response = $this->client->mget($params);
            $response['docs'] = array_filter($response['docs'], function ($document) {
                return isset($document['_source']);
            });
            $merged = array_merge($data['hits']['hits'], $response['docs']);
            $data['hits']['hits'] = $merged;
        }
        return $data;
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
     * Get arguments for array_slice
     *
     * @param string $resourceClass
     * @return array
     */
    public function customPagination($resourceClass)
    {
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);

        $paginationItemsPerPage = $resourceMetadata->getAttribute('pagination_items_per_page');
        $request = $this->requestStack->getCurrentRequest();
        $page = $request->query->get('page');
        if ($param = $request->query->get('itemsPerPage')) {
            $paginationItemsPerPage = $param;
        }
        --$page;

        return array('from' => $page * $paginationItemsPerPage, 'to' => $paginationItemsPerPage);
    }

}

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
                                  $queryBuilder->createQueryStringQuery()
                                               ->setQuery('"' . urldecode(strtolower($id)) . '"'));

        $search = $this->service->createSearch()
                                ->setIndex('kirjasampo')
                                ->setType('item')
                                ->setQuery($query)
                                ->setSize(1)
                                ->setPage(1);

        $result = $this->service->execute($search)['hits']['hits'];

        if($result) {
            if (isset($resultItem['_source']['@type'])) {
                $resultItem['_source']['@contentType'] = $resultItem['_source']['@type'];
            }
            $relatedDocuments = new GetRelatedDocument($result[0]);
            $relatedDocuments = $relatedDocuments->getRelatedDocuments();
            $result[0]['_source']['@relatedDocuments'] = $relatedDocuments;

            return new Document($result[0]['_id'], $result[0]['_source']);
        } else {
            return null;
        }
    }
}

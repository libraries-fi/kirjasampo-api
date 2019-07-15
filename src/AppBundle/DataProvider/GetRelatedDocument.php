<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;

class GetRelatedDocument
{
    /**
     * @param $resources
     * @return array
     */
    public static function getRelatedDocuments($resources)
    {
        $ids = [];
        $result = [];
        $params = ['body' => ['docs' => []]];

        if (!count($resources))
            return [];

        foreach ($resources as $items) {
            $ids[] = $items;
        }

        foreach ($ids as $id) {
            $params['body']['docs'] [] = [
                '_index' => 'kirjasampo',
                '_type' => 'item',
                '_id' => $id
            ];
        }

        if (!empty($params['body']['docs'])) {
            $response = ClientBuilder::create()->build()->mget($params);
            $response['docs'] = array_filter($response['docs'], function ($document) {
                return isset($document['_source']);
            });

            foreach ($response['docs'] as $doc) {
                $docObj = new Document($doc['_id'], $doc['_source']);
                $result [] = $docObj;
            }
        }

        return $result;
    }
}

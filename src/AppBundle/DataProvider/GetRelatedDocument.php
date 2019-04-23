<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;

class GetRelatedDocument
{
    /**
     * @param $relatedIds
     * @return array|null
     */
    public static function getRelatedDocuments($relatedIds)
    {
        $params = ['body' => ['docs' => []]];

        if ($relatedIds)
            foreach ($relatedIds as $id) {
                array_push($params['body']['docs'], [
                    '_index' => 'kirjasampo',
                    '_type' => 'item',
                    '_id' => $id
                ]);
            }
        else
            return null;

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

        return $result ?? null;
    }
}

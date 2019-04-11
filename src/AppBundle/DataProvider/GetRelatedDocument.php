<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;

class GetRelatedDocument
{
    /**
     * @param $document
     * @return array
     */
    public static function getRelatedDocuments($document)
    {
        $relatedPattern = "/ketjutettu_asiasana|worldPlace|sivuUrl|hasReview|eSampo|tekija|
                           manifests_in|manifests_in_part|kansikuva|tiedostoUrl|ilmestymisvuosi|
                           inverseProperty|kaantaja|sarjaInstanssi|palkintosarja|hasAward/i";

        $params = ['body' => ['docs' => []]];

        $relatedIDs = [];
        $result = [];

        foreach ($document['_source'] as $key => $value) {
            if (preg_match($relatedPattern, $key)) {
                foreach ($value as $item) {
                    if (array_key_exists('@id', $item)) {
                        array_push($relatedIDs, $item['@id']);
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

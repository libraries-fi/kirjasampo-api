<?php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Document;
use Elasticsearch\ClientBuilder;

class GetRelatedDocument
{

    private $client;

    /**
     * @var object
     */
    private $source;

    /**
     * GetRelatedDocument constructor.
     * @param object $document
     */
    public function __construct($document)
    {
        $this->source = $document['_source'];
        $this->client = ClientBuilder::create()->build();
    }

    public function getRelatedDocuments()
    {
        $relatedPattern = "/ketjutettu_asiasana|worldPlace|sivuUrl|hasReview|eSampo|tekija|
                           manifests_in|manifests_in_part|kansikuva|tiedostoUrl|ilmestymisvuosi|
                           inverseProperty|kaantaja|sarjaInstanssi|palkintosarja|hasAward/i";

        $params = ['body' => ['docs' => []]];

        $relatedIDs = [];
        $result = [];

        foreach ($this->source as $key => $value) {
            if (preg_match($relatedPattern, $key)) {
                if (!in_array($key, $relatedIDs))
                    $relatedIDs [] = $key;
            }
        }

        foreach ($relatedIDs as $id) {
            array_push($params['body']['docs'], [
                '_index' => 'kirjasampo',
                '_type' => 'item',
                '_id' => $id
            ]);
        }

        if (!empty($params['body']['docs'])) {
            $response = $this->client->mget($params);
            $response['docs'] = array_filter($response['docs'], function ($document) {
                return isset($document['_source']);
            });

            foreach ($response['docs'] as $doc) {
                $docObj = new Document($doc['_id'], $doc['_source']);
                $result [] = $docObj->getContent();
            }
        }

        return $result;
    }
}

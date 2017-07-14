<?php

namespace AppBundle\Action;

use AppBundle\Entity\Document;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\DataProvider\DocumentCollectionDataProvider;

class Search
{
    private $documentCollectionDataProvider;

    public function __construct(DocumentCollectionDataProvider $documentCollectionDataProvider)
    {
        $this->documentCollectionDataProvider = $documentCollectionDataProvider;
    }

    /**
     * @Route(
     *     name="search",
     *     path="search",
     *     defaults={"_api_resource_class"=Document::class, "_api_collection_operation_name"="search"}
     * )
     * @Method("GET")
     */
    public function __invoke()
    {
        return $this->documentCollectionDataProvider->searchCollection();
    }
}
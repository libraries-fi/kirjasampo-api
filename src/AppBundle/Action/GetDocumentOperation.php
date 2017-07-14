<?php

namespace AppBundle\Action;

use AppBundle\Entity\Document;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\DataProvider\DocumentItemDataProvider;

/**
 * Action for getting a single resource.
 *
 * This action supports slashes in the id parameter to allow using an IRI.
 **/

class GetDocumentOperation
{
    private $documentItemDataProvider;

    public function __construct(DocumentItemDataProvider $documentItemDataProvider)
    {
        $this->documentItemDataProvider = $documentItemDataProvider;
    }

    /**
     * @Route(
     *     name="api_documents_get_item",
     *     path="/documents/{id}",
     *     requirements={"id"=".+"},
     *     defaults={"_api_resource_class"=Document::class, "_api_item_operation_name"="get"}
     * )
     * @Method("GET")
     */
    public function __invoke($data)
    {
        return $data;
    }
}
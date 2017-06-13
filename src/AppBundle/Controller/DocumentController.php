<?php

namespace AppBundle\Controller;

use AppBundle\DataProvider\DocumentCollectionDataProvider;
use AppBundle\DataProvider\DocumentItemDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends Controller
{

    /**
     * @Route("/search")
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function searchAction(Request $request)
    {
        $dataProvider = new DocumentItemDataProvider($request);

        return $dataProvider->searchItems('Document', null);
    }

    /**
     * @Route("/")
     *
     * @param Request $request
     *
     * @return \ApiPlatform\Core\DataProvider\PaginatorInterface|array|\Traversable
     */
    public function indexAction(Request $request)
    {
        $dataProvider = new DocumentCollectionDataProvider($request);

        return $dataProvider->getCollection('Document');
    }

    /**
     * @Route("/{id}")
     *
     * @param         $id
     * @param Request $request
     *
     * @return array
     */
    public function showAction($id, Request $request)
    {
        $dataProvider = new DocumentItemDataProvider($request);

        return $dataProvider->getItem('Document', $id);
    }

}

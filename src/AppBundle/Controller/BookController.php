<?php

namespace AppBundle\Controller;

use AppBundle\DataProvider\BookItemDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BookController extends Controller
{

    /**
     * @Route("/{uuid}")
     *
     * @param $uuid
     *
     * @return null|object
     */
    public function showAction($uuid)
    {
        $dataProvider = new BookItemDataProvider();

        return $dataProvider->getItem('Book', $uuid);
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //
    }

    /**
     * @Route("/search")
     */
    public function searchAction()
    {
        //
    }

}

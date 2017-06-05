<?php

namespace AppBundle\Controller;

use AppBundle\DataProvider\BookItemDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BookController extends Controller
{

    /**
     * @Route("/{id}")
     *
     * @param id
     *
     * @return null|object
     */
    public function showAction($id)
    {
        $dataProvider = new BookItemDataProvider();

        return $dataProvider->getItem('Book', $id);
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

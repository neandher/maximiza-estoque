<?php

namespace App\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 * @package App\Controller
 *
 * @Route("", name="admin_")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function dashboard()
    {
        return $this->render('admin/dashboard/index.html.twig');
    }

    /**
     * @Route("/dfe", name="nfe")
     */
    public function nfe()
    {
        return new Response('ola!');
    }
}
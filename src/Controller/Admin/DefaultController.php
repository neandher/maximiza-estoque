<?php

namespace App\Controller\Admin;

use GuzzleHttp\Client;
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
     * @Route("/nfe", name="nfe")
     */
    public function nfe()
    {
        return new Response('ola!');
    }
}
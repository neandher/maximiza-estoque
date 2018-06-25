<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
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
        $total['add'] = $this->getDoctrine()->getRepository(Stock::class)->getTotalAdd()['totalAdd']??0;
        $total['remove'] = $this->getDoctrine()->getRepository(Stock::class)->getTotalRemove()['totalRemove']??0;
        $total['total'] = $this->getDoctrine()->getRepository(Stock::class)->getTotal()['total']??0;

        return $this->render('admin/dashboard/index.html.twig', [
            'total' => $total
        ]);
    }

    /**
     * @Route("/nfe", name="nfe")
     */
    public function nfe()
    {
        return new Response('ola!');
    }
}
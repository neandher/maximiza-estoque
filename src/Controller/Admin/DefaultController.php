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
        $total['add'] = $this->getDoctrine()->getRepository(Stock::class)->getTotalAdd()['totalAdd'];
        $total['remove'] = $this->getDoctrine()->getRepository(Stock::class)->getTotalRemove()['totalRemove'];
        $total['total'] = $this->getDoctrine()->getRepository(Stock::class)->getTotal()['total'];

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
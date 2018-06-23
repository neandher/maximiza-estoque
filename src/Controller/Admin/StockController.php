<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Stock;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Form\StockType;
use App\StockTypes;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StockController
 * @package App\Controller\Admin
 *
 * @Route("/stock", name="admin_stock_")
 */
class StockController extends BaseController
{
    /**
     * @var Pagination
     */
    private $pagination;
    /**
     * @var FlashBag
     */
    private $flashBag;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * StockController constructor.
     * @param Pagination $pagination
     * @param FlashBag $flashBag
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Pagination $pagination, FlashBag $flashBag, EventDispatcherInterface $dispatcher)
    {
        $this->pagination = $pagination;
        $this->flashBag = $flashBag;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        /** @var Stock[] $stocks */
        $stocks = $this->getDoctrine()->getRepository(Stock::class)->findLatest($pagination);

        $users = $this->getDoctrine()->getRepository(User::class)->queryLatestForm()
            ->getQuery()->getResult();

        $deleteForms = [];
        $total = [];
        $totalAdd = 0;
        $totalRemove = 0;

        foreach ($stocks as $stock) {
            $deleteForms[$stock->getId()] = $this->createDeleteForm($stock)->createView();
            if ($stock->getType() == StockTypes::TYPE_ADD) {
                $totalAdd += $stock->getQuantity();
            }
            if ($stock->getType() == StockTypes::TYPE_REMOVE) {
                $totalRemove += $stock->getQuantity();
            }
        }

        $total[StockTypes::TYPE_ADD] = $totalAdd;
        $total[StockTypes::TYPE_REMOVE] = $totalRemove;
        $total['total'] = $totalAdd - $totalRemove;

        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $stocks,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
            'total' => $total,
            'users' => $users
        ]);
    }

    /**
     * @Route("/new", name="new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        $stock = new Stock();

        $form = $this->createForm(StockType::class, $stock);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($stock);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_stock_new',
                'admin_stock_edit',
                ['id' => $stock->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('admin/stock/new.html.twig', [
            'stock' => $stock,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Stock $stock
     * @return Response
     */
    public function editAction(Stock $stock, Request $request)
    {
        $stock->normalizeQuantity();

        $pagination = $this->pagination->handle($request, Stock::class);

        $form = $this->createForm(StockType::class, $stock);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($stock);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_stock_new',
                'admin_stock_edit',
                ['id' => $stock->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('admin/stock/edit.html.twig', [
            'stock' => $stock,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Stock $stock
     * @return Response
     */
    public function deletAction(Request $request, Stock $stock)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        $form = $this->createDeleteForm($stock);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($stock);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_DELETED
            );
        } else {
            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_ERROR,
                FlashBagEvents::MESSAGE_ERROR_DELETED
            );
        }

        return $this->redirectToRoute('admin_stock_index', $pagination->getRouteParams());
    }

    /**
     * @param Stock $stock
     * @return FormInterface
     */
    private function createDeleteForm(Stock $stock)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_stock_delete', ['id' => $stock->getId()]))
            ->setMethod('DELETE')
            ->setData($stock)
            ->getForm();
    }
}

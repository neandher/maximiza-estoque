<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Stock;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Event\StockEvents;
use App\Form\StockImportXmlType;
use App\Form\StockMultipleType;
use App\Form\StockType;
use App\StockTypes;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $formXml = $this->createForm(StockImportXmlType::class);

        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $stocks,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
            'total' => $total,
            'users' => $users,
            'formXml' => $formXml->createView()
        ]);
    }

    /**
     * @Route("/new-old", name="new_old")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newOldAction(Request $request)
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
     * @Route("/new", name="new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        $form = $this->createForm(StockMultipleType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Stock[] $stock */
            $stocks = $form->getData()['stocks'];
            foreach ($stocks as $stock) {
                $em->persist($stock);
            }
            $em->flush();

            $this->dispatcher->dispatch(StockEvents::STOCK_CREATE_COMPLETED, (new GenericEvent($stocks)));

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            return $this->redirectToRoute('admin_stock_index', $pagination->getRouteParams());
        }

        return $this->render('admin/stock/new.html.twig', [
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

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_stock_index', $pagination->getRouteParams());
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
    public function delete(Request $request, Stock $stock)
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
     * @Route("/delete-items", name="delete_items")
     * @Method("DELETE")
     * @param Request $request
     * @return Response
     */
    public function deleteItems(Request $request)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        if ($request->request->has('ids')) {
            $i = 0;
            $em = $this->getDoctrine()->getManager();
            foreach (explode(',', $request->request->get('ids')) as $id) {
                $stock = $this->getDoctrine()->getRepository(Stock::class)->findOneBy(['id' => $id]);
                if ($stock) {
                    $em->remove($stock);
                    $i++;
                }
            }
            $em->flush();
            if ($i > 0) {
                $this->flashBag->newMessage(
                    FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                    $i . ' registro(s) deletado(s).'
                );
            }
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

    /**
     * @Route("/import-xml", name="import_xml")
     * @Method("POST")
     * @param Request $request
     * @return RedirectResponse
     */
    public function importXml(Request $request)
    {
        $form = $this->createForm(StockImportXmlType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()['file'];

            $xml = file_get_contents($file->getRealPath());

            $domDocument = new \DOMDocument();
            $domDocument->loadXml($xml);
            $nodeList = $domDocument->getElementsByTagName('det');

            if ($nodeList->length > 0) {
                $stocks = [];
                $em = $this->getDoctrine()->getManager();

                for ($i = 0; $i < $nodeList->length; $i++) {
                    $referency = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('cProd')->item(0)->childNodes->item(0)->wholeText;
                    $quantity = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('qCom')->item(0)->childNodes->item(0)->wholeText;

                    $stock = new Stock();
                    $stock->setReferency($referency)
                        ->setQuantity($quantity)
                        ->setType(StockTypes::TYPE_ADD);

                    $stocks[] = $stock;
                    $em->persist($stock);
                }
                $em->flush();

                $this->dispatcher->dispatch(StockEvents::STOCK_CREATE_COMPLETED, (new GenericEvent($stocks)));

                $this->flashBag->newMessage(
                    FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                    'XML Importado com sucesso. Total de ' . $nodeList->length . ' registros importados'
                );

                return $this->redirectToRoute('admin_stock_index');
            }
        }

        $this->flashBag->newMessage(
            FlashBagEvents::MESSAGE_TYPE_SUCCESS,
            'Nenhum registro importado'
        );

        return $this->redirectToRoute('admin_stock_index');
    }

    /**
     * @Route("/verify-referency", name="verify_referency", methods={"GET"}, options={"expose"="true"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function verifyReferency(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Invalid request'], 400);
        }

        $referency = $request->query->get('referency') ?? null;

        if (!$referency) {
            return new JsonResponse(['message' => 'Invalid parameters'], 400);
        }

        $stock = $this->getDoctrine()->getRepository(Stock::class)->findOneBy(['referency' => $referency]);

        if ($stock) {
            return new JsonResponse(['message' => 'Success']);
        }

        return new JsonResponse(['message' => 'Not Found'], 404);
    }
}

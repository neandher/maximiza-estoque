<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Brand;
use App\Entity\Stock;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Event\StockEvents;
use App\Form\StockImportXmlType;
use App\Form\StockMultipleType;
use App\Form\StockType;
use App\StockTypes;
use App\Util\FlashBag;
use App\Util\Helpers;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
     * @IsGranted("ROLE_LEVEL_2")
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

        $brands = $this->getDoctrine()->getRepository(Brand::class)->queryLatestForm()
            ->getQuery()->getResult();

        $deleteForms = [];
        $total = [];
        $totalAdd = 0;
        $totalRemove = 0;
        $totalAddAmount = 0;
        $totalRemoveAmount = 0;

        foreach ($stocks as $stock) {
            $deleteForms[$stock->getId()] = $this->createDeleteForm($stock)->createView();
            if ($stock->getType() == StockTypes::TYPE_ADD) {
                $totalAdd += $stock->getQuantity();
                $totalAddAmount += $stock->getAmount();
            }
            if ($stock->getType() == StockTypes::TYPE_REMOVE) {
                $totalRemove += $stock->getQuantity();
                $totalRemoveAmount += $stock->getAmount();
            }
        }

        $total[StockTypes::TYPE_ADD] = $totalAdd;
        $total['total_add_amount'] = $totalAddAmount;
        $total[StockTypes::TYPE_REMOVE] = $totalRemove;
        $total['total_remove_amount'] = $totalRemoveAmount;
        $total['total'] = $totalAdd + $totalRemove;
        $total['total_amount'] = $totalAddAmount + $totalRemoveAmount;

        //$formXml = $this->createForm(StockImportXmlType::class);

        return $this->render('admin/stock/index.html.twig', [
            'stocks' => $stocks,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
            'total' => $total,
            'users' => $users,
            'brands' => $brands
            //'formXml' => $formXml->createView()
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
     * @IsGranted("ROLE_LEVEL_2")
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
            /** @var Stock $stock */
            foreach ($stocks as $stock) {
                $stock->setAmount($stock->getUnitPrice() * $stock->getQuantity());
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
     * @IsGranted("ROLE_LEVEL_2")
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

            $stock->setAmount($stock->getUnitPrice() * $stock->getQuantity());
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
     * @IsGranted("ROLE_LEVEL_2")
     * @Method("DELETE")
     * @IsGranted("ROLE_LEVEL_2")
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
     * @IsGranted("ROLE_LEVEL_2")
     * @IsGranted("ROLE_LEVEL_2")
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
     * @IsGranted("ROLE_LEVEL_2")
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function importXml(Request $request)
    {
        if (!$request->isXmlHttpRequest())
            return new JsonResponse(['message' => 'Requisição inválida.'], 403);

        $form = $this->createForm(StockImportXmlType::class, null, ['csrf_protection' => false]);

        $data['file'] = $request->files->all()['file'];
        $form->submit($data);

        if (!$form->isValid())
            return new JsonResponse(['message' => Helpers::getErrorsFromForm($form, 'string')], 403);

        /** @var UploadedFile $file */
        $file = $data['file'];

        $xml = file_get_contents($file->getRealPath());

        $domDocument = new \DOMDocument();
        $domDocument->loadXml($xml);
        $nodeList = $domDocument->getElementsByTagName('det');

        if (!$request->request->get('type') || !$request->request->get('brand')) {
            return new JsonResponse(['message' => 'invalid_request',], 400);
        }

        $brand = $this->getDoctrine()->getRepository(Brand::class)->findOneBy(['id' => $request->request->get('brand')]);

        if (!$brand) {
            return new JsonResponse(['message' => 'invalid_request',], 400);
        }

        if ($nodeList->length > 0) {
            $stocks = [];
            $em = $this->getDoctrine()->getManager();

            for ($i = 0; $i < $nodeList->length; $i++) {
                $referency = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('cProd')->item(0)->childNodes->item(0)->wholeText;
                $quantity = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('qCom')->item(0)->childNodes->item(0)->wholeText;
                $amount = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('vProd')->item(0)->childNodes->item(0)->wholeText;
                $unitPrice = $nodeList->item($i)->getElementsByTagName('prod')->item(0)->getElementsByTagName('vUnCom')->item(0)->childNodes->item(0)->wholeText;

                $stock = new Stock();
                $stock->setReferency($referency)
                    ->setType($request->request->get('type'))
                    ->setQuantity($quantity)
                    ->setUnitPrice($unitPrice)
                    ->setAmount($stock->getQuantity() * $amount)
                    ->setBrand($brand);

                $stocks[] = $stock;
                $em->persist($stock);
            }
            $em->flush();

            $this->dispatcher->dispatch(StockEvents::STOCK_CREATE_COMPLETED, (new GenericEvent($stocks)));
        }

        return new JsonResponse(['message' => 'success', 'items' => $nodeList->length]);
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

        $repo = $this->getDoctrine()->getRepository(Stock::class);
        $stock = $repo->findOneBy(['referency' => $referency]);

        if ($stock) {

            $checkBalance = $request->query->get('balance') ?? false;

            if ($checkBalance) {
                $request->query->add(['search' => $referency]);
                $pagination = new Pagination(null);
                $pagination->handle($request);
                $referencyBalance = $repo->balance($pagination)[0]['saldo'];
                $stock->setBalance($referencyBalance);
            }

            $encoders = [new JsonEncoder()];
            $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $jsonContent = $serializer->serialize($stock, 'json');
            return JsonResponse::fromJsonString($jsonContent);
        }

        return new JsonResponse(['message' => 'Not Found'], 404);
    }

    /**
     * @Route("/balance", name="balance")
     * @param Request $request
     * @return Response
     */
    public function balance(Request $request)
    {
        $pagination = $this->pagination->handle($request, Stock::class);

        $brands = $this->getDoctrine()->getRepository(Brand::class)->queryLatestForm()
            ->getQuery()->getResult();

        $stocks = $this->getDoctrine()->getRepository(Stock::class)->balancePaginator($pagination);

        return $this->render('admin/stock/balance.html.twig', [
            'stocks' => $stocks,
            'brands' => $brands,
            'pagination' => $pagination,
        ]);
    }
}

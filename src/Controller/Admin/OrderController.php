<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Order;
use App\Entity\Stock;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Form\OrderType;
use App\StockPaymentMethods;
use App\StockTypes;
use App\Util\FlashBag;
use App\Util\Pagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse as BinaryFileResponseAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class OrderController
 * @package App\Controller\Admin
 *
 * @Route("/order", name="admin_order_")
 */
class OrderController extends BaseController
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
     * OrderController constructor.
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
        $pagination = $this->pagination->handle($request, Order::class);

        /** @var Order[] $orders */
        $orders = $this->getDoctrine()->getRepository(Order::class)->findLatest($pagination);

        $users = $this->getDoctrine()->getRepository(User::class)->queryLatestForm()
            ->getQuery()->getResult();

        $paymentMethods = StockPaymentMethods::PAYMENT_METHODS;

        $deleteForms = [];

        if ($request->query->get('exp') === 'excel') {
            return $this->exportExcel($orders);
        }

        $total = 0;
        foreach ($orders as $order) {
            $deleteForms[$order->getId()] = $this->createDeleteForm($order)->createView();
            $total += $order->getTotal();
        }


        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
            'users' => $users,
            'paymentMethods' => $paymentMethods,
            'total' => $total
        ]);
    }

    /**
     * @Route("/new-old", name="new_od", methods={"POST"}, options={"expose"="true"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function newOldAction(Request $request, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Invalid request'], 400);
        }

        $obj = json_decode($request->getContent(), true);

        $context = [];
        if (key_exists('id', $obj)) {
            $qb = $this->getDoctrine()->getRepository(Order::class)->createQueryBuilder('ord');
            $find = $qb
                ->innerJoin('ord.orderItems', 'orderItems')
                ->addSelect('orderItems')
                ->where('ord.id = :id')
                ->setParameter('id', $obj['id'])
                ->getQuery()
                ->getOneOrNullResult();
            // $find = $this->getDoctrine()->getRepository(Order::class)->find($obj['id']);
            dump($find);
            $context['object_to_populate'] = $find;
        }

        dump($request->getContent());

        /** @var Order $orderData */
        $orderData = $serializer->deserialize($request->getContent(), Order::class, 'json', $context);
        dump($orderData);
        $errors = $validator->validate($orderData);

        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Error'], 400);
        }

        $orderData->setUser($this->getUser());

        $em = $this->getDoctrine()->getManager();
//        if ($orderData->getId()) {
//            $em->merge($orderData);
//        } else {
//            $em->persist($orderData);
//        }
        $em->merge($orderData);

        foreach ($orderData->getOrderItems() as $orderItem) {
            $stockByReferency = $this->getDoctrine()->getRepository(Stock::class)->findOneBy(['referency' => $orderItem->getReferency()]);

            if (!$stockByReferency) {
                return new JsonResponse(['message' => 'Referência ' . $orderItem->getReferency() . ' não encontrada.'], 400);
            }

            if (!$orderData->getId()) {
                $stock = new Stock();
                $stock
                    ->setType(StockTypes::TYPE_REMOVE)
                    ->setQuantity($orderItem->getQuantity())
                    ->setAmount($stock->getQuantity() * $orderItem->getPrice())
                    ->setUnitPrice($orderItem->getPrice())
                    ->setCustomer(null)
                    ->setPaymentMethod($orderData->getPaymentMethod())
                    ->setReferency($orderItem->getReferency())
                    ->setBrand($stockByReferency->getBrand());

                $em->persist($stock);
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'success'], 200);
    }

    /**
     * @Route("/{id}/details", name="details", methods={"GET"}, options={"expose"="true"})
     * @param Request $request
     * @param Order $order
     * @return JsonResponse|Response
     */
    public function details(Request $request, Order $order)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Invalid request'], 400);
        }

        if ($order) {
            $encoders = [new JsonEncoder()];
            $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $jsonContent = $serializer->serialize($order, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
            return JsonResponse::fromJsonString($jsonContent);
        }

        return new JsonResponse(['message' => 'Not Found'], 404);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @IsGranted("ROLE_LEVEL_2")
     * @Method("DELETE")
     * @param Request $request
     * @param Order $order
     * @return Response
     */
    public function delete(Request $request, Order $order)
    {
        $pagination = $this->pagination->handle($request, Order::class);

        $form = $this->createDeleteForm($order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($order);
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

        return $this->redirectToRoute('admin_order_index', $pagination->getRouteParams());
    }

    /**
     * @Route("/delete-items", name="delete_items")
     * @IsGranted("ROLE_LEVEL_2")
     * @Method("DELETE")
     * @param Request $request
     * @return Response
     */
    public function deleteItems(Request $request)
    {
        $pagination = $this->pagination->handle($request, Order::class);

        if ($request->request->has('ids')) {
            $i = 0;
            $em = $this->getDoctrine()->getManager();
            foreach (explode(',', $request->request->get('ids')) as $id) {
                $order = $this->getDoctrine()->getRepository(Order::class)->findOneBy(['id' => $id]);
                if ($order) {
                    $em->remove($order);
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

        return $this->redirectToRoute('admin_order_index', $pagination->getRouteParams());
    }

    /**
     * @param Order $order
     * @return FormInterface
     */
    private function createDeleteForm(Order $order)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_order_delete', ['id' => $order->getId()]))
            ->setMethod('DELETE')
            ->setData($order)
            ->getForm();
    }

    /**
     * @param Order[] $orders
     * @return BinaryFileResponseAlias
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function exportExcel($orders)
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $linha = 1;
        $sheet->setCellValue('A' . $linha, 'Vendas');
        $linha += 2;

        $sheet->setCellValue('A' . $linha, 'Cliente');
        $sheet->setCellValue('B' . $linha, 'Forma de Pagameto');
        $sheet->setCellValue('C' . $linha, 'Subtotal');
        $sheet->setCellValue('D' . $linha, 'Desconto');
        $sheet->setCellValue('E' . $linha, 'Total');
        $sheet->setCellValue('F' . $linha, 'Forma de Pagmento');
        $sheet->setCellValue('G' . $linha, 'Usuário');
        $sheet->setCellValue('H' . $linha, 'Data');
        $sheet->setCellValue('I' . $linha, 'Itens');

        foreach ($orders as $order) {
            $linha++;
            $sheet->setCellValue('A' . $linha, $order->getClient());
            $sheet->setCellValue('B' . $linha, $order->getPaymentMethodFormatted());
            $sheet->setCellValue('C' . $linha, $order->getSubtotal());
            $sheet->setCellValue('D' . $linha, $order->getDiscount());
            $sheet->setCellValue('E' . $linha, $order->getTotal());
            $sheet->setCellValue('F' . $linha, $order->getPaymentMethod());
            $sheet->setCellValue('G' . $linha, $order->getUser()->getFullName());
            $sheet->setCellValue('H' . $linha, $order->getCreatedAt()->format('d/m/Y'));

            $items = '';
            foreach ($order->getOrderItems() as $orderItem) {
                $items .= ' * ' . $orderItem->getQuantity() . 'x ' . $orderItem->getReferency() . '(' . $orderItem->getPrice() . ') = ' . $orderItem->getTotal();
            }
            $sheet->setCellValue('I' . $linha, $items);
        }

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'vendas.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $pagination = $this->pagination->handle($request, Order::class);

        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_order_new',
                'admin_order_edit',
                ['id' => $order->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_order_index');
        }

        return $this->render('admin/order/new.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @param Order $order
     * @param Request $request
     * @return Response
     */
    public function editAction(Order $order, Request $request)
    {
        $pagination = $this->pagination->handle($request, Order::class);

        $form = $this->createForm(OrderType::class, $order);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_order_new',
                'admin_order_edit',
                ['id' => $order->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_order_index', $pagination->getRouteParams());
        }

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }
}

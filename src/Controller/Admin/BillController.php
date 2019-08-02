<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Bill;
use App\Event\FlashBagEvents;
use App\Form\BillType;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BillController
 * @package App\Controller\Admin
 *
 * @Route("/bill", name="admin_bill_")
 */
class BillController extends BaseController
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
     * BillController constructor.
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
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if (!$request->query->has('date_start') && !$request->query->has('date_end')) {
            $request->query->add(
                [
                    'date_start' => date('01/m/Y'),
                    'date_end' => date('t/m/Y')
                ]
            );
        }

        $pagination = $this->pagination->handle($request, Bill::class);

        /** @var Bill[] $bills */
        $bills = $this->getDoctrine()->getRepository(Bill::class)->findLatest($pagination);

        $deleteForms = [];

        $totalReceive = 0;
        $totalReceiveOpen = 0;
        $totalReceivePaid = 0;
        $totalReceiveOverDue = 0;

        $totalPay = 0;
        $totalPayOpen = 0;
        $totalPayPaid = 0;
        $totalPayOverDue = 0;

        $billPlansPay = [];
        $billPlansReceive = [];

        foreach ($bills as $bill) {
            $deleteForms[$bill->getId()] = $this->createDeleteForm($bill)->createView();

            if ($bill->getType() == Bill::BILL_TYPE_RECEIVE) {
                $totalReceive += $bill->getAmount();
                if ($bill->getStatus() == Bill::BILL_STATUS_OPEN) {
                    $totalReceiveOpen += $bill->getAmount();
                }
                if ($bill->getStatus() == Bill::BILL_STATUS_PAID) {
                    $totalReceivePaid += $bill->getAmount();
                }
                if ($bill->isOverDue()) {
                    $totalReceiveOverDue += $bill->getAmount();
                }
                if(!key_exists($bill->getBillPlan()->getDescriptionWithType(), $billPlansReceive)){
                    $billPlansReceive[$bill->getBillPlan()->getDescriptionWithType()] = 0;
                }
                $billPlansReceive[$bill->getBillPlan()->getDescriptionWithType()] += $bill->getAmount();
            }

            if ($bill->getType() == Bill::BILL_TYPE_PAY) {
                $totalPay += $bill->getAmount();
                if ($bill->getStatus() == Bill::BILL_STATUS_OPEN) {
                    $totalPayOpen += $bill->getAmount();
                }
                if ($bill->getStatus() == Bill::BILL_STATUS_PAID) {
                    $totalPayPaid += $bill->getAmount();
                }
                if ($bill->isOverDue()) {
                    $totalPayOverDue += $bill->getAmount();
                }
                if(!key_exists($bill->getBillPlan()->getDescriptionWithType(), $billPlansPay)){
                    $billPlansPay[$bill->getBillPlan()->getDescriptionWithType()] = 0;
                }
                $billPlansPay[$bill->getBillPlan()->getDescriptionWithType()] += $bill->getAmount();
            }

        }

        $cfTotalToPayAndReceive = $totalReceiveOpen - $totalPayOpen;
        $cfTotalPaidAndReceived = $totalReceivePaid - $totalPayPaid;
        $cfTotal = $totalReceive - $totalPay;

        return $this->render('admin/bill/index.html.twig', [
            'bills' => $bills,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
            'totalReceive' => $totalReceive,
            'totalReceiveOpen' => $totalReceiveOpen,
            'totalReceivePaid' => $totalReceivePaid,
            'totalReceiveOverDue' => $totalReceiveOverDue,
            'totalPay' => $totalPay,
            'totalPayOpen' => $totalPayOpen,
            'totalPayPaid' => $totalPayPaid,
            'totalPayOverDue' => $totalPayOverDue,
            'cfTotalToPayAndReceive' => $cfTotalToPayAndReceive,
            'cfTotalPaidAndReceived' => $cfTotalPaidAndReceived,
            'cfTotal' => $cfTotal,
            'billPlansReceive' => $billPlansReceive,
            'billPlansPay' => $billPlansPay
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
        $pagination = $this->pagination->handle($request, Bill::class);

        $bill = new Bill();

        $form = $this->createForm(BillType::class, $bill);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setBillStatus($bill);

            $em = $this->getDoctrine()->getManager();
            $em->persist($bill);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_new',
                'admin_bill_edit',
                ['id' => $bill->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_index');
        }

        return $this->render('admin/bill/new.html.twig', [
            'bill' => $bill,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Bill $bill
     * @return Response
     */
    public function editAction(Bill $bill, Request $request)
    {
        $pagination = $this->pagination->handle($request, Bill::class);

        $form = $this->createForm(BillType::class, $bill);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->setBillStatus($bill);

            $em = $this->getDoctrine()->getManager();
            $em->persist($bill);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_new',
                'admin_bill_edit',
                ['id' => $bill->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_index', $pagination->getRouteParams());
        }

        return $this->render('admin/bill/edit.html.twig', [
            'bill' => $bill,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Bill $bill
     * @return Response
     */
    public function deletAction(Request $request, Bill $bill)
    {
        $pagination = $this->pagination->handle($request, Bill::class);

        $form = $this->createDeleteForm($bill);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($bill);
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

        return $this->redirectToRoute('admin_bill_index', $pagination->getRouteParams());
    }

    /**
     * @param Bill $bill
     * @return FormInterface
     */
    private function createDeleteForm(Bill $bill)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_bill_delete', ['id' => $bill->getId()]))
            ->setMethod('DELETE')
            ->setData($bill)
            ->getForm();
    }

    private function setBillStatus(Bill $bill)
    {
        $status = Bill::BILL_STATUS_PAID;
        if ($bill->getPaymentDate() === null && $bill->getAmountPaid() === null) {
            $status = Bill::BILL_STATUS_OPEN;
        }
        $bill->setStatus($status);
    }
}

<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\BillPlan;
use App\Event\FlashBagEvents;
use App\Form\BillPlanType;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BillPlanController
 * @package App\Controller\Admin
 *
 * @Route("/bill-plan", name="admin_bill_plan_")
 */
class BillPlanController extends BaseController
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
     * BillPlanController constructor.
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
        $pagination = $this->pagination->handle($request, BillPlan::class);

        /** @var BillPlan[] $billPlans */
        $billPlans = $this->getDoctrine()->getRepository(BillPlan::class)->findLatest($pagination);

        $deleteForms = [];

        foreach ($billPlans as $billPlan) {
            $deleteForms[$billPlan->getId()] = $this->createDeleteForm($billPlan)->createView();
        }

        return $this->render('admin/billPlan/index.html.twig', [
            'billPlans' => $billPlans,
            'pagination' => $pagination,
            'delete_forms' => $deleteForms,
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
        $pagination = $this->pagination->handle($request, BillPlan::class);

        $billPlan = new BillPlan();

        $form = $this->createForm(BillPlanType::class, $billPlan);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($billPlan);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_plan_new',
                'admin_bill_plan_edit',
                ['id' => $billPlan->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_plan_index');
        }

        return $this->render('admin/billPlan/new.html.twig', [
            'billPlan' => $billPlan,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param BillPlan $billPlan
     * @return Response
     */
    public function editAction(BillPlan $billPlan, Request $request)
    {
        $pagination = $this->pagination->handle($request, BillPlan::class);

        $form = $this->createForm(BillPlanType::class, $billPlan);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($billPlan);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_plan_new',
                'admin_bill_plan_edit',
                ['id' => $billPlan->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_plan_index', $pagination->getRouteParams());
        }

        return $this->render('admin/billPlan/edit.html.twig', [
            'billPlan' => $billPlan,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param BillPlan $billPlan
     * @return Response
     */
    public function deletAction(Request $request, BillPlan $billPlan)
    {
        $pagination = $this->pagination->handle($request, BillPlan::class);

        $form = $this->createDeleteForm($billPlan);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($billPlan);
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

        return $this->redirectToRoute('admin_bill_plan_index', $pagination->getRouteParams());
    }

    /**
     * @param BillPlan $billPlan
     * @return FormInterface
     */
    private function createDeleteForm(BillPlan $billPlan)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_bill_plan_delete', ['id' => $billPlan->getId()]))
            ->setMethod('DELETE')
            ->setData($billPlan)
            ->getForm();
    }

    /**
     * @Route("/{bill_type}/listFormJson/", requirements={"id" : "\d+"}, name="list_form_json", options={"expose"=true})
     * @Method("GET")
     */
    public function listFormJson(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['message' => 'You can access this only using Ajax!'], 400);
        }

        $result = $this->getDoctrine()->getRepository(BillPlan::class)->queryLatestForm($request->attributes->get('bill_type'));

        return $this->json($result->getQuery()->getArrayResult());
    }
}

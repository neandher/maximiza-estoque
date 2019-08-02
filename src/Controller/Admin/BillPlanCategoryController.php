<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\BillPlanCategory;
use App\Event\FlashBagEvents;
use App\Form\BillPlanCategoryType;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BillPlanCategoryController
 * @package App\Controller\Admin
 *
 * @Route("/bill-plan-category", name="admin_bill_plan_category_")
 */
class BillPlanCategoryController extends BaseController
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
     * BillPlanCategoryController constructor.
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
        $pagination = $this->pagination->handle($request, BillPlanCategory::class);

        /** @var BillPlanCategory[] $billPlanCategorys */
        $billPlanCategorys = $this->getDoctrine()->getRepository(BillPlanCategory::class)->findLatest($pagination);

        $deleteForms = [];

        foreach ($billPlanCategorys as $billPlanCategory) {
            $deleteForms[$billPlanCategory->getId()] = $this->createDeleteForm($billPlanCategory)->createView();
        }

        return $this->render('admin/billPlanCategory/index.html.twig', [
            'billPlanCategorys' => $billPlanCategorys,
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
        $pagination = $this->pagination->handle($request, BillPlanCategory::class);

        $billPlanCategory = new BillPlanCategory();

        $form = $this->createForm(BillPlanCategoryType::class, $billPlanCategory);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($billPlanCategory);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_plan_category_new',
                'admin_bill_plan_category_edit',
                ['id' => $billPlanCategory->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_plan_category_index');
        }

        return $this->render('admin/billPlanCategory/new.html.twig', [
            'billPlanCategory' => $billPlanCategory,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param BillPlanCategory $billPlanCategory
     * @return Response
     */
    public function editAction(BillPlanCategory $billPlanCategory, Request $request)
    {
        $pagination = $this->pagination->handle($request, BillPlanCategory::class);

        $form = $this->createForm(BillPlanCategoryType::class, $billPlanCategory);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($billPlanCategory);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_bill_plan_category_new',
                'admin_bill_plan_category_edit',
                ['id' => $billPlanCategory->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_bill_plan_category_index', $pagination->getRouteParams());
        }

        return $this->render('admin/billPlanCategory/edit.html.twig', [
            'billPlanCategory' => $billPlanCategory,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param BillPlanCategory $billPlanCategory
     * @return Response
     */
    public function deletAction(Request $request, BillPlanCategory $billPlanCategory)
    {
        $pagination = $this->pagination->handle($request, BillPlanCategory::class);

        $form = $this->createDeleteForm($billPlanCategory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($billPlanCategory);
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

        return $this->redirectToRoute('admin_bill_plan_category_index', $pagination->getRouteParams());
    }

    /**
     * @param BillPlanCategory $billPlanCategory
     * @return FormInterface
     */
    private function createDeleteForm(BillPlanCategory $billPlanCategory)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_bill_plan_category_delete', ['id' => $billPlanCategory->getId()]))
            ->setMethod('DELETE')
            ->setData($billPlanCategory)
            ->getForm();
    }
}

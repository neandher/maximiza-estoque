<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\CustomerCategory;
use App\Event\FlashBagEvents;
use App\Form\CustomerCategoryType;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomerCategoryController
 * @package App\Controller\Admin
 *
 * @Route("/customer-category", name="admin_customer_category_")
 */
class CustomerCategoryController extends BaseController
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
     * CustomerCategoryController constructor.
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
        $pagination = $this->pagination->handle($request, CustomerCategory::class);

        /** @var CustomerCategory[] $customerCategories */
        $customerCategories = $this->getDoctrine()->getRepository(CustomerCategory::class)->findLatest($pagination);

        $deleteForms = [];

        foreach ($customerCategories as $customerCategory) {
            $deleteForms[$customerCategory->getId()] = $this->createDeleteForm($customerCategory)->createView();
        }

        return $this->render('admin/customerCategory/index.html.twig', [
            'customerCategories' => $customerCategories,
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
        $pagination = $this->pagination->handle($request, CustomerCategory::class);

        $customerCategory = new CustomerCategory();

        $form = $this->createForm(CustomerCategoryType::class, $customerCategory);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($customerCategory);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_customer_category_new',
                'admin_customer_category_edit',
                ['id' => $customerCategory->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_customer_category_index');
        }

        return $this->render('admin/customerCategory/new.html.twig', [
            'customerCategory' => $customerCategory,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param CustomerCategory $customerCategory
     * @return Response
     */
    public function editAction(CustomerCategory $customerCategory, Request $request)
    {
        $pagination = $this->pagination->handle($request, CustomerCategory::class);

        $form = $this->createForm(CustomerCategoryType::class, $customerCategory);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($customerCategory);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_customer_category_new',
                'admin_customer_category_edit',
                ['id' => $customerCategory->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_customer_category_index', $pagination->getRouteParams());
        }

        return $this->render('admin/customerCategory/edit.html.twig', [
            'customerCategory' => $customerCategory,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param CustomerCategory $customerCategory
     * @return Response
     */
    public function deletAction(Request $request, CustomerCategory $customerCategory)
    {
        $pagination = $this->pagination->handle($request, CustomerCategory::class);

        $form = $this->createDeleteForm($customerCategory);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($customerCategory);
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

        return $this->redirectToRoute('admin_customer_category_index', $pagination->getRouteParams());
    }

    /**
     * @param CustomerCategory $customerCategory
     * @return FormInterface
     */
    private function createDeleteForm(CustomerCategory $customerCategory)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_customer_category_delete', ['id' => $customerCategory->getId()]))
            ->setMethod('DELETE')
            ->setData($customerCategory)
            ->getForm();
    }
}

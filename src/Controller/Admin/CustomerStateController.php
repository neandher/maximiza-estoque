<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\CustomerState;
use App\Entity\CustomerStateState;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Event\CustomerStateEvents;
use App\Form\CustomerStateImportXmlType;
use App\Form\CustomerStateMultipleType;
use App\Form\CustomerStateType;
use App\CustomerStateTypes;
use App\Util\FlashBag;
use App\Util\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomerStateController
 * @package App\Controller\Admin
 *
 * @Route("/customer-state", name="admin_customer_state_")
 */
class CustomerStateController extends BaseController
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
     * CustomerStateController constructor.
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
        $pagination = $this->pagination->handle($request, CustomerState::class);

        /** @var CustomerState[] $customerStates */
        $customerStates = $this->getDoctrine()->getRepository(CustomerState::class)->findLatest($pagination);

        $deleteForms = [];

        foreach ($customerStates as $customerState) {
            $deleteForms[$customerState->getId()] = $this->createDeleteForm($customerState)->createView();
        }

        return $this->render('admin/customerState/index.html.twig', [
            'customerStates' => $customerStates,
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
        $pagination = $this->pagination->handle($request, CustomerState::class);

        $customerState = new CustomerState();

        $form = $this->createForm(CustomerStateType::class, $customerState);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($customerState);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_customer_state_new',
                'admin_customer_state_edit',
                ['id' => $customerState->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_customer_state_index');
        }

        return $this->render('admin/customerState/new.html.twig', [
            'customerState' => $customerState,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param CustomerState $customerState
     * @return Response
     */
    public function editAction(CustomerState $customerState, Request $request)
    {
        $pagination = $this->pagination->handle($request, CustomerState::class);

        $form = $this->createForm(CustomerStateType::class, $customerState);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($customerState);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_customer_state_new',
                'admin_customer_state_edit',
                ['id' => $customerState->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_customer_state_index', $pagination->getRouteParams());
        }

        return $this->render('admin/customerState/edit.html.twig', [
            'customerState' => $customerState,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param CustomerState $customerState
     * @return Response
     */
    public function deletAction(Request $request, CustomerState $customerState)
    {
        $pagination = $this->pagination->handle($request, CustomerState::class);

        $form = $this->createDeleteForm($customerState);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($customerState);
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

        return $this->redirectToRoute('admin_customer_state_index', $pagination->getRouteParams());
    }

    /**
     * @param CustomerState $customerState
     * @return FormInterface
     */
    private function createDeleteForm(CustomerState $customerState)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_customer_state_delete', ['id' => $customerState->getId()]))
            ->setMethod('DELETE')
            ->setData($customerState)
            ->getForm();
    }
}

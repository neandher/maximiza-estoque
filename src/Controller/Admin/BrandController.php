<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Brand;
use App\Entity\BrandState;
use App\Entity\User;
use App\Event\FlashBagEvents;
use App\Event\BrandEvents;
use App\Form\BrandImportXmlType;
use App\Form\BrandMultipleType;
use App\Form\BrandType;
use App\BrandTypes;
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
 * Class BrandController
 * @package App\Controller\Admin
 *
 * @Route("/brand", name="admin_brand_")
 */
class BrandController extends BaseController
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
     * BrandController constructor.
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
        $pagination = $this->pagination->handle($request, Brand::class);

        /** @var Brand[] $brands */
        $brands = $this->getDoctrine()->getRepository(Brand::class)->findLatest($pagination);

        $deleteForms = [];

        foreach ($brands as $brand) {
            $deleteForms[$brand->getId()] = $this->createDeleteForm($brand)->createView();
        }

        return $this->render('admin/brand/index.html.twig', [
            'brands' => $brands,
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
        $pagination = $this->pagination->handle($request, Brand::class);

        $brand = new Brand();

        $form = $this->createForm(BrandType::class, $brand);
        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($brand);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_INSERTED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_brand_new',
                'admin_brand_edit',
                ['id' => $brand->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_brand_index');
        }

        return $this->render('admin/brand/new.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/edit", requirements={"id" : "\d+"}, name="edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Brand $brand
     * @return Response
     */
    public function editAction(Brand $brand, Request $request)
    {
        $pagination = $this->pagination->handle($request, Brand::class);

        $form = $this->createForm(BrandType::class, $brand);

        $this->addDefaultSubmitButtons($form);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($brand);
            $em->flush();

            $this->flashBag->newMessage(
                FlashBagEvents::MESSAGE_TYPE_SUCCESS,
                FlashBagEvents::MESSAGE_SUCCESS_UPDATED
            );

            $handleSubmitButtons = $this->handleSubmitButtons(
                $form,
                'admin_brand_new',
                'admin_brand_edit',
                ['id' => $brand->getId()],
                $pagination->getRouteParams()
            );

            return $handleSubmitButtons ? $handleSubmitButtons : $this->redirectToRoute('admin_brand_index', $pagination->getRouteParams());
        }

        return $this->render('admin/brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id" : "\d+"}, name="delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Brand $brand
     * @return Response
     */
    public function deletAction(Request $request, Brand $brand)
    {
        $pagination = $this->pagination->handle($request, Brand::class);

        $form = $this->createDeleteForm($brand);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->remove($brand);
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

        return $this->redirectToRoute('admin_brand_index', $pagination->getRouteParams());
    }

    /**
     * @param Brand $brand
     * @return FormInterface
     */
    private function createDeleteForm(Brand $brand)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_brand_delete', ['id' => $brand->getId()]))
            ->setMethod('DELETE')
            ->setData($brand)
            ->getForm();
    }
}

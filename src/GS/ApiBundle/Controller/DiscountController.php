<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\Discount;
use GS\ApiBundle\Form\Type\DiscountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DiscountController extends Controller
{

    /**
     * @Route("/discount/add/{id}", name="add_discount", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function postAction(Activity $activity, Request $request)
    {
        $discount = new Discount();
        $discount->setActivity($activity);
        $form = $this->createForm(DiscountType::class, $discount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity->addDiscount($discount);

            $em = $this->getDoctrine()->getManager();
            $em->persist($discount);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Réduction bien enregistrée.');

            return $this->redirectToRoute('view_discount', array('id' => $discount->getId()));
        }

        return $this->render('GSApiBundle:Discount:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/discount/{id}", name="view_discount", requirements={"id": "\d+"})
     * @Security("is_granted('view', discount)")
     */
    public function viewAction(Discount $discount)
    {
        return $this->render('GSApiBundle:Discount:view.html.twig', array(
                    'discount' => $discount
        ));
    }

    /**
     * @Route("/discount", name="index_discount")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function indexAction()
    {
        $listDiscounts = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Discount')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Discount:index.html.twig', array(
                    'listDiscounts' => $listDiscounts
        ));
    }

    /**
     * @Route("/discount/{id}/edit", name="edit_discount", requirements={"id": "\d+"})
     * @Security("is_granted('edit', discount)")
     */
    public function editAction(Discount $discount, Request $request)
    {
        $form = $this->createForm(DiscountType::class, $discount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Réduction bien modifiée.');

            return $this->redirectToRoute('view_discount', array('id' => $discount->getId()));
        }

        return $this->render('GSApiBundle:Discount:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/discount/{id}/delete", name="delete_discount", requirements={"id": "\d+"})
     * @Security("is_granted('delete', discount)")
     */
    public function deleteAction(Discount $discount, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $discount->getActivity();
            $activity->removeDiscount($discount);

            $em = $this->getDoctrine()->getManager();
            $em->remove($discount);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "La réduction a bien été supprimée.");

            return $this->redirectToRoute('view_activity', array('id' => $activity->getId()));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Discount:delete.html.twig', array(
                    'discount' => $discount,
                    'form' => $form->createView()
        ));
    }

}

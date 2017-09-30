<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Venue;
use GS\ApiBundle\Form\Type\VenueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VenueController extends Controller
{

    /**
     * @Route("/venue/add", name="add_venue")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function addAction(Request $request)
    {
        $venue = new Venue();
        $form = $this->createForm(VenueType::class, $venue);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($venue);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Salle bien enregistrée.');

            return $this->redirectToRoute('index_venue');
        }

        return $this->render('GSApiBundle:Venue:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/venue/{id}/delete", name="delete_venue", requirements={"id": "\d+"})
     * @Security("is_granted('delete', venue)")
     */
    public function deleteAction(Venue $venue, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($venue);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "La salle a bien été supprimée.");

            return $this->redirectToRoute('homepage');
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Venue:delete.html.twig', array(
                    'venue' => $venue,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/venue/{id}", name="view_venue", requirements={"id": "\d+"})
     * @Security("is_granted('view', venue)")
     */
    public function viewAction(Venue $venue)
    {
        return $this->render('GSApiBundle:Venue:view.html.twig', array(
                    'venue' => $venue
        ));
    }

    /**
     * @Route("/venue", name="index_venue")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function indexAction()
    {
        $listVenues = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Venue')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Venue:index.html.twig', array(
                    'listVenues' => $listVenues
        ));
    }

    /**
     * @Route("/venue/{id}/edit", name="edit_venue", requirements={"id": "\d+"})
     * @Security("is_granted('edit', venue)")
     */
    public function editAction(Venue $venue, Request $request)
    {
        $form = $this->createForm(VenueType::class, $venue);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Salle bien modifiée.');

            return $this->redirectToRoute('view_venue', array('id' => $venue->getId()));
        }

        return $this->render('GSApiBundle:Venue:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

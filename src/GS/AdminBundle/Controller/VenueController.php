<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Venue;
use GS\StructureBundle\Form\Type\VenueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VenueController extends Controller
{

    /**
     * @Route("/venue/add", name="gsadmin_add_venue")
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

            return $this->redirectToRoute('gsadmin_index_venue');
        }

        return $this->render('GSAdminBundle:Venue:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/venue/{id}/delete", name="gsadmin_delete_venue", requirements={"id": "\d+"})
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
        return $this->render('GSAdminBundle:Venue:delete.html.twig', array(
                    'venue' => $venue,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/venue/{id}", name="gsadmin_view_venue", requirements={"id": "\d+"})
     * @Security("is_granted('view', venue)")
     */
    public function viewAction(Venue $venue)
    {
        return $this->render('GSAdminBundle:Venue:view.html.twig', array(
                    'venue' => $venue
        ));
    }

    /**
     * @Route("/venue", name="gsadmin_index_venue")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function indexAction()
    {
        $listVenues = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Venue')
            ->findAll()
            ;

        return $this->render('GSAdminBundle:Venue:index.html.twig', array(
                    'listVenues' => $listVenues
        ));
    }

    /**
     * @Route("/venue/{id}/edit", name="gsadmin_edit_venue", requirements={"id": "\d+"})
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

            return $this->redirectToRoute('gsadmin_view_venue', array('id' => $venue->getId()));
        }

        return $this->render('GSAdminBundle:Venue:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

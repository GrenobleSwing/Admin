<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Society;
use GS\ApiBundle\Entity\Year;
use GS\ApiBundle\Form\Type\YearType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class YearController extends Controller
{
    /**
     * @Route("/year/{id}/open", name="open_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function openAction(Year $year, Request $request)
    {
        if ('DRAFT' != $year->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open year");
            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year->setState('OPEN');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été ouverte.");

            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        return $this->render('GSApiBundle:Year:open.html.twig', array(
            'year' => $year,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/{id}/close", name="close_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function closeAction(Year $year, Request $request)
    {
        if ('OPEN' != $year->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to close year");
            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year->setState('CLOSE');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été fermée.");

            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        return $this->render('GSApiBundle:Year:close.html.twig', array(
            'year' => $year,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/add/{id}", name="add_year")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Society $society, Request $request)
    {
        $year = new Year();
        $year->setSociety($society);
        $form = $this->createForm(YearType::class, $year);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$year->getOwners()->contains($this->getUser())) {
                $year->addOwner($this->getUser());
            }
            $society->addYear($year);

            $em = $this->getDoctrine()->getManager();
            $em->persist($year);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Année bien enregistrée.');

            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        return $this->render('GSApiBundle:Year:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/year/{id}/delete", name="delete_year", requirements={"id": "\d+"})
     * @Security("is_granted('delete', year)")
     */
    public function deleteAction(Year $year, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($year);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été supprimé.");

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Year:delete.html.twig', array(
                    'year' => $year,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/{id}", name="view_year", requirements={"id": "\d+"})
     * @Security("is_granted('view', year)")
     */
    public function viewAction(Year $year)
    {
        return $this->render('GSApiBundle:Year:view.html.twig', array(
                    'year' => $year
        ));
    }

    /**
     * @Route("/year", name="index_year")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $listYears = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Year')
            ->findBy(array(), array('startDate' => 'ASC'))
            ;

        return $this->render('GSApiBundle:Year:index.html.twig', array(
                    'listYears' => $listYears
        ));
    }

    /**
     * @Route("/year/{id}/edit", name="edit_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function editAction(Year $year, Request $request)
    {
        $form = $this->createForm(YearType::class, $year);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Année bien modifiée.');

            return $this->redirectToRoute('view_year', array('id' => $year->getId()));
        }

        return $this->render('GSApiBundle:Year:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

//    /**
//     * @Security("has_role('ROLE_SECRETARY')")
//     */
//    public function getMembersAction(Year $year)
//    {
//        $members = $this->get('gsapi.user.membership')->getMembers($year);
//        $view = $this->view($members, 200);
//        return $this->handleView($view);
//    }

}

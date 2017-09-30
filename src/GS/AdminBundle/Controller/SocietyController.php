<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Society;
use GS\StructureBundle\Form\Type\SocietyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SocietyController extends Controller
{
    /**
     * @Route("/society/add", name="gsadmin_add_society")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $society = new Society();
        $form = $this->createForm(SocietyType::class, $society);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($society);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Société bien enregistrée.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('GSAdminBundle:Society:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/society", name="gsadmin_view_society")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction()
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Society')
            ->findAll()
            ;
        $society = $societies[0];
        return $this->render('GSAdminBundle:Society:view.html.twig', array(
                    'society' => $society
        ));
    }

    /**
     * @Route("/society/edit", name="gsadmin_edit_society")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request)
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Society')
            ->findAll()
            ;
        $society = $societies[0];

        $form = $this->createForm(SocietyType::class, $society);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Société bien modifiée.');

            return $this->redirectToRoute('gsadmin_view_society');
        }

        return $this->render('GSAdminBundle:Society:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

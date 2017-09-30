<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Society;
use GS\ApiBundle\Form\Type\SocietyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SocietyController extends Controller
{
    /**
     * @Route("/society/add", name="add_society")
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

        return $this->render('GSApiBundle:Society:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/society", name="view_society")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction()
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Society')
            ->findAll()
            ;
        $society = $societies[0];
        return $this->render('GSApiBundle:Society:view.html.twig', array(
                    'society' => $society
        ));
    }

    /**
     * @Route("/society/edit", name="edit_society")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request)
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Society')
            ->findAll()
            ;
        $society = $societies[0];

        $form = $this->createForm(SocietyType::class, $society);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Société bien modifiée.');

            return $this->redirectToRoute('view_society');
        }

        return $this->render('GSApiBundle:Society:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

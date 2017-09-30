<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Certificate;
use GS\ApiBundle\Form\Type\CertificateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends Controller
{

    /**
     * @Route("/certificate/add", name="add_certificate")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function addAction(Request $request)
    {
        $certificate = new Certificate();
        $form = $this->createForm(CertificateType::class, $certificate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($certificate);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Justificatif bien enregistré.');

            return $this->redirectToRoute('index_certificate');
        }

        return $this->render('GSApiBundle:Certificate:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/certificate/{id}/delete", name="delete_certificate", requirements={"id": "\d+"})
     * @Security("is_granted('delete', certificate)")
     */
    public function deleteAction(Certificate $certificate, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($certificate);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le justificatif a bien été supprimé.");

            return $this->redirectToRoute('homepage');
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Certificate:delete.html.twig', array(
            'certificate' => $certificate,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/certificate/{id}", name="view_certificate", requirements={"id": "\d+"})
     * @Security("is_granted('view', certificate)")
     */
    public function viewAction(Certificate $certificate)
    {
        return $this->render('GSApiBundle:Certificate:view.html.twig', array(
            'certificate' => $certificate
        ));
    }

    /**
     * @Route("/certificate", name="index_certificate")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexAction()
    {
        $listCertificates = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Certificate')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Certificate:index.html.twig', array(
            'listCertificates' => $listCertificates
        ));
    }

    /**
     * @Route("/certificate/{id}/edit", name="edit_certificate", requirements={"id": "\d+"})
     * @Security("is_granted('edit', certificate)")
     */
    public function editAction(Certificate $certificate, Request $request)
    {
        $form = $this->createForm(CertificateType::class, $certificate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Justificatif bien modifié.');

            return $this->redirectToRoute('view_certificate', array('id' => $certificate->getId()));
        }

        return $this->render('GSApiBundle:Certificate:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}

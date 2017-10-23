<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Certificate;
use GS\StructureBundle\Form\Type\CertificateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CertificateController extends Controller
{

    /**
     * @Route("/certificate/add", name="gsadmin_add_certificate")
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

            return $this->redirectToRoute('gsadmin_index_certificate');
        }

        return $this->render('GSAdminBundle:Certificate:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/certificate/{id}/delete",
     *     name="gsadmin_delete_certificate",
     *     requirements={"id": "\d+"},
     *     options = { "expose" = true }
     * )
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
        return $this->render('GSAdminBundle:Certificate:delete.html.twig', array(
            'certificate' => $certificate,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/certificate/{id}",
     *     name="gsadmin_view_certificate",
     *     requirements={"id": "\d+"},
     *     options = { "expose" = true }
     * )
     * @Security("is_granted('view', certificate)")
     */
    public function viewAction(Certificate $certificate)
    {
        return $this->render('GSAdminBundle:Certificate:view.html.twig', array(
            'certificate' => $certificate
        ));
    }

    /**
     * @Route("/certificate", name="gsadmin_index_certificate")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexAction()
    {
        return $this->render('GSAdminBundle:Certificate:index.html.twig');
    }

    /**
     * @Route("/certificate/json", name="gsadmin_index_json_certificate")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexJsonAction()
    {
        $listCertificates = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Certificate')
            ->findAll()
            ;

        $serializedEntity = $this->get('jms_serializer')->serialize($listCertificates, 'json');

        return JsonResponse::fromJsonString($serializedEntity);
    }

    /**
     * @Route("/certificate/{id}/edit",
     *     name="gsadmin_edit_certificate",
     *     requirements={"id": "\d+"},
     *     options = { "expose" = true }
     * )
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

            return $this->redirectToRoute('gsadmin_view_certificate', array('id' => $certificate->getId()));
        }

        return $this->render('GSAdminBundle:Certificate:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}

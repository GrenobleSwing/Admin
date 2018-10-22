<?php

namespace GS\ETransactionBundle\Controller;

use GS\ETransactionBundle\Entity\Config;
use GS\ETransactionBundle\Form\Type\ConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends Controller
{

    /**
     * @Route("/config/add", name="gs_etran_add_config")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        $config = new Config();
        $form = $this->createForm(ConfigType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($config);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Config bien enregistrée.');

            return $this->redirectToRoute('gs_etran_index_config');
        }

        return $this->render('GSETransactionBundle:Config:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/config/{id}/delete", name="gs_etran_delete_config", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Config $config, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($config);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "La config a bien été supprimée.");

            return $this->redirectToRoute('gs_etran_index_config');
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSETransactionBundle:Config:delete.html.twig', array(
                    'config' => $config,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/config/{id}", name="gs_etran_view_config", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewAction(Config $config)
    {
        return $this->render('GSETransactionBundle:Config:view.html.twig', array(
                    'config' => $config
        ));
    }

    /**
     * @Route("/config", name="gs_etran_index_config")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $listConfigs = $this->getDoctrine()->getManager()
            ->getRepository('GSETransactionBundle:Config')
            ->findAll()
            ;

        return $this->render('GSETransactionBundle:Config:index.html.twig', array(
                    'listConfigs' => $listConfigs
        ));
    }

    /**
     * @Route("/config/{id}/edit", name="gs_etran_edit_config", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Config $config, Request $request)
    {
        $form = $this->createForm(ConfigType::class, $config);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Config bien modifiée.');

            return $this->redirectToRoute('gs_etran_view_config', array('id' => $config->getId()));
        }

        return $this->render('GSETransactionBundle:Config:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

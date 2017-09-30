<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Invoice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{

    /**
     * @Route("/invoice/{id}", name="gsadmin_view_invoice", requirements={"id": "\d+"})
     * @Security("is_granted('view', invoice)")
     */
    public function viewAction(Invoice $invoice)
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Society')
            ->findAll()
            ;
        $html = $this->renderView('GSAdminBundle:Invoice:invoice.html.twig', array(
            'invoice' => $invoice,
            'society' => $societies[0],
        ));
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
            )
        );
    }

    /**
     * @Route("/invoice", name="gsadmin_index_invoice")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function cgetAction()
    {
        $listInvoices = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Invoice')
            ->findAll()
            ;

        return $this->render('GSAdminBundle:Invoice:index.html.twig', array(
                    'listInvoices' => $listInvoices
        ));
    }

}

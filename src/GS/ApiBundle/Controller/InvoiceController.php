<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Invoice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{

    /**
     * @Route("/invoice/{id}", name="view_invoice", requirements={"id": "\d+"})
     * @Security("is_granted('view', invoice)")
     */
    public function viewAction(Invoice $invoice)
    {
        $societies = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Society')
            ->findAll()
            ;
        $html = $this->renderView('GSApiBundle:Invoice:invoice.html.twig', array(
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
     * @Route("/invoice", name="index_invoice")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function cgetAction()
    {
        $listInvoices = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Invoice')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Invoice:index.html.twig', array(
                    'listInvoices' => $listInvoices
        ));
    }

}

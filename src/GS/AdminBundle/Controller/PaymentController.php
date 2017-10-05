<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Invoice;
use GS\StructureBundle\Entity\Payment;
use GS\StructureBundle\Form\Type\PaymentType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{

    /**
     * @Route("/payment/add", name="gsadmin_add_payment")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function addAction(Request $request)
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($payment);

            $account = $payment->getAccount();
            $account->addPayment($payment);

            $repo = $em->getRepository('GSStructureBundle:Invoice');
            if ('PAID' == $payment->getState() &&
                    null === $repo->findOneByPayment($payment)) {
                $prefix = $payment->getDate()->format('Y');
                $invoiceNumber = $repo->countByNumber($prefix) + 1;
                $invoice = new Invoice($payment);
                $invoice->setNumber($prefix . sprintf('%05d', $invoiceNumber));
                $invoice->setDate($payment->getDate());

                $this->get('gstoolbox.payment.service')->sendEmail($payment);

                $em->persist($invoice);
            }
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Paiement bien enregistré.');

            return $this->redirectToRoute('gsadmin_view_payment', array('id' => $payment->getId()));
        }

        return $this->render('GSAdminBundle:Payment:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/payment/{id}/delete", name="gsadmin_delete_payment", requirements={"id": "\d+"})
     * @Security("is_granted('delete', payment)")
     */
    public function deleteAction(Payment $payment, Request $request)
    {
        if ('PAID' == $payment->getState()) {
            $view = $this->view(null, 403);
            return $this->handleView($view);
        }
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payment->getAccount()->removePayment($payment);
            $em = $this->getDoctrine()->getManager();
            $em->remove($payment);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le paiement a bien été supprimé.");

            return $this->redirectToRoute('homepage');
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSAdminBundle:Payment:delete.html.twig', array(
                    'payment' => $payment,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/payment/{id}",
     *   name="gsadmin_view_payment",
     *   requirements={"id": "\d+"},
     *   options = { "expose" = true })
     * @Security("is_granted('view', payment)")
     */
    public function viewAction(Payment $payment)
    {
        return $this->render('GSAdminBundle:Payment:view.html.twig', array(
            'payment' => $payment,
        ));
    }

    /**
     * @Route("/payment", name="gsadmin_index_payment")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexAction(Request $request)
    {
        $state = null;
        if ( $request->query->has('state') ) {
            $state = $request->query->get('state');
        }
        return $this->render('GSAdminBundle:Payment:index.html.twig', array(
            'state' => $state,
        ));
    }

    /**
     * @Route("/payment/json", name="gsadmin_index_payment_json")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexJsonAction(Request $request)
    {
        if ( $request->query->has('state') && null !== $request->query->get('state') ) {
            $listPayments = $this->getDoctrine()->getManager()
                ->getRepository('GSStructureBundle:Payment')
                ->findByState($request->query->get('state'))
                ;
        } else {
            $listPayments = $this->getDoctrine()->getManager()
                ->getRepository('GSStructureBundle:Payment')
                ->findAll()
                ;
        }
        $serializedEntity = $this->get('jms_serializer')->serialize($listPayments, 'json',
                SerializationContext::create()->setGroups(array('Default', 'account' => array('payment'))));

        return JsonResponse::fromJsonString($serializedEntity);
    }

    /**
     * @Route("/payment/{id}/edit", name="gsadmin_edit_payment", requirements={"id": "\d+"})
     * @Security("is_granted('edit', payment)")
     */
    public function editAction(Payment $payment, Request $request)
    {
        $form = $this->createForm(PaymentType::class, $payment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $invoice = $em->getRepository('GSStructureBundle:Invoice')
                ->findOneByPayment($payment);
            if ('PAID' == $payment->getState() && null === $invoice) {
                $prefix = $payment->getDate()->format('Y');
                $invoiceNumber = $em->getRepository('GSStructureBundle:Invoice')
                        ->countByNumber($prefix) + 1;
                $invoice = new Invoice($payment);
                $invoice->setNumber($prefix . sprintf('%05d', $invoiceNumber));
                $invoice->setDate($payment->getDate());

                $this->get('gstoolbox.payment.service')->sendEmail($payment);

                $em->persist($invoice);
            }
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Paiement bien modifié.');

            return $this->redirectToRoute('gsadmin_view_payment', array('id' => $payment->getId()));
        }

        return $this->render('GSAdminBundle:Payment:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Account;
use GS\ETransactionBundle\Entity\Payment;
use GS\StructureBundle\Form\Type\AccountType;
use GS\StructureBundle\Form\Type\AccountPictureType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("", name="gsadmin_")
 */
class AccountController extends Controller
{

    private function myAccount()
    {
        $account = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Account')
            ->findOneByUser($this->getUser())
            ;
        return $account;
    }

    /**
     * @Route("/my_account", name="gsadmin_my_account")
     * @Security("has_role('ROLE_USER')")
     */
    public function myAccountAction(Request $request)
    {
        $account = $this->myAccount();

        if ( null === $account ) {
            $request->getSession()->getFlashBag()->add('danger', "Le profil demandé n'existe pas.");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('GSAdminBundle:Account:view.html.twig', array(
            'account' => $account,
        ));
    }

    /**
     * @Route("/my_registrations", name="gsadmin_my_registrations")
     * @Security("has_role('ROLE_USER')")
     */
    public function myRegistrationsAction(Request $request)
    {
        $account = $this->myAccount();

        if ( null === $account ) {
            $request->getSession()->getFlashBag()->add('danger', "Le profil demandé n'existe pas.");
            return $this->redirectToRoute('homepage');
        }

        $listRegistrations = $this->getRegistrations($account, $request);

        return $this->render('GSAdminBundle:Account:registrations.html.twig', array(
            'listRegistrations' => $listRegistrations,
        ));
    }

    /**
     * @Route("/my_payments", name="gsadmin_my_payments")
     * @Security("has_role('ROLE_USER')")
     */
    public function myPaymentsAction(Request $request)
    {
        $account = $this->myAccount();

        if ( null === $account ) {
            $request->getSession()->getFlashBag()->add('danger', "Le profil demandé n'existe pas.");
            return $this->redirectToRoute('homepage');
        }

        $listPayments = $this->getPayments($account, $request);

        return $this->render('GSAdminBundle:Account:payments.html.twig', array(
            'listPayments' => $listPayments,
        ));
    }

    /**
     * @Route("/account/{id}", name="gsadmin_view_account", requirements={"id": "\d+"})
     * @Security("is_granted('view', account)")
     */
    public function getAction(Account $account, Request $request)
    {
        $listRegistrations = $this->getRegistrations($account, $request);
        return $this->render('GSAdminBundle:Account:view.html.twig', array(
            'account' => $account,
            'listRegistrations' => $listRegistrations,
        ));
    }

    /**
     * @Route("/account", name="gsadmin_index_account")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function cgetAction()
    {
        $listAccounts = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Account')
            ->findAll()
            ;

        return $this->render('GSAdminBundle:Account:index.html.twig', array(
                    'listAccounts' => $listAccounts
        ));
    }

    /**
     * @Route("/account/{id}/edit", name="gsadmin_edit_account", requirements={"id": "\d+"})
     * @Security("is_granted('edit', account)")
     */
    public function putAction(Account $account, Request $request)
    {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Profil bien modifié.');

            return $this->redirectToRoute('gsadmin_view_account', array('id' => $account->getId()));
        }

        return $this->render('GSAdminBundle:Account:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/account/{id}/picture", name="gsadmin_edit_account_picture", requirements={"id": "\d+"})
     * @Security("is_granted('edit', account)")
     */
    public function putPictureAction(Account $account, Request $request)
    {
        $form = $this->createForm(AccountPictureType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Image du profil bien modifiée.');

            return $this->redirectToRoute('gsadmin_view_account', array('id' => $account->getId()));
        }

        return $this->render('GSAdminBundle:Account:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/my_balance", name="gsadmin_my_balance")
     * @Security("has_role('ROLE_USER')")
     */
    public function getMyBalanceAction(Request $request)
    {
        $account = $this->myAccount();
        $activityId = $request->query->get('activityId');
        if (null !== $activityId) {
            $em = $this->getDoctrine()->getManager();
            $activity = $em->getRepository('GSStructureBundle:Activity')->find($activityId);
        } else {
            $activity = null;
        }
        $balance = $this->get('gsadmin.account_balance')->getBalance($account, $activity);

        $payment = $balance['payment'];
        if ( null !== $payment) {
            $transaction = new Payment();
            $transaction->setCmd($payment->getRef());
            $transaction->setEnvironment(
                    $payment->getItems()[0]
                    ->getRegistration()
                    ->getTopic()
                    ->getActivity()
                    ->getYear()
                    ->getSociety()
                    ->getPaymentEnvironment());
            $transaction->setPorteur($account->getEmail());
            $transaction->setTotal((int)($payment->getAmount() * 100));
            $transaction->setUrlAnnule($this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL));
            $transaction->setUrlEffectue($this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL));
            $transaction->setUrlRefuse($this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL));
            $transaction->setIpnUrl($this->generateUrl('gse_transaction_ipn', array(), UrlGeneratorInterface::ABSOLUTE_URL));

            return $this->render('GSAdminBundle:Account:balance.html.twig', array(
                'transaction' => $transaction,
                'payment' => $payment,
            ));
        }

        return $this->redirectToRoute('gsadmin_my_account');
    }

    private function getRegistrations(Account $account, Request $request)
    {
        if ( $request->query->has('yearId') ) {
            $year = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:Year')
                    ->find($request->query->get('yearId'));
            $registrations = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:Registration')
                    ->getRegistrationsForAccountAndYear($account, $year);
        } else {
            $registrations = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:Registration')
                    ->findBy(array('account' => $account));
        }

        return $registrations;
    }

    private function getPayments(Account $account, Request $request)
    {
        $payments = $this->getDoctrine()->getManager()
                ->getRepository('GSStructureBundle:Payment')
                ->findBy(array('account' => $account));

        return $payments;
    }

}

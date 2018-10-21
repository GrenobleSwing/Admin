<?php

namespace GS\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("", name="gsadmin_")
 */
class CheckController extends Controller
{

    /**
     * @Route("/check/membership", name="check_membership")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function memberAction(Request $request)
    {
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('file', FileType::class, array(
                'label' => 'Fichier contenant les emails des personnes à vérifier (une adresse email par ligne)',
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Vérifier',
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year = $this->getDoctrine()->getManager()
                ->getRepository('GSStructureBundle:Year')
                ->findCurrentYear()
                ;
            $membershipService = $this->get('gstoolbox.user.membership');

            $data = $form->getData();
            $file = $data['file'];
            $handle = fopen($file->getPathname(), "r");
            $listNotMember = [];
            while (($line = fgets($handle)) !== false) {
                $email = trim($line);

                $account = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:Account')
                    ->findOneByEmail($email)
                    ;

                if ($account === null || !$membershipService->isMember($account, $year)) {
                    $listNotMember[] = $email;
                }
            }
            fclose($handle);

            return $this->render('GSAdminBundle:Check:view_member.html.twig', array(
                'listNotMember' => $listNotMember,
            ));
        }

        return $this->render('GSAdminBundle:Check:membership.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/check/payment", name="check_payment")
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function paymentAction(Request $request)
    {
        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('file', FileType::class, array(
                'label' => 'Fichier contenant les références des paiements à vérifier (une référence par ligne)',
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Vérifier',
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $data['file'];
            $handle = fopen($file->getPathname(), "r");
            $missingPayments = [];
            while (($line = fgets($handle)) !== false) {
                $ref = trim($line);

                $payment = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:Payment')
                    ->findOneByRef($ref)
                    ;

                if ($payment === null) {
                    $missingPayments[] = $ref;
                }
            }
            fclose($handle);

            return $this->render('GSAdminBundle:Check:view_payment.html.twig', array(
                'listRef' => $missingPayments,
            ));
        }

        return $this->render('GSAdminBundle:Check:payment.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}

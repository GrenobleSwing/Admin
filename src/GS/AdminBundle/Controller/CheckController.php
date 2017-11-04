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
     * @Route("/check/membership", name="gsadmin_check_membership")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function addAction(Request $request)
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

            return $this->render('GSAdminBundle:Check:view.html.twig', array(
                'listNotMember' => $listNotMember,
            ));
        }

        return $this->render('GSAdminBundle:Check:membership.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}

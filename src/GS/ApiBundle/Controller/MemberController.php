<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Year;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * @Route("/member/{id}", name="index_member")
     * @Security("has_role('ROLE_SECRETARY')")
     */
    public function indexAction(Year $year, Request $request)
    {
        $onlyPaid = true;
        if ($request->query->has('all') && $request->query->get('all')) {
            $onlyPaid = false;
        }
        return $this->render('GSApiBundle:Member:index.html.twig', array(
            'year' => $year,
            'onlyPaid' => $onlyPaid
        ));
    }

    /**
     * @Route("/member/{id}/json", name="index_member_json")
     * @Security("has_role('ROLE_SECRETARY')")
     */
    public function indexJsonAction(Year $year, Request $request)
    {
        $onlyPaid = false;
        if ($request->query->has('onlyPaid') && $request->query->get('onlyPaid')) {
            $onlyPaid = true;
        }

        $listAccounts = $this->get('gsapi.user.membership')->getMembers($year, $onlyPaid);

        $serializedEntity = $this->get('jms_serializer')->serialize($listAccounts, 'json');

        return new Response($serializedEntity);
    }

}

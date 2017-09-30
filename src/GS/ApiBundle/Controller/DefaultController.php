<?php

namespace GS\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage", options = { "expose" = true })
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PRIVILEGED')) {
            return $this->render('GSApiBundle:Default:rejected.html.twig');
        }

        $listOpenTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Topic')
            ->getOpenTopics()
            ;

        $listYears = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Year')
            ->getYearsForUsers($this->getUser())
            ;

        $listActivities = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Activity')
            ->getActivitiesForUsers($this->getUser())
            ;

        $listTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Topic')
            ->getTopicsForUsers($this->getUser())
            ;

        return $this->render('GSApiBundle:Default:index.html.twig', array(
            'listOpenTopics' => $listOpenTopics,
            'listYears' => $listYears,
            'listActivities' => $listActivities,
            'listTopics' => $listTopics
        ));
    }

}

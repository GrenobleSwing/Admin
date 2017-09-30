<?php

namespace GS\AdminBundle\Controller;

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
            return $this->render('GSAdminBundle:Default:rejected.html.twig');
        }

        $listOpenTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Topic')
            ->getOpenTopics()
            ;

        $listYears = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Year')
            ->getYearsForUsers($this->getUser())
            ;

        $listActivities = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Activity')
            ->getActivitiesForUsers($this->getUser())
            ;

        $listTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Topic')
            ->getTopicsForUsers($this->getUser())
            ;

        return $this->render('GSAdminBundle:Default:index.html.twig', array(
            'listOpenTopics' => $listOpenTopics,
            'listYears' => $listYears,
            'listActivities' => $listActivities,
            'listTopics' => $listTopics
        ));
    }

}

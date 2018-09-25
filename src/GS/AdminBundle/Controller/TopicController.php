<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Activity;
use GS\StructureBundle\Entity\Topic;
use GS\StructureBundle\Form\Type\TopicType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TopicController extends Controller
{
    /**
     * @Route("/topic/{id}/open", name="gsadmin_open_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function openAction(Topic $topic, Request $request)
    {
        if ('DRAFT' != $topic->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open topic: topic is not a draft.");
            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        if ('OPEN' != $topic->getActivity()->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open topic: activity is not open.");
            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $topic->getActivity()->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setState('OPEN');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le cours/niveau a bien été ouverte.");

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSAdminBundle:Topic:open.html.twig', array(
            'topic' => $topic,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/topic/{id}/close", name="gsadmin_close_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function closeAction(Topic $topic, Request $request)
    {
        if ('OPEN' != $topic->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to close topic");
            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic->close();
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le cours/niveau a bien été fermée.");

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSAdminBundle:Topic:close.html.twig', array(
            'topic' => $topic,
            'form' => $form->createView()
        ));
    }

    private function checkDates(Topic $topic)
    {
        foreach ($topic->getSchedules() as $schedule) {
            if ($topic->getActivity()->getYear()->getStartDate() > $schedule->getStartDate() ||
                    $topic->getActivity()->getYear()->getEndDate() < $schedule->getEndDate()) {
                return true;
            }
        }
        return false;
    }
    /**
     * @Route("/topic/add/{id}", name="gsadmin_add_topic", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function addAction(Activity $activity, Request $request)
    {
        $topic = new Topic();
        $topic->setActivity($activity);
        $form = $this->createForm(TopicType::class, $topic);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->checkDates($topic)) {
                $request->getSession()->getFlashBag()->add('danger', 'Les dates ne sont pas bonnes pour le cours/niveau.');
                return $this->render('GSAdminBundle:Topic:add.html.twig', array(
                            'form' => $form->createView(),
                ));
            }
            if (!$topic->getOwners()->contains($this->getUser())) {
                $topic->addOwner($this->getUser());
            }
            $activity->addTopic($topic);

            $em = $this->getDoctrine()->getManager();
            $em->persist($topic);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Cours/niveau bien enregistré.');

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSAdminBundle:Topic:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/topic/{id}/delete", name="gsadmin_delete_topic", requirements={"id": "\d+"})
     * @Security("is_granted('delete', topic)")
     */
    public function deleteAction(Topic $topic, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $topic->getActivity();
            $activity->removeTopic($topic);

            $em = $this->getDoctrine()->getManager();
            $em->remove($topic);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le cours/niveau a bien été supprimé.");

            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSAdminBundle:Topic:delete.html.twig', array(
                    'topic' => $topic,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/topic/{id}", name="gsadmin_view_topic", requirements={"id": "\d+"})
     * @Security("is_granted('view', topic)")
     */
    public function viewAction(Topic $topic, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em
            ->getRepository('GSStructureBundle:Account')
            ->findOneByUser($this->getUser())
            ;

        $count = array();
        $count['leader']['paid'] = $em
            ->getRepository('GSStructureBundle:Registration')
            ->countRegistrationsForTopicAndStateAndRole($topic, 'PAID', 'leader');

        $count['leader']['payment_in_progress'] = $em
            ->getRepository('GSStructureBundle:Registration')
            ->countRegistrationsForTopicAndStateAndRole($topic, 'PAYMENT_IN_PROGRESS', 'leader');

        $count['leader']['validated'] = $em
            ->getRepository('GSStructureBundle:Registration')
            ->countRegistrationsForTopicAndStateAndRole($topic, 'VALIDATED', 'leader');

        $count['leader']['waiting'] = $em
            ->getRepository('GSStructureBundle:Registration')
            ->countRegistrationsForTopicAndStateAndRole($topic, 'WAITING', 'leader');

        if ($topic->getType() == 'couple') {
            $count['follower']['paid'] = $em
                ->getRepository('GSStructureBundle:Registration')
                ->countRegistrationsForTopicAndStateAndRole($topic, 'PAID', 'follower');

            $count['follower']['payment_in_progress'] = $em
                ->getRepository('GSStructureBundle:Registration')
                ->countRegistrationsForTopicAndStateAndRole($topic, 'PAYMENT_IN_PROGRESS', 'follower');

            $count['follower']['validated'] = $em
                ->getRepository('GSStructureBundle:Registration')
                ->countRegistrationsForTopicAndStateAndRole($topic, 'VALIDATED', 'follower');

            $count['follower']['waiting'] = $em
                ->getRepository('GSStructureBundle:Registration')
                ->countRegistrationsForTopicAndStateAndRole($topic, 'WAITING', 'follower');
        }

        $registrations = $em
            ->getRepository('GSStructureBundle:Registration')
            ->getRegistrationsForAccountAndTopic($account, $topic);

        // TODO: Just look if there is a result (which means the user is registered for this topic).
        $topics = [];
        foreach ($registrations as $registration) {
            $topics[] = $registration->getTopic();
        }

        return $this->render('GSAdminBundle:Topic:view.html.twig', array(
            'topic' => $topic,
            'user_registrations' => $registrations,
            'user_topics' => $topics,
            'count' => $count,
        ));
    }

    /**
     * @Route("/topic", name="gsadmin_index_topic")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $listTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Topic')
            ->findAll()
            ;

        return $this->render('GSAdminBundle:Topic:index.html.twig', array(
                    'listTopics' => $listTopics
        ));
    }

    /**
     * @Route("/topic/{id}/edit", name="gsadmin_edit_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function editAction(Topic $topic, Request $request)
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->remove('activity');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->checkDates($topic)) {
                $request->getSession()->getFlashBag()->add('danger', 'Les dates ne sont pas bonnes pour le cours/niveau.');
                return $this->render('GSAdminBundle:Topic:edit.html.twig', array(
                            'form' => $form->createView(),
                ));
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Cours/niveau bien modifié.');

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSAdminBundle:Topic:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

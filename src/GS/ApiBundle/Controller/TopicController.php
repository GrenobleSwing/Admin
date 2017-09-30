<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\Topic;
use GS\ApiBundle\Form\Type\TopicType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TopicController extends Controller
{
    /**
     * @Route("/topic/{id}/open", name="open_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function openAction(Topic $topic, Request $request)
    {
        if ('DRAFT' != $topic->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open topic: topic is not a draft.");
            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        if ('OPEN' != $topic->getActivity()->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open topic: activity is not open.");
            return $this->redirectToRoute('view_activity', array('id' => $topic->getActivity()->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setState('OPEN');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le cours/niveau a bien été ouverte.");

            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSApiBundle:Topic:open.html.twig', array(
            'topic' => $topic,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/topic/{id}/close", name="close_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function closeAction(Topic $topic, Request $request)
    {
        if ('OPEN' != $topic->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to close topic");
            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setState('CLOSE');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "Le cours/niveau a bien été fermée.");

            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSApiBundle:Topic:close.html.twig', array(
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
     * @Route("/topic/add/{id}", name="add_topic", requirements={"id": "\d+"})
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
                return $this->render('GSApiBundle:Topic:add.html.twig', array(
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

            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSApiBundle:Topic:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/topic/{id}/delete", name="delete_topic", requirements={"id": "\d+"})
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

            return $this->redirectToRoute('view_activity', array('id' => $activity->getId()));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Topic:delete.html.twig', array(
                    'topic' => $topic,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/topic/{id}", name="view_topic", requirements={"id": "\d+"})
     * @Security("is_granted('view', topic)")
     */
    public function viewAction(Topic $topic, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em
            ->getRepository('GSApiBundle:Account')
            ->findOneByUser($this->getUser())
            ;

        $registrations = $em
            ->getRepository('GSApiBundle:Registration')
            ->getRegistrationsForAccountAndTopic($account, $topic);

        $topics = [];
        foreach ($registrations as $registration) {
            $topics[] = $registration->getTopic();
        }

        return $this->render('GSApiBundle:Topic:view.html.twig', array(
            'topic' => $topic,
            'user_registrations' => $registrations,
            'user_topics' => $topics,
        ));
    }

    /**
     * @Route("/topic", name="index_topic")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $listTopics = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Topic')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Topic:index.html.twig', array(
                    'listTopics' => $listTopics
        ));
    }

    /**
     * @Route("/topic/{id}/edit", name="edit_topic", requirements={"id": "\d+"})
     * @Security("is_granted('edit', topic)")
     */
    public function editAction(Topic $topic, Request $request)
    {
        $form = $this->createForm(TopicType::class, $topic);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->checkDates($topic)) {
                $request->getSession()->getFlashBag()->add('danger', 'Les dates ne sont pas bonnes pour le cours/niveau.');
                return $this->render('GSApiBundle:Topic:edit.html.twig', array(
                            'form' => $form->createView(),
                ));
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Cours/niveau bien modifié.');

            return $this->redirectToRoute('view_topic', array('id' => $topic->getId()));
        }

        return $this->render('GSApiBundle:Topic:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

//    /**
//     * @Security("is_granted('view', topic)")
//     */
//    public function getRegistrationsAction(Topic $topic)
//    {
//        $registrations = $this->getDoctrine()->getManager()
//                ->getRepository('GSApiBundle:Registration')
//                ->findBy(array('topic' => $topic))
//                ;
//
//        $view = $this->view($registrations, 200);
//        return $this->handleView($view);
//    }
//
//    /**
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function newRegistrationAction(Topic $topic)
//    {
//        $registration = new Registration();
//        $registration->setTopic($topic);
//        $this->denyAccessUnlessGranted('create', $registration);
//        $registrationService = $this->get('gsapi.registration.service');
//        $missingRequirements = $registrationService->checkRequirements($registration, $this->getUser());
//        if (count($missingRequirements) > 0) {
//            $view = $this->view($missingRequirements, 412);
//        }
//        else
//        {
//            $form = $this->get('gsapi.form_generator')->getRegistrationForm($registration, 'post_registration');
//            $view = $this->get('gsapi.form_generator')->getFormView($form);
//        }
//        return $this->handleView($view);
//    }

}

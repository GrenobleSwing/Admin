<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Activity;
use GS\StructureBundle\Entity\ActivityEmail;
use GS\StructureBundle\Entity\Registration;
use GS\StructureBundle\Entity\Year;
use GS\StructureBundle\Form\Type\ActivityType;
use Lexik\Bundle\MailerBundle\Entity\Email;
use Lexik\Bundle\MailerBundle\Entity\EmailTranslation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("", name="gsadmin_")
 */
class ActivityController extends Controller
{
    /**
     * @Route("/activity/{id}/open", name="gsadmin_open_activity", requirements={"id": "\d+"})
     * @Security("is_granted('edit', activity)")
     */
    public function openAction(Activity $activity, Request $request)
    {
        if ('DRAFT' != $activity->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open activity: activity is not a draft.");
            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        if ('OPEN' != $activity->getYear()->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open activity: year is not open.");
            return $this->redirectToRoute('gsadmin_view_year', array('id' => $activity->getYear()->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setState('OPEN');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'activité a bien été ouverte.");

            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        return $this->render('GSAdminBundle:Activity:open.html.twig', array(
            'activity' => $activity,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/activity/{id}/close", name="gsadmin_close_activity", requirements={"id": "\d+"})
     * @Security("is_granted('edit', activity)")
     */
    public function closeAction(Activity $activity, Request $request)
    {
        if ('OPEN' != $activity->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to close activity");
            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setState('CLOSE');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'activité a bien été fermée.");

            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        return $this->render('GSAdminBundle:Activity:close.html.twig', array(
            'activity' => $activity,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/activity/add/{id}", name="gsadmin_add_activity", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function addAction(Year $year, Request $request)
    {
        $activity = new Activity();
        $activity->setYear($year);
        $form = $this->createForm(ActivityType::class, $activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$activity->getOwners()->contains($this->getUser())) {
                $activity->addOwner($this->getUser());
            }
            $year->addActivity($activity);

            $layout = $activity->getEmailLayout();
            $emailTranslations = array(
                array(
                    'locale' => 'fr',
                    'subject' => '[Grenoble Swing] Inscription',
                    'body' => 'Mettre votre texte ici.',
                    'from_address' => 'info@grenobleswing.com',
                    'from_name' => 'Grenoble Swing',
                ),
                array(
                    'locale' => 'en',
                    'subject' => '[Grenoble Swing] Registration',
                    'body' => 'Put your text here.',
                    'from_address' => 'info@grenobleswing.com',
                    'from_name' => 'Grenoble Swing',
                ),
            );

            foreach (array(Registration::CREATE, Registration::WAIT,
                Registration::VALIDATE, Registration::CANCEL) as
                    $action) {
                $email = new Email();
                $email->setDescription($action);
                $email->setReference(uniqid('template_'));
                $email->setSpool(false);
                $email->setLayout($layout);
                $email->setUseFallbackLocale(true);
                foreach ($emailTranslations as $trans) {
                    $emailTranslation = new EmailTranslation();
                    $emailTranslation->setLang($trans['locale']);
                    $emailTranslation->setSubject($trans['subject']);
                    $emailTranslation->setBody($trans['body']);
                    $emailTranslation->setFromAddress($trans['from_address']);
                    $emailTranslation->setFromName($trans['from_name']);
                    $email->addTranslation($emailTranslation);
                }
                $activityEmail = new ActivityEmail();
                $activityEmail->setAction($action);
                $activityEmail->setEmailTemplate($email);

                $activity->addEmailTemplate($activityEmail);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Activité bien enregistrée.');

            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        return $this->render('GSAdminBundle:Activity:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/activity/{id}/delete", name="gsadmin_delete_activity", requirements={"id": "\d+"})
     * @Security("is_granted('delete', activity)")
     */
    public function deleteAction(Activity $activity, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year = $activity->getYear();
            $year->removeActivity($activity);

            $em = $this->getDoctrine()->getManager();
            $em->remove($activity);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'activité a bien été supprimée.");

            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSAdminBundle:Activity:delete.html.twig', array(
                    'activity' => $activity,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/activity/{id}", name="gsadmin_view_activity", requirements={"id": "\d+"})
     * @Security("is_granted('view', activity)")
     */
    public function viewAction(Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em
            ->getRepository('GSStructureBundle:Account')
            ->findOneByUser($this->getUser())
            ;

        $registrations = $em
            ->getRepository('GSStructureBundle:Registration')
            ->getRegistrationsNotCancelledForAccountAndActivity($account, $activity);

        $topics = [];
        foreach ($registrations as $registration) {
            $topics[] = $registration->getTopic();
        }

        return $this->render('GSAdminBundle:Activity:view.html.twig', array(
            'activity' => $activity,
            'user_topics' => $topics,
        ));
    }

    /**
     * @Route("/activity", name="gsadmin_index_activity")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $listActivities = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Activity')
            ->findAll()
            ;

        return $this->render('GSAdminBundle:Activity:index.html.twig', array(
                    'listActivities' => $listActivities
        ));
    }

    /**
     * @Route("/activity/{id}/edit", name="gsadmin_edit_activity", requirements={"id": "\d+"})
     * @Security("is_granted('edit', activity)")
     */
    public function editAction(Activity $activity, Request $request)
    {
        $form = $this->createForm(ActivityType::class, $activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Activité bien modifiée.');

            return $this->redirectToRoute('gsadmin_view_activity', array('id' => $activity->getId()));
        }

        return $this->render('GSAdminBundle:Activity:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

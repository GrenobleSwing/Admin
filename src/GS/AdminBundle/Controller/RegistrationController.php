<?php

namespace GS\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use GS\StructureBundle\Entity\Registration;
use GS\StructureBundle\Entity\Topic;
use GS\StructureBundle\Form\Type\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{

    /**
     * @Route("/registration/{id}/validate", name="gsadmin_validate_registration", requirements={"id": "\d+"})
     * @Security("is_granted('validate', registration)")
     */
    public function validateAction(Registration $registration, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registration->validate();
            $em = $this->getDoctrine()->getManager();

            $membershipService = $this->get('gstoolbox.user.membership');
            $membershipService->fulfillMembershipRegistration($registration);

            # In case of a registration with a partner, validate also the partner
            if (null !== $registration->getPartnerRegistration()) {
                $registration->getPartnerRegistration()->validate();
                $membershipService->fulfillMembershipRegistration($registration->getPartnerRegistration());
            }

            $this->get('gstoolbox.registration.service')->onValidate($registration);

            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'inscription a bien été validée.");

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $registration->getTopic()->getId()));
        }

        return $this->render('GSAdminBundle:Registration:validate.html.twig', array(
            'registration' => $registration,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/{id}/wait", name="gsadmin_wait_registration", requirements={"id": "\d+"})
     * @Security("is_granted('wait', registration)")
     */
    public function waitAction(Registration $registration, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registration->wait();
            $em = $this->getDoctrine()->getManager();

            $this->get('gstoolbox.registration.service')->onWait($registration);

            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'inscription a bien été mise en liste d'attente.");

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $registration->getTopic()->getId()));
        }

        return $this->render('GSAdminBundle:Registration:wait.html.twig', array(
            'registration' => $registration,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/{id}/cancel", name="gsadmin_cancel_registration", requirements={"id": "\d+"})
     * @Security("is_granted('cancel', registration)")
     */
    public function cancelAction(Registration $registration, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $registration->cancel();

            $em = $this->getDoctrine()->getManager();

            // If the registration is not paid, there is no need to keep it.
            if ($registration->getState() != 'PAID' && $registration->getState() != 'PAYMENT_IN_PROGRESS') {
                $em->remove($registration);
            }

            $this->get('gstoolbox.registration.service')->onCancel($registration);

            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'inscription a bien été annulée.");

            return $this->redirectToRoute('gsadmin_view_topic', array('id' => $registration->getTopic()->getId()));
        }

        return $this->render('GSAdminBundle:Registration:cancel.html.twig', array(
            'registration' => $registration,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/add/{id}", name="gsadmin_add_registration", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Topic $topic, Request $request)
    {
        $registration = new Registration();
        $account = $this->getDoctrine()
                ->getRepository('GSStructureBundle:Account')
                ->findOneByUser($this->getUser());
        $registration->setAccount($account);
        $registration->setTopic($topic);

        $form = $this->createForm(RegistrationType::class, $registration);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($registration->getWithPartner() &&
                    null === $registration->getPartnerRegistration()) {
                $partner = $this->findPartner($registration);

                if (null !== $partner &&
                        null === $partner->getPartnerRegistration()) {
                    $registration->setPartnerRegistration($partner);
                }
            }

            $topic->addRegistration($registration);

            if ($topic->getAutoValidation()) {
                $registration->validate();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($registration);

            $this->get('gstoolbox.registration.service')->onSubmitted($registration);

            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Inscription bien enregistrée.');

            return $this->redirectToRoute('gsadmin_view_registration', array('id' => $registration->getId()));
        }

        return $this->render('GSAdminBundle:Registration:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    private function findPartner (Registration $registration)
    {
        $partnerAccount = $this->findPartnerAccount($registration);
        if ($partnerAccount === null) {
            return null;
        }
        if ($registration->getRole() == 'leader') {
            $partnerRole = 'follower';
        } else {
            $partnerRole = 'leader';
        }
        $partnerRegistrations = $this->getDoctrine()
                ->getRepository('GSStructureBundle:Registration')
                ->findBy(array(
                    'account' => $partnerAccount,
                    'topic' => $registration->getTopic(),
                    'role' => $partnerRole,
                    ));
        if ($partnerRegistrations === null || count($partnerRegistrations) != 1) {
            return null;
        }
        return $partnerRegistrations[0];
    }

    private function findPartnerAccount (Registration $registration)
    {
        $partnerAccounts = null;
        if ($registration->getPartnerEmail() !== null) {
            $partnerAccounts = $this->getDoctrine()
                    ->getRepository('GSStructureBundle:Account')
                    ->findByEmail($registration->getPartnerEmail());
        }
        elseif ($registration->getPartnerFirstName() !== null &&
                $registration->getPartnerLastName() !== null) {
            $partnerAccounts = $this->getDoctrine()
                    ->getRepository('GSStructureBundle:Account')
                    ->findBy(array(
                        'firstName' => $registration->getPartnerFirstName(),
                        'lastName' => $registration->getPartnerLastName()));
        }

        $partnerAccount = null;
        if ($partnerAccounts !== null && count($partnerAccounts) == 1) {
            $partnerAccount = $partnerAccounts[0];
        }
        return $partnerAccount;
    }

    /**
     * @Route("/registration/{id}/delete", name="gsadmin_delete_registration", requirements={"id": "\d+"})
     * @Security("is_granted('delete', registration)")
     */
    public function deleteAction(Registration $registration, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('gstoolbox.registration.service')->cleanPayments($registration);

            $em = $this->getDoctrine()->getManager();
            $em->remove($registration);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'inscription a bien été supprimée.");

            return $this->redirectToRoute('homepage');
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSAdminBundle:Registration:delete.html.twig', array(
            'registration' => $registration,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/{id}",
     *   name="gsadmin_view_registration",
     *   requirements={"id": "\d+"},
     *   options = { "expose" = true }
     * )
     * @Security("is_granted('view', registration)")
     */
    public function getAction(Registration $registration)
    {
        return $this->render('GSAdminBundle:Registration:view.html.twig', array(
            'registration' => $registration
        ));
    }

    /**
     * @Route("/registration", name="gsadmin_index_registration")
     * @Security("has_role('ROLE_SECRETARY')")
     */
    public function indexAction()
    {
        return $this->render('GSAdminBundle:Registration:index.html.twig');
    }

    /**
     * @Route("/registration/json", name="gsadmin_index_json_registration")
     * @Security("has_role('ROLE_SECRETARY')")
     */
    public function indexJsonAction()
    {
        $listRegistrations = $this->getDoctrine()->getManager()
                ->getRepository('GSStructureBundle:Registration')
                ->findAll()
                ;

        $serializedEntity = $this->get('jms_serializer')->serialize($listRegistrations, 'json');

        return JsonResponse::fromJsonString($serializedEntity);
    }

    /**
     * @Route("/registration/{id}/edit", name="gsadmin_edit_registration", requirements={"id": "\d+"})
     * @Security("is_granted('edit', registration)")
     */
    public function editAction(Registration $registration, Request $request)
    {
        $form = $this->createForm(RegistrationType::class, $registration);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Inscription bien modifiée.');

            return $this->redirectToRoute('gsadmin_view_registration', array('id' => $registration->getId()));
        }

        return $this->render('GSAdminBundle:Registration:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

}

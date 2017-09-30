<?php

namespace GS\AdminBundle\Services;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\MailerBundle\Message\MessageFactory;

use GS\StructureBundle\Entity\User;
use GS\StructureBundle\Entity\Registration;

class RegistrationService
{
    private $entityManager;
    private $mailer;
    private $messageFactory;

    public function __construct(EntityManager $entityManager, \Swift_Mailer $mailer,
            MessageFactory $messageFactory)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
    }

    public function cleanPayments(Registration $registration)
    {
        $em = $this->entityManager;

        $paymentItems = $em
            ->getRepository('GSStructureBundle:PaymentItem')
            ->findByRegistration($registration);

        foreach ($paymentItems as $paymentItem) {
            $payment = $paymentItem->getPayment();
            $em->remove($payment);
        }
        $em->flush();
    }

    // Not used yet
    public function checkRequirements(Registration $registration, User $user)
    {
        $requirements = array();

        $topic = $registration->getTopic();
        $requiredTopics = $topic->getRequiredTopics();
        if (null === $requiredTopics ||
                count($requiredTopics) == 0) {
            return $requirements;
        }

        $account = $this->entityManager
            ->getRepository('GSStructureBundle:Account')
            ->findOneByUser($user);

        foreach ($requiredTopics as $requiredTopic) {
            $results = $this->entityManager
                ->getRepository('GSStructureBundle:Registration')
                ->getRegistrationsForAccountAndTopic($account, $requiredTopic);
            if (null === $results) {
                $requirements[] = $requiredTopic->getId();
            }
        }

        return $requirements;
    }

    private function sendEmail(Registration $registration, $action)
    {
        $template = $this->entityManager
            ->getRepository('GSStructureBundle:ActivityEmail')
            ->findOneBy(array('activity' => $registration->getTopic()->getActivity(),
                'action' => $action));

        $params = array('registration' => $registration);
        $message = $this->messageFactory->get(
                (string)$template->getEmailTemplate(),
                $registration->getAccount()->getEmail(),
                $params,
                'fr');
        $this->mailer->send($message);
        return true;
    }

    public function onSubmitted(Registration $registration)
    {
        if (in_array(Registration::CREATE, $registration->getTopic()->getActivity()->getTriggeredEmails())) {
            $this->sendEmail($registration, Registration::CREATE);
        }
        return true;
    }

    public function onValidate(Registration $registration)
    {
        if (in_array(Registration::VALIDATE, $registration->getTopic()->getActivity()->getTriggeredEmails())) {
            $this->sendEmail($registration, Registration::VALIDATE);
        }
        return true;
    }

    public function onWait(Registration $registration)
    {
        if (in_array(Registration::WAIT, $registration->getTopic()->getActivity()->getTriggeredEmails())) {
            $this->sendEmail($registration, Registration::WAIT);
        }
        return true;
    }

    public function onCancel(Registration $registration)
    {
        $this->cleanPayments($registration);
        if (in_array(Registration::CANCEL, $registration->getTopic()->getActivity()->getTriggeredEmails())) {
            $this->sendEmail($registration, Registration::CANCEL);
        }
        return true;
    }

}
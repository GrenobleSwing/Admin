<?php

namespace GS\ToolboxBundle\Services;

use GS\StructureBundle\Entity\Payment;
use Lexik\Bundle\MailerBundle\Message\MessageFactory;

class PaymentService
{
    private $mailer;
    private $messageFactory;

    public function __construct(\Swift_Mailer $mailer,
            MessageFactory $messageFactory)
    {
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
    }

    public function sendEmailSuccess(Payment $payment)
    {
        $template = $payment->getItems()[0]
                ->getRegistration()
                ->getTopic()
                ->getActivity()
                ->getYear()
                ->getSociety()
                ->getEmailPaymentTemplate();

        $params = array('payment' => $payment);
        $message = $this->messageFactory->get(
                (string)$template,
                $payment->getAccount()->getEmail(),
                $params,
                'fr');
        $this->mailer->send($message);
    }

    public function sendEmailFailurePartialPayment(Payment $childPayment, $buttonHtml)
    {
        $payment = $childPayment->getParent();
        $template = $payment->getItems()[0]
                ->getRegistration()
                ->getTopic()
                ->getActivity()
                ->getYear()
                ->getSociety()
                ->getEmailPaymentFailureTemplate();

        $params = array('childPayment' => $childPayment, 'payment' => $payment, 'button' => $buttonHtml);
        $message = $this->messageFactory->get(
                (string)$template,
                $payment->getAccount()->getEmail(),
                $params,
                'fr');
        $this->mailer->send($message);
    }

}
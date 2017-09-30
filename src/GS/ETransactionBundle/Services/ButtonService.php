<?php

namespace GS\ETransactionBundle\Services;

use GS\ETransactionBundle\Entity\Payment;

class ButtonService
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    private function createButton(Payment $payment, $msg, $template)
    {
        $binKey = pack("H*", $payment->getEnvironment()->getHmacKey());

        // On calcule l’empreinte (à renseigner dans le paramètre PBX_HMAC) grâce à la fonction hash_hmac et // la clé binaire
        // On envoie via la variable PBX_HASH l'algorithme de hachage qui a été utilisé (SHA512 dans ce cas)
        // Pour afficher la liste des algorithmes disponibles sur votre environnement, décommentez la ligne // suivante
        // print_r(hash_algos());
        $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));
        // La chaîne sera envoyée en majuscules, d'où l'utilisation de strtoupper()

        // On crée le formulaire à envoyer à e-transactions
        // ATTENTION : l'ordre des champs est extrêmement important, il doit
        // correspondre exactement à l'ordre des champs dans la chaîne hachée
        return $this->twig->render($template, array(
            'payment' => $payment,
            'hmac' => $hmac,
        ));
    }

    public function createButton1x(Payment $payment)
    {
        // On crée la chaîne à hacher sans URLencodage
        $msg = $payment->getStringFullPayment();

        return $this->createButton($payment, $msg, 'GSETransactionBundle:Default:button1x.html.twig');
    }

    public function createButton2x(Payment $payment)
    {
        // On crée la chaîne à hacher sans URLencodage
        $msg = $payment->getStringPaymentIn2Times();

        return $this->createButton($payment, $msg, 'GSETransactionBundle:Default:button2x.html.twig');
    }

    public function createButton3x(Payment $payment)
    {
        // On crée la chaîne à hacher sans URLencodage
        $msg = $payment->getStringPaymentIn3Times();

        return $this->createButton($payment, $msg, 'GSETransactionBundle:Default:button3x.html.twig');
    }

}
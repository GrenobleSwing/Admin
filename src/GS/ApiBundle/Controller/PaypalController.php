<?php

namespace GS\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use GS\ApiBundle\Entity\Payment;
use GS\ApiBundle\Entity\PaymentItem;

class PaypalController extends Controller
{
    /**
     * @ApiDoc(
     *   section="Paypal",
     *   description="Create a new Paypal payment",
     *   parameters={
     *     {
     *       "name"="accountId",
     *       "dataType"="integer",
     *       "required"=true,
     *       "description"="Account id"
     *     },
     *     {
     *       "name"="activityId",
     *       "dataType"="integer",
     *       "required"=false,
     *       "description"="Activity id, only if the payment concern only this activity"
     *     }
     *   },
     *   statusCodes={
     *     200="The payment has been created",
     *   }
     * )
     * @Route("/paypal/create-payment")
     * @Method("POST")
     */
    public function CreatePaymentAction(Request $request)
    {
        $accountId = $request->query->get('accountId');
        if (null === $accountId) {
            return null;
        }
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('GSApiBundle:Account')->find($accountId);
        
        $activityId = $request->query->get('activityId');
        if (null !== $activityId) {
            $activity = $em->getRepository('GSApiBundle:Activity')->find($activityId);
        } else {
            $activity = null;
        }
        $details = $this->get('gsapi.account_balance')->getBalanceForPaypal($account, $activity);

        // Creation of a Payment that will store all the details
        $payment = $this->createPayment($details);
        
        // Creation of the Paypal payment using our own Payment entity
        $paypalPayment = $this->createPaypalPayment($payment, $request);
        
        $payment->setPaypalPaymentId($paypalPayment->getId());

        // We save the created Payment for later use
        $em->persist($payment);
        $em->flush();

        return new JsonResponse(array('paymentID' => $paypalPayment->getId()), 200);
    }

    /**
     * @ApiDoc(
     *   section="Paypal",
     *   description="Execute a given Paypal payment",
     *   parameters={
     *     {
     *       "name"="paymentId",
     *       "dataType"="integer",
     *       "required"=true,
     *       "description"="Payment id"
     *     },
     *   },
     *   statusCodes={
     *     200="The payment has been executed",
     *   }
     * )
     * @Route("/paypal/execute-payment")
     * @Method("GET")
     */
    public function ExecutePaymentAction(Request $request)
    {
        if ('true' == $request->query->get('success')) {
            $paypal = $this->get('paypal');
            $apiContext = $paypal->getApiContext();
            
            $paymentId = $request->query->get('paymentId');
            $paypalPayment = Payment::get($paymentId, $apiContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($request->query->get('PayerID'));
            
            try {
                $paypalPayment->execute($execution, $apiContext);
                $response = new Response('Success');
            } catch (\Exception $ex) {
                return null;
            }
        } else {
            $response = new Response('Failure');
        }
        
        // Updating the payment to mark it as paid
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('GSApiBundle:Payment')
                ->findOneBy(array('paypalPaymentId' => $paymentId));
        $payment->setState('PAID');
        $em->flush();
        
        return $response;
    }

    private function createPayment($details)
    {
        $payment = new Payment();
        $payment->setType('PAYPAL');
        foreach ($details as $line) {
            $registration = $line[0];
            $discount = $line[1];
            $paymentItem = new PaymentItem();
            $paymentItem->setRegistration($registration);
            $paymentItem->setDiscount($discount);
            $payment->addItem($paymentItem);
        }
        
        return $payment;
    }

    private function createPaypalPayment(Payment $payment, Request $request)
    {
        $itemList = $payment->getPaypalPaymentItemList();
        
        $amount = new Amount();
        $amount->setCurrency("EUR")
                ->setTotal($payment->getAmount());

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment description")
                ->setInvoiceNumber(uniqid());

        $baseUrl = $this->getBaseUrl($request);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($baseUrl . "/execute-payment?success=true")
                ->setCancelUrl($baseUrl . "/execute-payment?success=false");

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $paypalPayment = new PaypalPayment();
        $paypalPayment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
//                ->setExperienceProfileId('TXP-67804166P8087423J')
                ->setTransactions(array($transaction));

        $paypal = $this->get('paypal');
        $apiContext = $paypal->getApiContext();
        
        $paypalPayment->create($apiContext);
        return $paypalPayment;
    }

    private static function getBaseUrl(Request $request)
    {
        $protocol = 'http';
        if ($request->server->get('SERVER_PORT') == 443 ||
                (!empty($request->server->get('HTTPS')) &&
                strtolower($request->server->get('HTTPS')) == 'on')) {
            $protocol .= 's';
        }
        $host = $request->server->get('HTTP_HOST');
        $phpself = $request->server->get('PHP_SELF');
        return dirname($protocol . '://' . $host . $phpself);
    }

}

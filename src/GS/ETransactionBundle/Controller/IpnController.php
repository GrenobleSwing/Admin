<?php

namespace GS\ETransactionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IpnController extends Controller
{
    /**
     * @Route("/ipn", name="gse_transaction_ipn")
     */
    public function ipnAction()
    {
        $this->get('gs.e_transaction.signature.service')->verifySignature();
        return new Response();
    }

}

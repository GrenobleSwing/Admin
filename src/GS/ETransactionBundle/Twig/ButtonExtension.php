<?php

namespace GS\ETransactionBundle\Twig;

use GS\ETransactionBundle\Services\ButtonService;

class ButtonExtension extends \Twig_Extension
{
    /**
     * @var PaymentService
     */
    private $etrans;

    public function __construct(ButtonService $etrans) {
        $this->etrans = $etrans;
    }

    public function getGlobals()
    {
        return array(
            'buttonGenerator' => $this->etrans,
        );
    }

}
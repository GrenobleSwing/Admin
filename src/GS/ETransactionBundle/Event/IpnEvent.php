<?php

namespace GS\ETransactionBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * IpnEvent class.
 */
class IpnEvent extends Event
{
    const NAME = 'gs_etran.ipn.received';

    /**
     * @var array
     */
    private $data;

    /**
     * @var boolean
     */
    private $verified;

    /**
     * @var string
     */
    private $remAddr;

    /**
     * Constructor.
     *
     * @param array   $data
     * @param boolean $verified
     */
    public function __construct(array $data, $remAddr, $verified = false)
    {
        $this->data = $data;
        $this->remAddr = $remAddr;
        $this->verified = (bool) $verified;
    }

    /**
     * Returns all parameters sent on IPN.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns true if signature verification was successful.
     *
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * Returns IP contacting the IPN.
     *
     * @return string
     */
    public function getRemAddr()
    {
        return $this->remAddr;
    }

}

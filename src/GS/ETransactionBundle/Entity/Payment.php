<?php

namespace GS\ETransactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment
 * @ORM\Entity
 * @ORM\Table(name="gs_etran_payment")
 */
class Payment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Type("integer")
     * @Assert\GreaterThan(0)
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255
     * )
     */
    private $cmd;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255
     * )
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    private $porteur;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Assert\DateTime
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $ipnUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlEffectue;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlRefuse;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlAnnule;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ETransactionBundle\Entity\Environment")
     */
    private $environment;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->time = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set total
     *
     * @param string $total
     *
     * @return Payment
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set cmd
     *
     * @param string $cmd
     *
     * @return Payment
     */
    public function setCmd($cmd)
    {
        $this->cmd = $cmd;

        return $this;
    }

    /**
     * Get cmd
     *
     * @return string
     */
    public function getCmd()
    {
        return $this->cmd;
    }

    /**
     * Set porteur
     *
     * @param string $porteur
     *
     * @return Payment
     */
    public function setPorteur($porteur)
    {
        $this->porteur = $porteur;

        return $this;
    }

    /**
     * Get porteur
     *
     * @return string
     */
    public function getPorteur()
    {
        return $this->porteur;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     *
     * @return Payment
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }


    /**
     * Set environment
     *
     * @param \GS\ETransactionBundle\Entity\Environment $environment
     *
     * @return Payment
     */
    public function setEnvironment(\GS\ETransactionBundle\Entity\Environment $environment = null)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return \GS\ETransactionBundle\Entity\Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set ipnUrl
     *
     * @param string $ipnUrl
     *
     * @return Payment
     */
    public function setIpnUrl($ipnUrl)
    {
        $this->ipnUrl = $ipnUrl;

        return $this;
    }

    /**
     * Get ipnUrl
     *
     * @return string
     */
    public function getIpnUrl()
    {
        return $this->ipnUrl;
    }

    /**
     * Set urlEffectue
     *
     * @param string $urlEffectue
     *
     * @return Payment
     */
    public function setUrlEffectue($urlEffectue)
    {
        $this->urlEffectue = $urlEffectue;

        return $this;
    }

    /**
     * Get urlEffectue
     *
     * @return string
     */
    public function getUrlEffectue()
    {
        return $this->urlEffectue;
    }

    /**
     * Set urlRefuse
     *
     * @param string $urlRefuse
     *
     * @return Payment
     */
    public function setUrlRefuse($urlRefuse)
    {
        $this->urlRefuse = $urlRefuse;

        return $this;
    }

    /**
     * Get urlRefuse
     *
     * @return string
     */
    public function getUrlRefuse()
    {
        return $this->urlRefuse;
    }

    /**
     * Set urlAnnule
     *
     * @param string $urlAnnule
     *
     * @return Payment
     */
    public function setUrlAnnule($urlAnnule)
    {
        $this->urlAnnule = $urlAnnule;

        return $this;
    }

    /**
     * Get urlAnnule
     *
     * @return string
     */
    public function getUrlAnnule()
    {
        return $this->urlAnnule;
    }

    private function getStringCommon(\GS\ETransactionBundle\Entity\Config $config)
    {
        $msg = "PBX_SITE=" . $config->getSite() .
                "&PBX_RANG=" . $config->getRang() .
                "&PBX_IDENTIFIANT=" . $config->getIdentifiant() .
                "&PBX_DEVISE=" . $config->getDevise() .
                "&PBX_CMD=" . $this->getCmd() .
                "&PBX_PORTEUR=" . $this->getPorteur() .
                "&PBX_TYPEPAIEMENT=CARTE" .
                "&PBX_REPONDRE_A=" . $this->getIpnUrl() .
                "&PBX_ANNULE=" . $this->getUrlAnnule() .
                "&PBX_EFFECTUE=" . $this->getUrlEffectue() .
                "&PBX_REFUSE=" . $this->getUrlRefuse() .
                "&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E;sign:K" .
                "&PBX_HASH=SHA512" .
                "&PBX_TIME=" . $this->getTime()->format("c");

        return $msg;
    }

    public function getStringFullPayment()
    {
        $config = $this->getEnvironment()->getConfig();

        // On crée la chaîne à hacher sans URLencodage
        $msg = $this->getStringCommon($config);
        $msg .= "&PBX_TOTAL=" . (string)$this->getTotal();

        return $msg;
    }

    public function getStringPaymentIn2Times()
    {
        $config = $this->getEnvironment()->getConfig();
        $echeanceDate = clone $this->getTime();
        $echeanceDate->add(new \DateInterval('P1M'));

        $total1 = (int)($this->getTotal() / 2);
        $total2 = $this->getTotal() - $total1;

        // On crée la chaîne à hacher sans URLencodage
        $msg = $this->getStringCommon($config);
        $msg .= "&PBX_TOTAL=". (string)$total1 .
                "&PBX_DATE1=" . $echeanceDate->format("d/m/Y") .
                "&PBX_2MONT1=". (string)$total2;

        return $msg;
    }

    public function getStringPaymentIn3Times()
    {
        $config = $this->getEnvironment()->getConfig();
        $echeanceDate = clone $this->getTime();
        $echeanceDate->add(new \DateInterval('P1M'));

        $total1 = (int)($this->getTotal() / 3);
        $total2 = $this->getTotal() - 2 * $total1;

        // On crée la chaîne à hacher sans URLencodage
        $msg = $this->getStringCommon($config);
        $msg .= "&PBX_TOTAL=". (string)$total1 .
                "&PBX_DATE1=" . $echeanceDate->format("d/m/Y") .
                "&PBX_2MONT1=". (string)$total1 .
                "&PBX_DATE2=" . $echeanceDate->add(new \DateInterval('P1M'))->format("d/m/Y") .
                "&PBX_2MONT2=". (string)$total2;

        return $msg;
    }

}

<?php

namespace GS\ETransactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Environment
 * @ORM\Entity
 * @ORM\Table(name="gs_etran_environment")
 */
class Environment
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255
     * )
     */
    private $hmacKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlClassique;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlLight;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(
     *    checkDNS = true
     * )
     */
    private $urlMobile;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     * @Assert\All({
     *     @Assert\Ip
     * })
     */
    private $validIps = array();

    /**
     * @ORM\ManyToOne(targetEntity="GS\ETransactionBundle\Entity\Config", inversedBy="environments")
     */
    private $config;


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
     * Set name
     *
     * @param string $name
     *
     * @return Environment
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set hmacKey
     *
     * @param string $hmacKey
     *
     * @return Environment
     */
    public function setHmacKey($hmacKey)
    {
        $this->hmacKey = $hmacKey;

        return $this;
    }

    /**
     * Get hmacKey
     *
     * @return string
     */
    public function getHmacKey()
    {
        return $this->hmacKey;
    }

    /**
     * Set urlClassique
     *
     * @param string $urlClassique
     *
     * @return Environment
     */
    public function setUrlClassique($urlClassique)
    {
        $this->urlClassique = $urlClassique;

        return $this;
    }

    /**
     * Get urlClassique
     *
     * @return string
     */
    public function getUrlClassique()
    {
        return $this->urlClassique;
    }

    /**
     * Set urlLight
     *
     * @param string $urlLight
     *
     * @return Environment
     */
    public function setUrlLight($urlLight)
    {
        $this->urlLight = $urlLight;

        return $this;
    }

    /**
     * Get urlLight
     *
     * @return string
     */
    public function getUrlLight()
    {
        return $this->urlLight;
    }

    /**
     * Set urlMobile
     *
     * @param string $urlMobile
     *
     * @return Environment
     */
    public function setUrlMobile($urlMobile)
    {
        $this->urlMobile = $urlMobile;

        return $this;
    }

    /**
     * Get urlMobile
     *
     * @return string
     */
    public function getUrlMobile()
    {
        return $this->urlMobile;
    }

    /**
     * Set validIps
     *
     * @param array $validIps
     *
     * @return Environment
     */
    public function setValidIps($validIps)
    {
        $this->validIps = $validIps;

        return $this;
    }

    /**
     * Get validIps
     *
     * @return array
     */
    public function getValidIps()
    {
        return $this->validIps;
    }

    /**
     * Set config
     *
     * @param \GS\ETransactionBundle\Entity\Config $config
     *
     * @return Environment
     */
    public function setConfig(\GS\ETransactionBundle\Entity\Config $config = null)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return \GS\ETransactionBundle\Entity\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}

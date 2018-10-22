<?php

namespace GS\ETransactionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Config
 * @ORM\Entity
 * @ORM\Table(name="gs_etran_config")
 */
class Config
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 255
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=7, nullable=true)
     * @Assert\Regex(
     *     pattern="/^\d{7}$/",
     *     message="It should contains 7 digits"
     * )
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\Regex(
     *     pattern="/^\d{2}$/",
     *     message="It should contains 2 digits"
     * )
     */
    private $rang;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=9, nullable=true)
     * @Assert\Regex(
     *     pattern="/^\d{1,9}$/",
     *     message="It should contains 1 to 9 digits"
     * )
     */
    private $identifiant;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     * @Assert\Regex(
     *     pattern="/^\d{3}$/",
     *     message="It should contains 3 digits"
     * )
     */
    private $devise = "978";

    /**
     * @ORM\OneToMany(targetEntity="GS\ETransactionBundle\Entity\Environment", mappedBy="config", cascade={"persist", "remove"})
     */
    private $environments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->environments = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Config
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
     * Set site
     *
     * @param string $site
     *
     * @return Config
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set rang
     *
     * @param string $rang
     *
     * @return Config
     */
    public function setRang($rang)
    {
        $this->rang = $rang;

        return $this;
    }

    /**
     * Get rang
     *
     * @return string
     */
    public function getRang()
    {
        return $this->rang;
    }

    /**
     * Set identifiant
     *
     * @param string $identifiant
     *
     * @return Config
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    /**
     * Get identifiant
     *
     * @return string
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set devise
     *
     * @param string $devise
     *
     * @return Config
     */
    public function setDevise($devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get devise
     *
     * @return string
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Add environment
     *
     * @param \GS\ETransactionBundle\Entity\Environment $environment
     *
     * @return Config
     */
    public function addEnvironment(\GS\ETransactionBundle\Entity\Environment $environment)
    {
        $this->environments[] = $environment;
        $environment->setConfig($this);

        return $this;
    }

    /**
     * Remove environment
     *
     * @param \GS\ETransactionBundle\Entity\Environment $environment
     */
    public function removeEnvironment(\GS\ETransactionBundle\Entity\Environment $environment)
    {
        $this->environments->removeElement($environment);
    }

    /**
     * Get environments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

}

<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Registration
 *
 * Hateoas\Relation(
 *     "self",
 *     href = Hateoas\Route(
 *         "gsadmin_get_registration",
 *         parameters = { "registration" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('view', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "cancel",
 *     href = Hateoas\Route(
 *         "gsadmin_cancel_registration",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('cancel', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "edit",
 *     href = Hateoas\Route(
 *         "gsadmin_edit_registration",
 *         parameters = { "registration" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('edit', object))"
 *     )
 * )
 *
 * @ORM\Entity(repositoryClass="GS\StructureBundle\Repository\RegistrationRepository")
 * @UniqueEntity(
 *     fields = {"topic", "account"},
 *     repositoryMethod = "checkUniqueness",
 *     message = "This registration already exists."
 * )
 */
class Registration
{
    const CREATE = 'create';
    const WAIT = 'wait';
    const VALIDATE = 'validate';
    const CANCEL = 'cancel';
    const PAY = 'pay';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     * @Assert\Date()
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Choice({"leader", "follower"})
     */
    private $role = 'leader';

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $withPartner = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     strict = true
     * )
     */
    private $partnerEmail;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 64
     * )
     */
    private $partnerFirstName;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 64
     * )
     */
    private $partnerLastName;

    /**
     * States: submitted, waiting, validated, paid and cancelled
     *                      validate
     *     |---------------------------------------|
     *     |                                       |
     *     |        wait             validate      v          pay
     * submitted ----------> waiting ----------> validated ----------> paid
     *     |                   |                   |                   |
     *     | cancel            | cancel            | cancel            | cancel
     *     |                   v                   |                   v
     *     |-----> "delete the registration" <-----|               cancelled
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice({"SUBMITTED", "WAITING", "VALIDATED", "PAID", "CANCELLED"})
     */
    private $state = "SUBMITTED";

    /**
     * To store the amount that as been paid for the registration.
     * It is useful when computing the balance since a registration can be paid
     * without any Discount but later with the addition of another Registration
     * it can benefit from a Discount a thus the Account has paid too much and
     * it has to be taken into account for the balance.
     *
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     */
    private $amountPaid = 0.0;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Topic", inversedBy="registrations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $topic;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Account")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("accountId")
     * @Type("Relation")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Registration", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @SerializedName("partnerRegistrationId")
     * @Type("Relation")
     */
    private $partnerRegistration = null;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $acceptRules = false;

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
     * Set role
     *
     * @param string $role
     *
     * @return Registration
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Registration
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set topic
     *
     * @param \GS\StructureBundle\Entity\Topic $topic
     *
     * @return Registration
     */
    public function setTopic(\GS\StructureBundle\Entity\Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return \GS\StructureBundle\Entity\Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set account
     *
     * @param \GS\StructureBundle\Entity\Account $account
     *
     * @return Registration
     */
    public function setAccount(\GS\StructureBundle\Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \GS\StructureBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getTopic()->getCategory()->getPrice();
    }


    /**
     * Set amountPaid
     *
     * @param float $amountPaid
     *
     * @return Registration
     */
    public function setAmountPaid($amountPaid)
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * Get amountPaid
     *
     * @return float
     */
    public function getAmountPaid()
    {
        return $this->amountPaid;
    }

    /**
     * Get displayName
     *
     * @return float
     */
    public function getDisplayName()
    {
        return $this->getAccount()->getDisplayName() . ' - ' .
                $this->getTopic()->getTitle();
    }

    public function wait()
    {
        $this->setState('WAITING');
        return $this;
    }

    public function validate()
    {
        $this->setState('VALIDATED');
        return $this;
    }

    public function pay($amount = null)
    {
        $this->setState('PAID');
        if (null === $amount) {
            $this->setAmountPaid($this->getTopic()->getCategory()->getPrice());
        } else {
            $this->setAmountPaid($amount);
        }
        return $this;
    }

    public function cancel()
    {
        if ('PAID' == $this->getState()) {
            $this->setState('PARTIALLY_CANCELLED');
        } else {
            $this->setState('CANCELLED');
        }
        return $this;
    }

    /**
     * Set partnerEmail
     *
     * @param string $partnerEmail
     *
     * @return Registration
     */
    public function setPartnerEmail($partnerEmail)
    {
        $this->partnerEmail = $partnerEmail;

        return $this;
    }

    /**
     * Get partnerEmail
     *
     * @return string
     */
    public function getPartnerEmail()
    {
        return $this->partnerEmail;
    }

    /**
     * Set partnerFirstName
     *
     * @param string $partnerFirstName
     *
     * @return Registration
     */
    public function setPartnerFirstName($partnerFirstName)
    {
        $this->partnerFirstName = $partnerFirstName;

        return $this;
    }

    /**
     * Get partnerFirstName
     *
     * @return string
     */
    public function getPartnerFirstName()
    {
        return $this->partnerFirstName;
    }

    /**
     * Set partnerLastName
     *
     * @param string $partnerLastName
     *
     * @return Registration
     */
    public function setPartnerLastName($partnerLastName)
    {
        $this->partnerLastName = $partnerLastName;

        return $this;
    }

    /**
     * Get partnerLastName
     *
     * @return string
     */
    public function getPartnerLastName()
    {
        return $this->partnerLastName;
    }

    /**
     * Set withPartner
     *
     * @param boolean $withPartner
     *
     * @return Registration
     */
    public function setWithPartner($withPartner)
    {
        $this->withPartner = $withPartner;

        return $this;
    }

    /**
     * Get withPartner
     *
     * @return boolean
     */
    public function getWithPartner()
    {
        return $this->withPartner;
    }

    /**
     * Set partnerRegistration
     *
     * @param \GS\StructureBundle\Entity\Registration $partnerRegistration
     *
     * @return Registration
     */
    public function setPartnerRegistration(\GS\StructureBundle\Entity\Registration $partnerRegistration = null)
    {
        $this->partnerRegistration = $partnerRegistration;
        if ($this !== $partnerRegistration->getPartnerRegistration()) {
            $partnerRegistration->setPartnerRegistration($this);
        }

        return $this;
    }

    /**
     * Get partnerRegistration
     *
     * @return \GS\StructureBundle\Entity\Registration
     */
    public function getPartnerRegistration()
    {
        return $this->partnerRegistration;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Registration
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set acceptRules
     *
     * @param boolean $acceptRules
     *
     * @return Registration
     */
    public function setAcceptRules($acceptRules)
    {
        $this->acceptRules = $acceptRules;

        return $this;
    }

    /**
     * Get acceptRules
     *
     * @return boolean
     */
    public function getAcceptRules()
    {
        return $this->acceptRules;
    }
}

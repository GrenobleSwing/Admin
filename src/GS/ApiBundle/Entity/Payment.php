<?php

namespace GS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use PayPal\Api\ItemList;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment
 *
 * @ORM\Entity(repositoryClass="GS\ApiBundle\Repository\PaymentRepository")
 */
class Payment
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Valid types: CASH, TRANSFER, CHECK, CARD
     *
     * @ORM\Column(type="string", length=10)
     * @Assert\Choice({"CASH", "TRANSFER", "CHECK", "CARD"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=23, nullable=true)
     */
    private $ref;

    /**
     * States:
     *   - DRAFT: for online payments: once a payment is received moved to PAID
     *   - PAID
     *
     * @ORM\Column(type="string", length=6)
     * @Assert\Choice({"DRAFT", "IN_PROGRESS", "PAID"})
     */
    private $state = 'DRAFT';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     */
    private $amount = 0.0;

    /**
     * @ORM\Column(type="date")
     * @Type("DateTime<'Y-m-d'>")
     * @Assert\Date()
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="GS\ApiBundle\Entity\PaymentItem", mappedBy="payment", cascade={"persist", "remove"})
     */
    private $items;

    /**
     * @ORM\OneToOne(targetEntity="GS\ApiBundle\Entity\Invoice", mappedBy="payment")
     * @Type("Relation")
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Account", inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $account;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->date = new \DateTime();
        $this->ref = uniqid("", true);
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
     * Set type
     *
     * @param string $type
     *
     * @return Payment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    private function updateRegistrations()
    {
        if ('PAID' == $this->getState()) {
            foreach ($this->getItems() as $item) {
                $registration = $item->getRegistration();
                if (null !== $registration) {
                    $registration->pay($item->getAmount());
                }
            }
        }
    }

    public function updateAmount()
    {
        $amount = 0.0;
        foreach ($this->getItems() as $item) {
            $amount += $item->getAmount();
        }
        $this->setAmount($amount);
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Add item
     *
     * @param \GS\ApiBundle\Entity\PaymentItem $item
     *
     * @return Payment
     */
    public function addItem(\GS\ApiBundle\Entity\PaymentItem $item)
    {
        $this->items[] = $item;
        $item->setPayment($this);
        $this->updateAmount();
        // Mark all the resitrations (one per item) as paid
        $this->updateRegistrations();
        $this->updateAccount();

        return $this;
    }

    /**
     * Remove item
     *
     * @param \GS\ApiBundle\Entity\PaymentItem $item
     */
    public function removeItem(\GS\ApiBundle\Entity\PaymentItem $item)
    {
        $this->items->removeElement($item);
        // The Registration removed is not paid anymore but only validated
        $item->getRegistration()->setAmountPaid(0.0)->validate();
        $this->updateAmount();
        $this->updateAccount();
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Payment
     */
    public function setState($state)
    {
        $this->state = $state;

        // Mark all the resitrations (one per item) as paid if state is changed
        // to PAID otherwise do nothing.
        $this->updateRegistrations();

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Payment
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Payment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Update account
     *
     * @return Payment
     */
    private function updateAccount()
    {
        if (!$this->items->isEmpty() &&
                ($registration = $this->items->first()->getRegistration()) !== null) {
            $this->account = $registration->getAccount();
        }

        return $this;
    }

    /**
     * Set account
     *
     * @param \GS\ApiBundle\Entity\Account $account
     *
     * @return Payment
     */
    public function setAccount(\GS\ApiBundle\Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \GS\ApiBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set invoice
     *
     * @param \GS\ApiBundle\Entity\Invoice $invoice
     *
     * @return Payment
     */
    public function setInvoice(\GS\ApiBundle\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \GS\ApiBundle\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set ref
     *
     * @param string $ref
     *
     * @return Payment
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }
}

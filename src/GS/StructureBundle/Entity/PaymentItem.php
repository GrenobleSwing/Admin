<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment
 *
 * @ORM\Entity(repositoryClass="GS\StructureBundle\Repository\PaymentItemRepository")
 */
class PaymentItem
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     */
    private $amount = 0.0;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Registration")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("registrationId")
     * @Type("Relation")
     */
    private $registration;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Discount")
     * @SerializedName("discountId")
     * @Type("Relation")
     */
    private $discount;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Payment", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("paymentId")
     * @Type("Relation")
     */
    private $payment;


    public function __construct()
    {
        $this->discount = null;
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


    private function getDiscountAmount($amount, $coeff)
    {
        $discount = $this->getDiscount();
        if(null !== $discount) {
            if ($discount->getType() == 'percent') {
                return $amount * $discount->getValue() / 100;
            } else {
                return $coeff * $discount->getValue();
            }
        }
        return 0;
    }

    private function updateAmount()
    {
        if (null === $this->getRegistration()) {
            return;
        }
        $amount = $this->getRegistration()->getTopic()
                ->getCategory()->getPrice();

        $isSemester = $this->getRegistration()->getSemester();
        $coeff = $isSemester ? 0.5 : 1.0;

        $amount *= $coeff;

        // Apply the discount if needed
        $amount -= $this->getDiscountAmount($amount, $coeff);

        // Substract the amount already paid
        $amount -= $this->getRegistration()->getAmountPaid();

        // Save the amount
        $this->setAmount($amount);

        if (null !== $this->getPayment()) {
            $this->getPayment()->updateAmount();
        }
    }

    /**
     * Set registration
     *
     * @param \GS\StructureBundle\Entity\Registration $registration
     *
     * @return PaymentItem
     */
    public function setRegistration(\GS\StructureBundle\Entity\Registration $registration)
    {
        $this->registration = $registration;
        $this->updateAmount();

        return $this;
    }

    /**
     * Get registration
     *
     * @return \GS\StructureBundle\Entity\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set discount
     *
     * @param \GS\StructureBundle\Entity\Discount $discount
     *
     * @return PaymentItem
     */
    public function setDiscount(\GS\StructureBundle\Entity\Discount $discount = null)
    {
        $this->discount = $discount;
        $this->updateAmount();

        return $this;
    }

    /**
     * Get discount
     *
     * @return \GS\StructureBundle\Entity\Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set payment
     *
     * @param \GS\StructureBundle\Entity\Payment $payment
     *
     * @return PaymentItem
     */
    public function setPayment(\GS\StructureBundle\Entity\Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \GS\StructureBundle\Entity\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return PaymentItem
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

}

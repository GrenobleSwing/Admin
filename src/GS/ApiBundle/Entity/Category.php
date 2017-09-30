<?php

namespace GS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 * @ORM\Entity
 */
class Category
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\Length(
     *      min = 2,
     *      max = 200
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Activity", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("activityId")
     * @Type("Relation")
     */
    private $activity;

    /**
     * @ORM\ManyToMany(targetEntity="GS\ApiBundle\Entity\Discount")
     * @Type("Relation<Discount>")
     */
    private $discounts;


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
     * @return Category
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
     * Set price
     *
     * @param string $price
     *
     * @return Category
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set activity
     *
     * @param \GS\ApiBundle\Entity\Activity $activity
     *
     * @return Category
     */
    public function setActivity(\GS\ApiBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \GS\ApiBundle\Entity\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->discounts = new ArrayCollection();
    }

    /**
     * Add discount
     *
     * @param \GS\ApiBundle\Entity\Discount $discount
     *
     * @return Category
     */
    public function addDiscount(\GS\ApiBundle\Entity\Discount $discount)
    {
        $this->discounts[] = $discount;

        return $this;
    }

    /**
     * Remove discount
     *
     * @param \GS\ApiBundle\Entity\Discount $discount
     */
    public function removeDiscount(\GS\ApiBundle\Entity\Discount $discount)
    {
        $this->discounts->removeElement($discount);
    }

    /**
     * Get discounts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }
}

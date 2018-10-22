<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "gs_api_get_category",
 *         parameters = { "category" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('view', object))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "edit",
 *     href = @Hateoas\Route(
 *         "gs_api_edit_category",
 *         parameters = { "category" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('edit', object))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "remove",
 *     href = @Hateoas\Route(
 *         "gs_api_remove_category",
 *         parameters = { "category" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('delete', object))"
 *     )
 * )
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
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $canBeFreeTopicForTeachers;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Activity", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("activityId")
     * @Type("Relation")
     */
    private $activity;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\Discount")
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
     * @param \GS\StructureBundle\Entity\Activity $activity
     *
     * @return Category
     */
    public function setActivity(\GS\StructureBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \GS\StructureBundle\Entity\Activity
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
     * @param \GS\StructureBundle\Entity\Discount $discount
     *
     * @return Category
     */
    public function addDiscount(\GS\StructureBundle\Entity\Discount $discount)
    {
        $this->discounts[] = $discount;

        return $this;
    }

    /**
     * Remove discount
     *
     * @param \GS\StructureBundle\Entity\Discount $discount
     */
    public function removeDiscount(\GS\StructureBundle\Entity\Discount $discount)
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

    /**
     * Set canBeFreeTopicForTeachers
     *
     * @param boolean $canBeFreeTopicForTeachers
     *
     * @return Registration
     */
    public function setCanBeFreeTopicForTeachers($canBeFreeTopicForTeachers)
    {
        $this->canBeFreeTopicForTeachers = $canBeFreeTopicForTeachers;

        return $this;
    }

    /**
     * Get canBeFreeTopicForTeachers
     *
     * @return boolean
     */
    public function getCanBeFreeTopicForTeachers()
    {
        return $this->canBeFreeTopicForTeachers;
    }

}

<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Discount
 *
 * Hateoas\Relation(
 *     "self",
 *     href = Hateoas\Route(
 *         "gsadmin_get_discount",
 *         parameters = { "discount" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('view', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "edit",
 *     href = Hateoas\Route(
 *         "gsadmin_edit_discount",
 *         parameters = { "discount" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('edit', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "remove",
 *     href = Hateoas\Route(
 *         "gsadmin_remove_discount",
 *         parameters = { "discount" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('delete', object))"
 *     )
 * )
 * @ORM\Entity
 */
class Discount
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
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice({"percent", "amount"})
     */
    private $type;

    /**
     * @ORM\Column(type="float")
     * @Assert\Type("float")
     */
    private $value;

    /**
     * @ORM\Column(name="`condition`", type="string", length=200)
     * @Assert\Choice({"member", "student", "2nd", "3rd", "4th", "5th"})
     */
    private $condition;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Activity", inversedBy="discounts")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("activityId")
     * @Type("Relation")
     */
    private $activity;


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
     * @return Discount
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
     * Set type
     *
     * @param string $type
     *
     * @return Discount
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

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Discount
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set condition
     *
     * @param string $condition
     *
     * @return Discount
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set activity
     *
     * @param \GS\StructureBundle\Entity\Activity $activity
     *
     * @return Discount
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
}

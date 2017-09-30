<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Venue
 *
 * Hateoas\Relation(
 *     "self",
 *     href = Hateoas\Route(
 *         "gsadmin_get_venue",
 *         parameters = { "venue" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('view', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "edit",
 *     href = Hateoas\Route(
 *         "gsadmin_edit_venue",
 *         parameters = { "venue" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('edit', object))"
 *     )
 * )
 * Hateoas\Relation(
 *     "remove",
 *     href = Hateoas\Route(
 *         "gsadmin_remove_venue",
 *         parameters = { "venue" = "expr(object.getId())" }
 *     ),
 *     exclusion = Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('delete', object))"
 *     )
 * )
 * @ORM\Entity
 */
class Venue
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
     * @ORM\OneToOne(targetEntity="GS\StructureBundle\Entity\Address", cascade={"persist", "remove"})
     */
    private $address = null;


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
     * @return Venue
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
     * Set address
     *
     * @param \GS\StructureBundle\Entity\Address $address
     *
     * @return Venue
     */
    public function setAddress(\GS\StructureBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \GS\StructureBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}

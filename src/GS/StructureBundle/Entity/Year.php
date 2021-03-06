<?php

namespace GS\StructureBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Year
 *
 * @ORM\Entity(repositoryClass="GS\StructureBundle\Repository\YearRepository")
 */
class Year
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(
     *      min = 2,
     *      max = 64
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     * @Type("DateTime<'Y-m-d'>")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     * @Type("DateTime<'Y-m-d'>")
     */
    private $endDate;

    /**
     * States: draft, open, close
     *
     * @ORM\Column(type="string", length=16)
     * @Assert\Choice({"DRAFT", "OPEN", "CLOSE"})
     */
    private $state = 'DRAFT';

    /**
     * @ORM\Column(type="integer")
     * @Assert\Type("integer")
     * @Assert\Range(
     *      min = 0,
     *      max = 100
     * )
     */
    private $numberOfFreeTopicPerTeacher;

    /**
     * @ORM\OneToMany(targetEntity="GS\StructureBundle\Entity\Activity", mappedBy="year", cascade={"persist", "remove"})
     * @Type("Relation<Activity>")
     */
    private $activities;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Society", inversedBy="years")
     * @ORM\JoinColumn(nullable=false)
     * @Type("Relation")
     */
    private $society;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\User")
     * @ORM\JoinTable(name="year_user")
     * @Type("Relation<User>")
     */
    private $owners;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\User")
     * @ORM\JoinTable(name="year_teacher")
     * @Type("Relation<User>")
     */
    private $teachers;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->owners = new ArrayCollection();
    }

    /**
     * Add owner
     *
     * @param \GS\StructureBundle\Entity\User $owner
     *
     * @return Year
     */
    public function addOwner(\GS\StructureBundle\Entity\User $owner)
    {
        $this->owners[] = $owner;
        return $this;
    }

    /**
     * Remove owner
     *
     * @param \GS\StructureBundle\Entity\User $owner
     */
    public function removeOwner(\GS\StructureBundle\Entity\User $owner)
    {
        $this->owners->removeElement($owner);
        $owner->removeYear($this);
    }

    /**
     * Get owners
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwners()
    {
        return $this->owners;
    }

    /**
     * Add activity
     *
     * @param \GS\StructureBundle\Entity\Activity $activity
     *
     * @return Year
     */
    public function addActivity(\GS\StructureBundle\Entity\Activity $activity)
    {
        $this->activities[] = $activity;
        $activity->setYear($this);

        return $this;
    }

    /**
     * Remove activity
     *
     * @param \GS\StructureBundle\Entity\Activity $activity
     */
    public function removeActivity(\GS\StructureBundle\Entity\Activity $activity)
    {
        $this->activities->removeElement($activity);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivities()
    {
        return $this->activities;
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
     * Set title
     *
     * @param string $title
     *
     * @return Year
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Year
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Year
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Year
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Year
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
     * Set society
     *
     * @param \GS\StructureBundle\Entity\Society $society
     *
     * @return Year
     */
    public function setSociety(\GS\StructureBundle\Entity\Society $society)
    {
        $this->society = $society;

        return $this;
    }

    /**
     * Get society
     *
     * @return \GS\StructureBundle\Entity\Society
     */
    public function getSociety()
    {
        return $this->society;
    }

    /**
     * Set numberOfFreeTopicPerTeacher
     *
     * @param integer $numberOfFreeTopicPerTeacher
     *
     * @return Year
     */
    public function setNumberOfFreeTopicPerTeacher($numberOfFreeTopicPerTeacher)
    {
        $this->numberOfFreeTopicPerTeacher = $numberOfFreeTopicPerTeacher;

        return $this;
    }

    /**
     * Get numberOfFreeTopicPerTeacher
     *
     * @return integer
     */
    public function getNumberOfFreeTopicPerTeacher()
    {
        return $this->numberOfFreeTopicPerTeacher;
    }

    /**
     * Add teacher
     *
     * @param \GS\StructureBundle\Entity\User $teacher
     *
     * @return Year
     */
    public function addTeacher(\GS\StructureBundle\Entity\User $teacher)
    {
        $this->teachers[] = $teacher;

        return $this;
    }

    /**
     * Remove teacher
     *
     * @param \GS\StructureBundle\Entity\User $teacher
     */
    public function removeTeacher(\GS\StructureBundle\Entity\User $teacher)
    {
        $this->teachers->removeElement($teacher);
    }

    /**
     * Get teachers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeachers()
    {
        return $this->teachers;
    }

    /**
     * Close year
     */
    public function close()
    {
        $this->setState('CLOSE');
        foreach ($this->getActivities() as $activity) {
            $activity->close();
        }
    }

    /**
     * Get Membership Topics
     *
     * @return \GS\StructureBundle\Entity\Topic
     */
    public function getMembershipTopics ()
    {
        $topics = new ArrayCollection();
        foreach ($this->getActivities() as $activity) {
            if ($activity->getMembership() == true) {
//                $topics = array_merge($topics, $activity->getTopics());
                $topics = new ArrayCollection(
                    array_merge($topics->toArray(), $activity->getTopics()->toArray())
                );
            }
        }
        return $topics;
    }
}

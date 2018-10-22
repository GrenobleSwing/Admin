<?php

namespace GS\StructureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Topic
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "gs_api_get_topic",
 *         parameters = { "topic" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted('view', object))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "new_registration",
 *     href = @Hateoas\Route(
 *         "gs_api_new_topic_registration",
 *         parameters = { "topic" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(
 *         excludeIf = "expr(not is_granted(['ROLE_USER']))"
 *     )
 * )
 * @ORM\Entity(repositoryClass="GS\StructureBundle\Repository\TopicRepository")
 */
class Topic
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"registration_group"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Groups({"registration_group"})
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 200
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=16)
     * @Groups({"registration_group"})
     * @Assert\Choice({"couple", "solo", "adhesion"})
     */
    private $type = 'couple';

    /**
     * States: draft, open, close
     *
     * @ORM\Column(type="string", length=16)
     * @Assert\Choice({"DRAFT", "OPEN", "CLOSE"})
     */
    private $state = 'DRAFT';

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $autoValidation = false;

    /**
     * @ORM\Column(type="string", length=6)
     * @Assert\Choice({"PARENT", "TRUE", "FALSE"})
     */
    private $allowSemester = "PARENT";

    /**
     * @ORM\Column(type="array")
     */
    private $options;

    /**
     * @ORM\OneToMany(targetEntity="GS\StructureBundle\Entity\Schedule", mappedBy="topic", cascade={"persist", "remove"})
     */
    private $schedules;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Activity", inversedBy="topics")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @SerializedName("activityId")
     * @Type("Relation")
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="GS\StructureBundle\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("categoryId")
     * @Type("Relation")
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\Topic")
     * @ORM\JoinTable(name="topic_requirements")
     * @SerializedName("requiredTopicIds")
     * @Type("Relation<Topic>")
     */
    private $requiredTopics;

    /**
     * @ORM\OneToMany(targetEntity="GS\StructureBundle\Entity\Registration", mappedBy="topic", cascade={"persist", "remove"})
     * @SerializedName("registrationIds")
     * @Type("Relation<Registration>")
     */
    private $registrations;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\User")
     * @ORM\JoinTable(name="topic_owner")
     * @Type("Relation<User>")
     */
    private $owners;

    /**
     * @ORM\ManyToMany(targetEntity="GS\StructureBundle\Entity\User")
     * @ORM\JoinTable(name="topic_moderator")
     * @Type("Relation<User>")
     */
    private $moderators;


    public function __construct()
    {
        $this->options = array();
        $this->registrations = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->moderators = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    /**
     * Add owner
     *
     * @param \GS\StructureBundle\Entity\User $owner
     *
     * @return Topic
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
        $owner->removeTopic($this);
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
     * Add moderator
     *
     * @param \GS\StructureBundle\Entity\User $moderator
     *
     * @return Topic
     */
    public function addModerator(\GS\StructureBundle\Entity\User $moderator)
    {
        $this->moderators[] = $moderator;
        return $this;
    }

    /**
     * Remove moderator
     *
     * @param \GS\StructureBundle\Entity\User $moderator
     */
    public function removeModerator(\GS\StructureBundle\Entity\User $moderator)
    {
        $this->moderators->removeElement($moderator);
        $moderator->removeTopic($this);
    }

    /**
     * Get moderators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModerators()
    {
        return $this->moderators;
    }

    /**
     * Add registration
     *
     * @param \GS\StructureBundle\Entity\Registration $registration
     *
     * @return Topic
     */
    public function addRegistration(\GS\StructureBundle\Entity\Registration $registration)
    {
        $this->registrations[] = $registration;
        $registration->setTopic($this);

        return $this;
    }

    /**
     * Remove registration
     *
     * @param \GS\StructureBundle\Entity\Registration $registration
     */
    public function removeRegistration(\GS\StructureBundle\Entity\Registration $registration)
    {
        $this->registrations->removeElement($registration);
    }

    /**
     * Get registrations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRegistrations()
    {
        return $this->registrations;
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
     * @return Topic
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

    public function addOption($option)
    {
        if (!in_array($option, $this->options, true)) {
            $this->options[] = $option;
        }
        return $this;
    }

    public function removeOption($option)
    {
        if (($key = array_search($option, $this->options)) != false) {
            unset($this->options[$key]);
        }
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options
     *
     * @param array $options
     *
     * @return Topic
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set activity
     *
     * @param \GS\StructureBundle\Entity\Activity $activity
     *
     * @return Topic
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
     * Set category
     *
     * @param \GS\StructureBundle\Entity\Category $category
     *
     * @return Topic
     */
    public function setCategory(\GS\StructureBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \GS\StructureBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Topic
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
     * Set state
     *
     * @param string $state
     *
     * @return Topic
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
     * Add requiredTopic
     *
     * @param \GS\StructureBundle\Entity\Topic $requiredTopic
     *
     * @return Topic
     */
    public function addRequiredTopic(\GS\StructureBundle\Entity\Topic $requiredTopic)
    {
        $this->requiredTopics[] = $requiredTopic;

        return $this;
    }

    /**
     * Remove requiredTopic
     *
     * @param \GS\StructureBundle\Entity\Topic $requiredTopic
     */
    public function removeRequiredTopic(\GS\StructureBundle\Entity\Topic $requiredTopic)
    {
        $this->requiredTopics->removeElement($requiredTopic);
    }

    /**
     * Get requiredTopics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredTopics()
    {
        return $this->requiredTopics;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Topic
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
     * Add schedule
     *
     * @param \GS\StructureBundle\Entity\Schedule $schedule
     *
     * @return Topic
     */
    public function addSchedule(\GS\StructureBundle\Entity\Schedule $schedule)
    {
        $this->schedules[] = $schedule;
        $schedule->setTopic($this);

        return $this;
    }

    /**
     * Remove schedule
     *
     * @param \GS\StructureBundle\Entity\Schedule $schedule
     */
    public function removeSchedule(\GS\StructureBundle\Entity\Schedule $schedule)
    {
        $this->schedules->removeElement($schedule);
    }

    /**
     * Get schedules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * Set autoValidation
     *
     * @param boolean $autoValidation
     *
     * @return Topic
     */
    public function setAutoValidation($autoValidation)
    {
        $this->autoValidation = $autoValidation;

        return $this;
    }

    /**
     * Get autoValidation
     *
     * @return boolean
     */
    public function getAutoValidation()
    {
        return $this->autoValidation;
    }

    /**
     * @return boolean
     */
    public function isAdhesion()
    {
        return $this->getType() == 'adhesion';
    }

    /**
     * Get displayName
     *
     * @VirtualProperty
     * @SerializedName("displayName")
     * @return string
     */
    public function getDisplayName()
    {
        $display = $this->getTitle();
        if (count($this->getSchedules()) == 1) {
            $schedule = $this->getSchedules()[0];
            $display .= ' - ';
            $display .= $schedule->getStartDate()->format('D') . ' de ';
            $display .= $schedule->getStartTime()->format('H:i') . ' à ';
            $display .= $schedule->getEndTime()->format('H:i');

            if ($schedule->getVenue() !== null) {
                $display .= ' - ' . $schedule->getVenue()->getName();
            }

            if (!empty($schedule->getTeachers())) {
                $display .= ' (' . $schedule->getTeachers() . ')';
            }
        }
        return $display;
    }

    /**
     * Close topic
     */
    public function close()
    {
        $this->setState('CLOSE');
    }

    /**
     * Set allowSemester
     *
     * @param string $allowSemester
     *
     * @return Topic
     */
    public function setAllowSemester($allowSemester)
    {
        $this->allowSemester = $allowSemester;

        return $this;
    }

    /**
     * Get allowSemester
     *
     * @return string
     */
    public function getAllowSemester()
    {
        return $this->allowSemester;
    }

    /**
     * Is allowSemester
     *
     * @return boolean
     */
    public function isAllowSemester()
    {
        if ($this->allowSemester == 'PARENT') {
            return $this->getActivity()->getAllowSemester();
        } elseif ($this->allowSemester == 'TRUE') {
            return true;
        }
        return false;
    }

}

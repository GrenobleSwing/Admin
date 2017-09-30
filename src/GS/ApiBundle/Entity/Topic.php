<?php

namespace GS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Topic
 * @ORM\Entity(repositoryClass="GS\ApiBundle\Repository\TopicRepository")
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
     * @ORM\Column(type="array")
     */
    private $options;

    /**
     * @ORM\OneToMany(targetEntity="GS\ApiBundle\Entity\Schedule", mappedBy="topic", cascade={"persist", "remove"})
     */
    private $schedules;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Activity", inversedBy="topics")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @SerializedName("activityId")
     * @Type("Relation")
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("categoryId")
     * @Type("Relation")
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="GS\ApiBundle\Entity\Topic")
     * @ORM\JoinTable(name="topic_requirements")
     * @SerializedName("requiredTopicIds")
     * @Type("Relation<Topic>")
     */
    private $requiredTopics;

    /**
     * @ORM\OneToMany(targetEntity="GS\ApiBundle\Entity\Registration", mappedBy="topic", cascade={"persist", "remove"})
     * @SerializedName("registrationIds")
     * @Type("Relation<Registration>")
     */
    private $registrations;

    /**
     * @ORM\ManyToMany(targetEntity="GS\ApiBundle\Entity\User")
     * @ORM\JoinTable(name="topic_owner")
     * @Type("Relation<User>")
     */
    private $owners;

    /**
     * @ORM\ManyToMany(targetEntity="GS\ApiBundle\Entity\User")
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
     * @param \GS\ApiBundle\Entity\User $owner
     *
     * @return Topic
     */
    public function addOwner(\GS\ApiBundle\Entity\User $owner)
    {
        $this->owners[] = $owner;
        return $this;
    }

    /**
     * Remove owner
     *
     * @param \GS\ApiBundle\Entity\User $owner
     */
    public function removeOwner(\GS\ApiBundle\Entity\User $owner)
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
     * @param \GS\ApiBundle\Entity\User $moderator
     *
     * @return Topic
     */
    public function addModerator(\GS\ApiBundle\Entity\User $moderator)
    {
        $this->moderators[] = $moderator;
        return $this;
    }

    /**
     * Remove moderator
     *
     * @param \GS\ApiBundle\Entity\User $moderator
     */
    public function removeModerator(\GS\ApiBundle\Entity\User $moderator)
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
     * @param \GS\ApiBundle\Entity\Registration $registration
     *
     * @return Topic
     */
    public function addRegistration(\GS\ApiBundle\Entity\Registration $registration)
    {
        $this->registrations[] = $registration;
        $registration->setTopic($this);

        return $this;
    }

    /**
     * Remove registration
     *
     * @param \GS\ApiBundle\Entity\Registration $registration
     */
    public function removeRegistration(\GS\ApiBundle\Entity\Registration $registration)
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
     * @param \GS\ApiBundle\Entity\Activity $activity
     *
     * @return Topic
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
     * Set category
     *
     * @param \GS\ApiBundle\Entity\Category $category
     *
     * @return Topic
     */
    public function setCategory(\GS\ApiBundle\Entity\Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \GS\ApiBundle\Entity\Category
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
     * @param \GS\ApiBundle\Entity\Topic $requiredTopic
     *
     * @return Topic
     */
    public function addRequiredTopic(\GS\ApiBundle\Entity\Topic $requiredTopic)
    {
        $this->requiredTopics[] = $requiredTopic;

        return $this;
    }

    /**
     * Remove requiredTopic
     *
     * @param \GS\ApiBundle\Entity\Topic $requiredTopic
     */
    public function removeRequiredTopic(\GS\ApiBundle\Entity\Topic $requiredTopic)
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
     * @param \GS\ApiBundle\Entity\Schedule $schedule
     *
     * @return Topic
     */
    public function addSchedule(\GS\ApiBundle\Entity\Schedule $schedule)
    {
        $this->schedules[] = $schedule;
        $schedule->setTopic($this);

        return $this;
    }

    /**
     * Remove schedule
     *
     * @param \GS\ApiBundle\Entity\Schedule $schedule
     */
    public function removeSchedule(\GS\ApiBundle\Entity\Schedule $schedule)
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

}

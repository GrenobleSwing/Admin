<?php

namespace GS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Schedule
 *
 * @ORM\Entity
 */
class Schedule
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     * @Type("DateTime<'Y-m-d'>")
     */
    private $startDate;

    /**
     * @ORM\Column(type="time")
     * @Assert\Time()
     * @Type("DateTime<'G:i'>")
     */
    private $startTime;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     * @Type("DateTime<'Y-m-d'>")
     */
    private $endDate;

    /**
     * @ORM\Column(type="time")
     * @Assert\Time()
     * @Type("DateTime<'G:i'>")
     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice({"once", "weekly"})
     */
    private $frequency;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 100
     * )
     */
    private $teachers = null;

   /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Venue")
     * @ORM\JoinColumn(nullable=true)
     */
    private $venue = null;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Topic", inversedBy="schedules")
     * @ORM\JoinColumn(nullable=false)
     * @SerializedName("topicId")
     * @Type("Relation")
     */
    private $topic;


    /**
     * Constructor
     */
    public function __construct()
    {
        $now = new \DateTime();
        $this->startDate = $now;
        $this->endDate = $now;
        $this->startTime = new \DateTime('20:00');
        $this->endTime = new \DateTime('21:00');
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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Schedule
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
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return Schedule
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Schedule
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
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return Schedule
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set frequency
     *
     * @param string $frequency
     *
     * @return Schedule
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * Get frequency
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set teachers
     *
     * @param string $teachers
     *
     * @return Schedule
     */
    public function setTeachers($teachers)
    {
        $this->teachers = $teachers;

        return $this;
    }

    /**
     * Get teachers
     *
     * @return string
     */
    public function getTeachers()
    {
        return $this->teachers;
    }

    /**
     * Set topic
     *
     * @param \GS\ApiBundle\Entity\Topic $topic
     *
     * @return Schedule
     */
    public function setTopic(\GS\ApiBundle\Entity\Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return \GS\ApiBundle\Entity\Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set venue
     *
     * @param \GS\ApiBundle\Entity\Venue $venue
     *
     * @return Schedule
     */
    public function setVenue(\GS\ApiBundle\Entity\Venue $venue = null)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get venue
     *
     * @return \GS\ApiBundle\Entity\Venue
     */
    public function getVenue()
    {
        return $this->venue;
    }
}

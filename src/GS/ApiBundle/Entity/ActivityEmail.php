<?php

namespace GS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityEmail
 * @ORM\Entity
 */
class ActivityEmail
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity="Lexik\Bundle\MailerBundle\Entity\Email", cascade={"persist", "remove"})
     */
    private $emailTemplate;

    /**
     * @ORM\ManyToOne(targetEntity="GS\ApiBundle\Entity\Activity", inversedBy="emailTemplates")
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
     * Set action
     *
     * @param string $action
     *
     * @return ActivityEmail
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set emailTemplate
     *
     * @param \Lexik\Bundle\MailerBundle\Entity\Email $emailTemplate
     *
     * @return ActivityEmail
     */
    public function setEmailTemplate(\Lexik\Bundle\MailerBundle\Entity\Email $emailTemplate = null)
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    /**
     * Get emailTemplate
     *
     * @return \Lexik\Bundle\MailerBundle\Entity\Email
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * Set activity
     *
     * @param \GS\ApiBundle\Entity\Activity $activity
     *
     * @return ActivityEmail
     */
    public function setActivity(\GS\ApiBundle\Entity\Activity $activity = null)
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
}

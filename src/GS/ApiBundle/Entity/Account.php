<?php

namespace GS\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Account
 * @ORM\Entity
 * @Vich\Uploadable
 * @UniqueEntity("email")
 */
class Account
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"payment"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     * @Groups({"payment"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 64
     * )
     * @Groups({"payment"})
     */
    private $firstName = "";

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 64
     * )
     * @Groups({"payment"})
     */
    private $lastName = "";

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     */
    private $birthDate;

    /**
     * @ORM\Column(type="phone_number")
     * @Type("libphonenumber\PhoneNumber")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
   private $phoneNumber;

   /**
     * @ORM\OneToOne(targetEntity="GS\ApiBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $student = false;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $unemployed = false;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="account_image", fileNameProperty="imageName")
     * @Assert\File(
     *     maxSize = "3M",
     * )
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
    * @ORM\OneToOne(targetEntity="GS\ApiBundle\Entity\User", cascade={"persist", "remove"})
    * @SerializedName("userId")
    * @Type("Relation")
    */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="GS\ApiBundle\Entity\Payment", mappedBy="account", cascade={"persist", "remove"})
     * @Type("Relation<Payment>")
     */
    private $payments;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->birthDate = new \DateTime();
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $this->phoneNumber = $phoneNumberUtil->parse('0123456789', 'FR');
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Account
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Account
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return Account
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set phoneNumber
     *
     * @param phone_number $phoneNumber
     *
     * @return Account
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return phone_number
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set address
     *
     * @param \GS\ApiBundle\Entity\Address $address
     *
     * @return Account
     */
    public function setAddress(\GS\ApiBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \GS\ApiBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Account
     */
    public function setEmail($email)
    {
        $this->email = $email;

        // If the email is modified, the user login should be modified.
        if (null !== $this->user) {
            $this->user->setEmail($email);
        }

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user
     *
     * @param \GS\ApiBundle\Entity\User $user
     *
     * @return Account
     */
    public function setUser(\GS\ApiBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \GS\ApiBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set student
     *
     * @param boolean $student
     *
     * @return Account
     */
    public function setStudent($student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Is student
     *
     * @return boolean
     */
    public function isStudent()
    {
        return $this->student;
    }

    /**
     * Is member
     *
     * @return boolean
     */
    public function isMember()
    {
        return true;
    }

    /**
     * Get displayName
     *
     * @return float
     * @VirtualProperty
     * @SerializedName("displayName")
     * @Groups({"payment"})
     */
    public function getDisplayName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName() . ' (' . $this->getEmail() . ')';
    }

    /**
     * Add payment
     *
     * @param \GS\ApiBundle\Entity\Payment $payment
     *
     * @return Account
     */
    public function addPayment(\GS\ApiBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        $payment->setAccount($this);

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \GS\ApiBundle\Entity\Payment $payment
     */
    public function removePayment(\GS\ApiBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return Account
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     *
     * @return Product
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Get student
     *
     * @return boolean
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set unemployed
     *
     * @param boolean $unemployed
     *
     * @return Account
     */
    public function setUnemployed($unemployed)
    {
        $this->unemployed = $unemployed;

        return $this;
    }

    /**
     * Get unemployed
     *
     * @return boolean
     */
    public function getUnemployed()
    {
        return $this->unemployed;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Account
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

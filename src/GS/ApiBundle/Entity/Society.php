<?php

namespace GS\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GS\ETransactionBundle\Entity\Config;
use JMS\Serializer\Annotation\Type;
use Lexik\Bundle\MailerBundle\Entity\Email;
use Lexik\Bundle\MailerBundle\Entity\EmailTranslation;
use Lexik\Bundle\MailerBundle\Entity\Layout;
use Lexik\Bundle\MailerBundle\Entity\LayoutTranslation;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Society
 *
 * @ORM\Entity
 */
class Society
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     */
    private $taxInformation;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     */
    private $vatInformation;

   /**
     * @ORM\OneToOne(targetEntity="GS\ApiBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="phone_number")
     * @Type("libphonenumber\PhoneNumber")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
   private $phoneNumber;

    /**
     * @ORM\OneToMany(targetEntity="GS\ApiBundle\Entity\Year", mappedBy="society", cascade={"persist", "remove"})
     * @Type("Relation<Year>")
     */
    private $years;

    /**
     * @ORM\OneToOne(targetEntity="Lexik\Bundle\MailerBundle\Entity\Layout", cascade={"persist", "remove"})
     */
    private $emailPaymentLayout;

    /**
     * @ORM\OneToOne(targetEntity="Lexik\Bundle\MailerBundle\Entity\Email", cascade={"persist", "remove"})
     */
    private $emailPaymentTemplate;

    /**
     * @ORM\OneToOne(targetEntity="GS\ETransactionBundle\Entity\Config", cascade={"persist", "remove"})
     */
    private $paymentConfig;

    /**
     * @ORM\OneToOne(targetEntity="GS\ETransactionBundle\Entity\Environment")
     * @ORM\JoinColumn(nullable=true)
     */
    private $paymentEnvironment;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->years = new ArrayCollection();

        $layoutTranslations = array(
            array(
                'locale' => 'fr',
                'body' => '{% block content %}{% endblock %}',
            ),
            array(
                'locale' => 'en',
                'body' => '{% block content %}{% endblock %}',
            ),
        );
        $layout = new Layout();
        $layout->setReference(uniqid('layout_payment_'));
        $layout->setDescription('Layout for payment emails');
        $layout->setDefaultLocale('fr');
        foreach ($layoutTranslations as $trans) {
            $layoutTranslation = new LayoutTranslation();
            $layoutTranslation->setBody($trans['body']);
            $layoutTranslation->setLang($trans['locale']);
            $layout->addTranslation($layoutTranslation);
        }
        $this->setEmailPaymentLayout($layout);

        $defaultBodyFr = "";
        $defaultBodyFr .= "Bonjour {{ payment.account.firstName }} {{ payment.account.lastName }},<br>\n";
        $defaultBodyFr .= "Nous avons bien reçu le paiement de {{ payment.amount }}&euro;\n";
        $defaultBodyFr .= "le {{ payment.date|date('d/m/Y', timezone='Europe/Paris') }}\n";
        $defaultBodyFr .= "pour le règlement des inscriptions suivantes :\n";
        $defaultBodyFr .= "{% import 'GSApiBundle:PaymentItem:macros.html.twig' as macro %}\n";
        $defaultBodyFr .= "<ul>\n";
        $defaultBodyFr .= "    {% for item in payment.items %}\n";
        $defaultBodyFr .= "        <li>\n";
        $defaultBodyFr .= "            {{ macro.print(item) }}\n";
        $defaultBodyFr .= "        </li>\n";
        $defaultBodyFr .= "    {% endfor %}\n";
        $defaultBodyFr .= "</ul>\n";
        $defaultBodyFr .= "<br>";
        $defaultBodyFr .= "Cordialement,\n";
        $defaultBodyFr .= "<br>";
        $defaultBodyFr .= "Grenoble Swing\n";
        $emailTranslations = array(
            array(
                'locale' => 'fr',
                'subject' => '[Grenoble Swing] Paiement',
                'body' => $defaultBodyFr,
                'from_address' => 'info@grenobleswing.com',
                'from_name' => 'Grenoble Swing',
            ),
            array(
                'locale' => 'en',
                'subject' => '[Grenoble Swing] Payment',
                'body' => 'Put your text here.',
                'from_address' => 'info@grenobleswing.com',
                'from_name' => 'Grenoble Swing',
            ),
        );

        $email = new Email();
        $email->setDescription('Template for payment emails');
        $email->setReference(uniqid('template_payment_'));
        $email->setSpool(false);
        $email->setLayout($layout);
        $email->setUseFallbackLocale(true);
        foreach ($emailTranslations as $trans) {
            $emailTranslation = new EmailTranslation();
            $emailTranslation->setLang($trans['locale']);
            $emailTranslation->setSubject($trans['subject']);
            $emailTranslation->setBody($trans['body']);
            $emailTranslation->setFromAddress($trans['from_address']);
            $emailTranslation->setFromName($trans['from_name']);
            $email->addTranslation($emailTranslation);
        }

        $this->setEmailPaymentTemplate($email);

        $this->setPaymentConfig(new Config());
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
     * Set name
     *
     * @param string $name
     *
     * @return Society
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
    public function getname()
    {
        return $this->name;
    }

    /**
     * Set phoneNumber
     *
     * @param phone_number $phoneNumber
     *
     * @return Society
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
     * @return Society
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
     * @return Society
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set taxInformation
     *
     * @param string $taxInformation
     *
     * @return Society
     */
    public function setTaxInformation($taxInformation)
    {
        $this->taxInformation = $taxInformation;

        return $this;
    }

    /**
     * Get taxInformation
     *
     * @return string
     */
    public function getTaxInformation()
    {
        return $this->taxInformation;
    }

    /**
     * Set vatInformation
     *
     * @param string $vatInformation
     *
     * @return Society
     */
    public function setVatInformation($vatInformation)
    {
        $this->vatInformation = $vatInformation;

        return $this;
    }

    /**
     * Get vatInformation
     *
     * @return string
     */
    public function getVatInformation()
    {
        return $this->vatInformation;
    }

    /**
     * Add year
     *
     * @param \GS\ApiBundle\Entity\Year $year
     *
     * @return Society
     */
    public function addYear(\GS\ApiBundle\Entity\Year $year)
    {
        $this->years[] = $year;
        $year->setSociety($this);

        return $this;
    }

    /**
     * Remove year
     *
     * @param \GS\ApiBundle\Entity\Year $year
     */
    public function removeYear(\GS\ApiBundle\Entity\Year $year)
    {
        $this->years->removeElement($year);
    }

    /**
     * Get years
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * Set emailPaymentLayout
     *
     * @param \Lexik\Bundle\MailerBundle\Entity\Layout $emailPaymentLayout
     *
     * @return Society
     */
    public function setEmailPaymentLayout(\Lexik\Bundle\MailerBundle\Entity\Layout $emailPaymentLayout = null)
    {
        $this->emailPaymentLayout = $emailPaymentLayout;

        return $this;
    }

    /**
     * Get emailPaymentLayout
     *
     * @return \Lexik\Bundle\MailerBundle\Entity\Layout
     */
    public function getEmailPaymentLayout()
    {
        return $this->emailPaymentLayout;
    }

    /**
     * Set emailPaymentTemplate
     *
     * @param \Lexik\Bundle\MailerBundle\Entity\Email $emailPaymentTemplate
     *
     * @return Society
     */
    public function setEmailPaymentTemplate(\Lexik\Bundle\MailerBundle\Entity\Email $emailPaymentTemplate = null)
    {
        $this->emailPaymentTemplate = $emailPaymentTemplate;

        return $this;
    }

    /**
     * Get emailPaymentTemplate
     *
     * @return \Lexik\Bundle\MailerBundle\Entity\Email
     */
    public function getEmailPaymentTemplate()
    {
        return $this->emailPaymentTemplate;
    }

    /**
     * Set paymentConfig
     *
     * @param \GS\ETransactionBundle\Entity\Config $paymentConfig
     *
     * @return Society
     */
    public function setPaymentConfig(\GS\ETransactionBundle\Entity\Config $paymentConfig = null)
    {
        $this->paymentConfig = $paymentConfig;

        return $this;
    }

    /**
     * Get paymentConfig
     *
     * @return \GS\ETransactionBundle\Entity\Config
     */
    public function getPaymentConfig()
    {
        return $this->paymentConfig;
    }

    /**
     * Set paymentEnvironment
     *
     * @param \GS\ETransactionBundle\Entity\Environment $paymentEnvironment
     *
     * @return Society
     */
    public function setPaymentEnvironment(\GS\ETransactionBundle\Entity\Environment $paymentEnvironment = null)
    {
        $this->paymentEnvironment = $paymentEnvironment;

        return $this;
    }

    /**
     * Get paymentEnvironment
     *
     * @return \GS\ETransactionBundle\Entity\Environment
     */
    public function getPaymentEnvironment()
    {
        return $this->paymentEnvironment;
    }
}

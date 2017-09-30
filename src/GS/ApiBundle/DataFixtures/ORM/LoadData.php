<?php

namespace GS\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\MailerBundle\Entity\Email;
use Lexik\Bundle\MailerBundle\Entity\EmailTranslation;

use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\ActivityEmail;
use GS\ApiBundle\Entity\Address;
use GS\ApiBundle\Entity\Category;
use GS\ApiBundle\Entity\Discount;
use GS\ApiBundle\Entity\Registration;
use GS\ApiBundle\Entity\Schedule;
use GS\ApiBundle\Entity\Society;
use GS\ApiBundle\Entity\Topic;
use GS\ApiBundle\Entity\Venue;
use GS\ApiBundle\Entity\Year;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager)
    {
        $organizerUser = $this->getReference('organizer_user');
        $adminUser = $this->getReference('admin_user');

        $address1 = new Address();
        $address1->setStreet('2 rue Mozart');
        $address1->setZipCode('38000');
        $address1->setCity('Grenoble');

        $venue = new Venue();
        $venue->setName('Les Planches');
        $venue->setAddress($address1);

        $manager->persist($venue);

        $address2 = new Address();
        $address2->setStreet('3 rue Henri Moissan');
        $address2->setZipCode('38100');
        $address2->setCity('Grenoble');

        $society = new Society();
        $society->setAddress($address2);
        $society->setPhoneNumber($this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR'));
        $society->setName('Grenoble Swing');
        $society->setEmail('info@grenobleswing.com');
        $society->setTaxInformation('SIRET : 22222222');
        $society->setVatInformation('TVA Intra : FR2222222');

        $year = new Year();
        $year->setTitle('Annee 2016-2017');
        $year->setDescription('description pour annee 2016-2017');
        $year->setStartDate(new \DateTime('2016-09-01'));
        $year->setEndDate(new \DateTime('2017-08-31'));
        $year->setState('OPEN');
        $year->addOwner($adminUser);

        $activity1 = new Activity();
        $activity1->setTitle('Adhesion');
        $activity1->setDescription('Adhesion annuelle a Grenoble Swing');
        $activity1->setState('OPEN');
        $activity1->setMembership(true);
        $activity1->addOwner($organizerUser);

        $emailTranslations = array(
            array(
                'locale' => 'fr',
                'subject' => '[Grenoble Swing] Inscription',
                'body' => 'Mettre votre texte ici.',
                'from_address' => 'info@grenobleswing.com',
                'from_name' => 'Grenoble Swing',
            ),
            array(
                'locale' => 'en',
                'subject' => '[Grenoble Swing] Registration',
                'body' => 'Put your text here.',
                'from_address' => 'info@grenobleswing.com',
                'from_name' => 'Grenoble Swing',
            ),
        );

        $layout1 = $activity1->getEmailLayout();
        foreach (array(Registration::CREATE, Registration::WAIT,
            Registration::VALIDATE, Registration::CANCEL) as
                $action) {
            $email = new Email();
            $email->setDescription($action);
            $email->setReference(uniqid('template_'));
            $email->setSpool(false);
            $email->setLayout($layout1);
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
            $activityEmail = new ActivityEmail();
            $activityEmail->setAction($action);
            $activityEmail->setEmailTemplate($email);

            $activity1->addEmailTemplate($activityEmail);
        }

        $category1 = new Category();
        $category1->setName('Adhesion');
        $category1->setPrice(10.0);

        $activity1->addCategory($category1);

        $schedule = new Schedule();
        $schedule->setFrequency('weekly');
        $schedule->setStartDate(new \DateTime('2016-09-14'));
        $schedule->setEndDate(new \DateTime('2017-06-24'));
        $schedule->setStartTime(new \DateTime('20:30'));
        $schedule->setEndTime(new \DateTime('21:30'));

        $topic1 = new Topic();
        $topic1->setTitle('Adhesion');
        $topic1->setDescription('Adhesion annuelle');
        $topic1->setType('adhesion');
        $topic1->setCategory($category1);
        $topic1->setState('OPEN');
        $topic1->setAutoValidation(true);
        $topic1->addOwner($organizerUser);

        $activity1->addTopic($topic1);

        $activity2 = new Activity();
        $activity2->setTitle('Cours et troupes');
        $activity2->setDescription('Les cours et les troupes');
        $activity2->setState('OPEN');
        $activity2->setMembersOnly(true);
        $activity2->setMembershipTopic($topic1);
        $activity2->addOwner($organizerUser);

        $layout2 = $activity2->getEmailLayout();
        foreach (array(Registration::CREATE, Registration::WAIT,
            Registration::VALIDATE, Registration::CANCEL) as
                $action) {
            $email = new Email();
            $email->setDescription($action);
            $email->setReference(uniqid('template_'));
            $email->setSpool(false);
            $email->setLayout($layout2);
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
            $activityEmail = new ActivityEmail();
            $activityEmail->setAction($action);
            $activityEmail->setEmailTemplate($email);

            $activity2->addEmailTemplate($activityEmail);
        }

        $category2 = new Category();
        $category2->setName('Cours');
        $category2->setPrice(190.0);

        $category3 = new Category();
        $category3->setName('Troupe');
        $category3->setPrice(90.0);

        $discount1 = new Discount();
        $discount1->setName('Etudiant');
        $discount1->setType('percent');
        $discount1->setValue(20.0);
        $discount1->setCondition('student');

        $discount2 = new Discount();
        $discount2->setName('2e cours');
        $discount2->setType('percent');
        $discount2->setValue(30.0);
        $discount2->setCondition('2nd');

        $category2->addDiscount($discount1);
        $category2->addDiscount($discount2);

        $activity2->addCategory($category2);
        $activity2->addCategory($category3);
        $activity2->addDiscount($discount1);
        $activity2->addDiscount($discount2);

        $topic2 = new Topic();
        $topic2->setTitle('Lindy debutant');
        $topic2->setDescription('Cours de lindy');
        $topic2->setType('couple');
        $topic2->setState('OPEN');
        $topic2->setCategory($category2);
        $topic2->addRequiredTopic($topic1);
        $topic2->addSchedule($schedule);
        $topic2->addOwner($organizerUser);

        $topic3 = new Topic();
        $topic3->setTitle('Lindy intermediaire');
        $topic3->setDescription('Cours de lindy');
        $topic3->setType('couple');
        $topic3->setState('OPEN');
        $topic3->setCategory($category2);
        $topic3->addRequiredTopic($topic1);
        $topic3->addSchedule(clone $schedule);
        $topic3->addOwner($organizerUser);

        $topic4 = new Topic();
        $topic4->setTitle('Lindy avance');
        $topic4->setDescription('Cours de lindy');
        $topic4->setType('couple');
        $topic4->setState('OPEN');
        $topic4->setCategory($category2);
        $topic4->addRequiredTopic($topic1);
        $topic4->addSchedule(clone $schedule);
        $topic4->addOwner($organizerUser);

        $topic5 = new Topic();
        $topic5->setTitle('Troupe avancee');
        $topic5->setDescription('Troupe avancee');
        $topic5->setType('couple');
        $topic5->setState('OPEN');
        $topic5->setCategory($category3);
        $topic5->addRequiredTopic($topic1);
        $topic5->addSchedule(clone $schedule);
        $topic5->addOwner($organizerUser);

        $topic6 = new Topic();
        $topic6->setTitle('Troupe charleston');
        $topic6->setDescription('Troupe charleston');
        $topic6->setType('solo');
        $topic6->setState('OPEN');
        $topic6->setCategory($category3);
        $topic6->addRequiredTopic($topic1);
        $topic6->addSchedule(clone $schedule);
        $topic6->addOwner($organizerUser);

        $activity2->addTopic($topic2);
        $activity2->addTopic($topic3);
        $activity2->addTopic($topic4);
        $activity2->addTopic($topic5);
        $activity2->addTopic($topic6);

        $year->addActivity($activity1);
        $year->addActivity($activity2);

        $society->addYear($year);

        $manager->persist($society);
        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}

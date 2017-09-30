<?php

namespace GS\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use GS\ApiBundle\Entity\User;
use GS\ApiBundle\Entity\Account;
use GS\ApiBundle\Entity\Address;

class LoadUser extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
        $userManager = $this->container->get('fos_user.user_manager');

        $admin_user = $userManager->createUser();
        $admin_user->setEmail('admin@test.com');
        $admin_user->setPlainPassword('test');
        $admin_user->setEnabled(true);
        $admin_user->addRole('ROLE_ADMIN');

        $admin_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $admin_account = new Account();
        $admin_account->setFirstName('Admin');
        $admin_account->setLastName('Test');
        $admin_account->setAddress(new Address());
        $admin_account->setBirthDate(new \DateTime('1986-04-26'));
        $admin_account->setUser($admin_user);
        $admin_account->setEmail($admin_user->getEmail());
        $admin_account->setPhoneNumber($admin_phoneNumber);

        $this->addReference('admin_user', $admin_user);

        $manager->persist($admin_account);

        $organizer_user = $userManager->createUser();
        $organizer_user->setEmail('organizer@test.com');
        $organizer_user->setPlainPassword('test');
        $organizer_user->setEnabled(true);
        $organizer_user->addRole('ROLE_ORGANIZER');

        $organizer_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $organizer_account = new Account();
        $organizer_account->setFirstName('Organizer');
        $organizer_account->setLastName('Test');
        $organizer_account->setAddress(new Address());
        $organizer_account->setBirthDate(new \DateTime('1986-04-26'));
        $organizer_account->setUser($organizer_user);
        $organizer_account->setEmail($organizer_user->getEmail());
        $organizer_account->setPhoneNumber($organizer_phoneNumber);

        $this->addReference('organizer_user', $organizer_user);

        $manager->persist($organizer_account);

        $teacher1_user = $userManager->createUser();
        $teacher1_user->setEmail('teacher1@test.com');
        $teacher1_user->setPlainPassword('test');
        $teacher1_user->setEnabled(true);
        $teacher1_user->addRole('ROLE_TOPIC_MANAGER');

        $teacher1_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $teacher1_account = new Account();
        $teacher1_account->setFirstName('Teacher 1');
        $teacher1_account->setLastName('Test');
        $teacher1_account->setAddress(new Address());
        $teacher1_account->setBirthDate(new \DateTime('1986-04-26'));
        $teacher1_account->setUser($teacher1_user);
        $teacher1_account->setEmail($teacher1_user->getEmail());
        $teacher1_account->setPhoneNumber($teacher1_phoneNumber);

        $manager->persist($teacher1_account);

        $teacher2_user = $userManager->createUser();
        $teacher2_user->setEmail('teacher2@test.com');
        $teacher2_user->setPlainPassword('test');
        $teacher2_user->setEnabled(true);
        $teacher2_user->addRole('ROLE_TOPIC_MANAGER');

        $teacher2_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $teacher2_account = new Account();
        $teacher2_account->setFirstName('Teacher 2');
        $teacher2_account->setLastName('Test');
        $teacher2_account->setAddress(new Address());
        $teacher2_account->setBirthDate(new \DateTime('1986-04-26'));
        $teacher2_account->setUser($teacher2_user);
        $teacher2_account->setEmail($teacher2_user->getEmail());
        $teacher2_account->setPhoneNumber($teacher2_phoneNumber);

        $manager->persist($teacher2_account);

        $user1 = $userManager->createUser();
        $user1->setEmail('john.doe@test.com');
        $user1->setPlainPassword('test');
        $user1->setEnabled(true);

        $user1_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $user1_account = new Account();
        $user1_account->setFirstName('John');
        $user1_account->setLastName('Doe');
        $user1_account->setAddress(new Address());
        $user1_account->setBirthDate(new \DateTime('1986-04-26'));
        $user1_account->setUser($user1);
        $user1_account->setEmail($user1->getEmail());
        $user1_account->setPhoneNumber($user1_phoneNumber);

        $user2 = $userManager->createUser();
        $user2->setEmail('jane.doe@test.com');
        $user2->setPlainPassword('test');
        $user2->setEnabled(true);

        $user2_phoneNumber = $this->container->get('libphonenumber.phone_number_util')->parse('0380581981', 'FR');
        $user2_account = new Account();
        $user2_account->setFirstName('Jane');
        $user2_account->setLastName('Doe');
        $user2_account->setAddress(new Address());
        $user2_account->setBirthDate(new \DateTime('1986-04-26'));
        $user2_account->setUser($user2);
        $user2_account->setEmail($user2->getEmail());
        $user2_account->setPhoneNumber($user2_phoneNumber);

        $manager->persist($user1_account);
        $manager->persist($user2_account);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }
}

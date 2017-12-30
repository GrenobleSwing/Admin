<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\Society;
use GS\StructureBundle\Entity\Year;
use GS\StructureBundle\Form\Type\YearType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("", name="gsadmin_")
 */
class YearController extends Controller
{
    /**
     * @Route("/year/{id}/open", name="open_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function openAction(Year $year, Request $request)
    {
        if ('DRAFT' != $year->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to open year");
            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year->setState('OPEN');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été ouverte.");

            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        return $this->render('GSAdminBundle:Year:open.html.twig', array(
            'year' => $year,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/{id}/close", name="close_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function closeAction(Year $year, Request $request)
    {
        if ('OPEN' != $year->getState()) {
            $request->getSession()->getFlashBag()->add('danger', "Impossible to close year");
            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $year->setState('CLOSE');
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été fermée.");

            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        return $this->render('GSAdminBundle:Year:close.html.twig', array(
            'year' => $year,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/add/{id}", name="add_year")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Society $society, Request $request)
    {
        $year = new Year();
        $year->setSociety($society);
        $form = $this->createForm(YearType::class, $year);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$year->getOwners()->contains($this->getUser())) {
                $year->addOwner($this->getUser());
            }
            $society->addYear($year);

            $em = $this->getDoctrine()->getManager();
            $em->persist($year);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Année bien enregistrée.');

            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        return $this->render('GSAdminBundle:Year:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/year/{id}/delete", name="delete_year", requirements={"id": "\d+"})
     * @Security("is_granted('delete', year)")
     */
    public function deleteAction(Year $year, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($year);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "L'année a bien été supprimé.");

            return $this->redirect($this->generateUrl('homepage'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSAdminBundle:Year:delete.html.twig', array(
                    'year' => $year,
                    'form' => $form->createView()
        ));
    }

    /**
     * @Route("/year/{id}", name="view_year", requirements={"id": "\d+"})
     * @Security("is_granted('view', year)")
     */
    public function viewAction(Year $year)
    {
        return $this->render('GSAdminBundle:Year:view.html.twig', array(
                    'year' => $year
        ));
    }

    /**
     * @Route("/year", name="index_year")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $listYears = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Year')
            ->findBy(array(), array('startDate' => 'ASC'))
            ;

        return $this->render('GSAdminBundle:Year:index.html.twig', array(
                    'listYears' => $listYears
        ));
    }

    /**
     * @Route("/year/{id}/edit", name="edit_year", requirements={"id": "\d+"})
     * @Security("is_granted('edit', year)")
     */
    public function editAction(Year $year, Request $request)
    {
        $form = $this->createForm(YearType::class, $year);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Année bien modifiée.');

            return $this->redirectToRoute('gsadmin_view_year', array('id' => $year->getId()));
        }

        return $this->render('GSAdminBundle:Year:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/year/{id}/balance", name="balance_year", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_TREASURER')")
     */
    public function indexTreasurerAction(Year $year, Request $request)
    {
        $listPaidRegistrations = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Registration')
            ->getRegistrationsForYearAndState($year, 'PAID')
            ;
        $listFreeRegistrations = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:Registration')
            ->getRegistrationsForYearAndState($year, 'FREE')
            ;

        $result = array(
            'number' => 0,
            'total' => 0.0,
            'activities' => array(),
        );

        foreach (array_merge($listPaidRegistrations, $listFreeRegistrations) as $registration) {
            $topic = $registration->getTopic();
            $activity = $topic->getActivity();
            $category = $topic->getCategory();
            $price = $category->getPrice();

            if ($registration->getState() != 'FREE') {
                // We need to find all the payment items to know what is the final price paid for the registration
                $listPaymentItems = $this->getDoctrine()->getManager()
                    ->getRepository('GSStructureBundle:PaymentItem')
                    ->findPaidByRegistration($registration)
                    ;
                $item = end($listPaymentItems);
                $discount = $item->getDiscount();
            } else {
                $price = 0.0;
                $discount = null;
            }

            $activityName = $activity->getTitle();
            if (!array_key_exists($activityName, $result['activities'])) {
                $result['activities'][$activityName] = array(
                    'categories' => array(),
                    'number' => 0,
                    'total' => 0.0,
                );
            }

            $categoryName = $category->getName();
            if (!array_key_exists($categoryName, $result['activities'][$activityName]['categories'])) {
                $result['activities'][$activityName]['categories'][$categoryName] = array(
                    'discounts' => array(),
                    'number' => 0,
                    'total' => 0.0,
                );
            }

            if (null !== $discount) {
                $discountName = $discount->getName();
            } elseif ($registration->getState() == 'FREE') {
                $discountName = 'Gratuit (profs)';
            } else {
                $discountName = 'Sans réduction';
            }

            if (!array_key_exists($discountName, $result['activities'][$activityName]['categories'][$categoryName]['discounts'])) {
                $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName] = array(
                    'price' => null === $discount ? $price : $price - $discount->getAmount($price),
                    'number' => 0,
                    'total' => 0.0,
                );
            }

            $result['number'] += 1;
            $result['activities'][$activityName]['number'] += 1;
            $result['activities'][$activityName]['categories'][$categoryName]['number'] += 1;
            $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['number'] += 1;
            $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['total'] +=
                    $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['price'];
            $result['activities'][$activityName]['categories'][$categoryName]['total'] +=
                    $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['price'];
            $result['activities'][$activityName]['total'] +=
                    $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['price'];
            $result['total'] +=
                    $result['activities'][$activityName]['categories'][$categoryName]['discounts'][$discountName]['price'];
        }

        return $this->render('GSAdminBundle:Year:balance.html.twig', array(
            'year' => $year,
            'result' => $result,
        ));
    }

}

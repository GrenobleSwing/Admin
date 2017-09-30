<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\Activity;
use GS\ApiBundle\Entity\Category;
use GS\ApiBundle\Form\Type\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{

    /**
     * @Route("/category/add/{id}", name="add_category", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function postAction(Activity $activity, Request $request)
    {
        $category = new Category();
        $category->setActivity($activity);
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity->addCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Catégorie bien enregistrée.');

            return $this->redirectToRoute('view_category', array('id' => $category->getId()));
        }

        return $this->render('GSApiBundle:Category:add.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/category/{id}", name="view_category", requirements={"id": "\d+"})
     * @Security("is_granted('view', category)")
     */
    public function viewAction(Category $category)
    {
        return $this->render('GSApiBundle:Category:view.html.twig', array(
                    'category' => $category
        ));
    }

    /**
     * @Route("/category", name="index_category")
     * @Security("has_role('ROLE_ORGANIZER')")
     */
    public function indexAction()
    {
        $listCategories = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:Category')
            ->findAll()
            ;

        return $this->render('GSApiBundle:Category:index.html.twig', array(
                    'listCategories' => $listCategories
        ));
    }

    /**
     * @Route("/category/{id}/edit", name="edit_category", requirements={"id": "\d+"})
     * @Security("is_granted('edit', category)")
     */
    public function editAction(Category $category, Request $request)
    {
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Catégorie bien modifiée.');

            return $this->redirectToRoute('view_category', array('id' => $category->getId()));
        }

        return $this->render('GSApiBundle:Category:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/category/{id}/delete", name="delete_category", requirements={"id": "\d+"})
     * @Security("is_granted('delete', category)")
     */
    public function deleteAction(Category $category, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $category->getActivity();
            $activity->removeCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', "La catégorie a bien été supprimée.");

            return $this->redirectToRoute('view_activity', array('id' => $activity->getId()));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('GSApiBundle:Category:delete.html.twig', array(
                    'category' => $category,
                    'form' => $form->createView()
        ));
    }

}

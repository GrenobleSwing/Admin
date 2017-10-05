<?php

namespace GS\AdminBundle\Controller;

use GS\StructureBundle\Entity\User;
use GS\StructureBundle\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user", name="gsadmin_index_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('GSAdminBundle:User:index.html.twig');
    }

    /**
     * @Route("/user/all", name="gsadmin_all_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function allJsonAction()
    {
        $listUsers = $this->getDoctrine()->getManager()
            ->getRepository('GSStructureBundle:User')
            ->findAll()
            ;

        $serializedEntity = $this->get('jms_serializer')->serialize($listUsers, 'json');

        return JsonResponse::fromJsonString($serializedEntity);
    }

    /**
     * @Route("/user/{id}/edit",
     *     name="gsadmin_edit_user",
     *     requirements={"id": "\d+"},
     *     options = { "expose" = true }
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Utilisateur bien modifiée.');

            return $this->redirectToRoute('gsadmin_index_user');
        }

        return $this->render('GSAdminBundle:User:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }


}

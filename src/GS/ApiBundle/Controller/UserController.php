<?php

namespace GS\ApiBundle\Controller;

use GS\ApiBundle\Entity\User;
use GS\ApiBundle\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @Route("/user", name="index_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('GSApiBundle:User:index.html.twig');
    }

    /**
     * @Route("/user/all", name="all_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function allJsonAction()
    {
        $listUsers = $this->getDoctrine()->getManager()
            ->getRepository('GSApiBundle:User')
            ->findAll()
            ;

        $serializedEntity = $this->get('jms_serializer')->serialize($listUsers, 'json');

        return new Response($serializedEntity);
    }

    /**
     * @Route("/user/{id}/edit",
     *     name="edit_user",
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

            return $this->redirectToRoute('index_user');
        }

        return $this->render('GSApiBundle:User:edit.html.twig', array(
                    'form' => $form->createView(),
        ));
    }


}

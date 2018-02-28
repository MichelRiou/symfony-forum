<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Answer;
use AppBundle\Entity\Vote;
use AppBundle\Form\AnswerType;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Entity\Post;

class PostController extends Controller
{

    /**
     * @param $id
     * @Route("/post/{slug}",
     *          name="post_details"
     * )
     * @return Response
     */
    public function detailsAction($slug, Request $request)
    {

        $postRepository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");
        $answerRepository=$this->getDoctrine()->getRepository("AppBundle:Answer");

        $post = $postRepository->findOneBySlug($slug);
        //Formulaire

        $answer = new Answer();

        $answer->setCreatedAt(new \DateTime())
        ->setPost($post);
        $form = $this->createForm(AnswerType::class, $answer);
        //traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            //Redirection pour eviter de rester en post
            return $this->redirectToRoute('post_details', ["slug" => $slug]);
        }

        return $this->render("post/details.html.twig", [
            "post" => $post,
            "answerList" => $post->getAnswers(),
            "newAnswerForm" => $form->createView(),
            "query"=>$answerRepository->getAnwsersByPost($post)->getQuery()->getResult()
        ]);
    }


}
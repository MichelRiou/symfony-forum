<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Post;
use AppBundle\Form\AuthorType;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");
        $postRepository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");
        $authorRepository = $this->getDoctrine()
            ->getRepository("AppBundle:Author");

        $list = $repository->findAll();

        return $this->render('default/index.html.twig',
            [
                "themeList" => $list,
                "lastPosts" => $postRepository->getLastPosts(5)->getResult(),
                "authorSummary" => $authorRepository->getAuthorSummary()->getResult(),
                "yearSummary" => $postRepository->getNumberOfPostsByYear()->getResult(),
                "message" => $this->get("app.hello")->greet()
            ]);
    }

    /**
     * @Route("/theme/{id}", name="theme_details", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function themeAction($id, Request $request)
    {

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");
        $authorrepository = $this->getDoctrine()->getRepository("AppBundle:Author");

        $theme = $repository->find($id);

        $allThemes = $repository->findAll();
        if (!$theme) {
            //throw
        }

        if ($this->getUser() != null) {
            // Création du formulaire
            $post = new Post();
            $post->setTheme($theme)
                ->setCreatedAt(new \DateTime())
                //->setAuthor($authorrepository->findOneByName("Hugo"))
                ->setAuthor($this->getUser());


            $form = $this->createForm(PostType::class, $post, [
                'attr' => ['novalidate' => 'novalidate']
            ]);
            // TRAITEMENT DU FORMULAIRE
            $form->handleRequest($request);

            //Sauvegarde et persistence

            if ($form->isSubmitted() && $form->isValid()) {
                dump($post->getImage());
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);


                if ($post->getImage() instanceof UploadedFile) {
                    $uploadManager = $this->get('stof_doctrine_extensions.uploadable.manager');
                    $uploadManager->markEntityToUpload($post, $post->getImage());
                }
                $em->flush();
                //Redirection pour eviter de rester en post
                return $this->redirectToRoute('theme_details', ["id" => $id]);

            }

            $formView = $form->createView();
        } else {
            $formView = null;
        }
        return $this->render('default/theme.html.twig', [
            "theme" => $theme,
            "postList" => $theme->getPosts(),
            "all" => $allThemes,
            "newPostForm" => $formView
        ]);
    }

    /**
     * @Route("/posts-par-auteur/{id}", name="post_par_auteur")
     * @param Author $author
     */
    public function postsByAuthorAction(Author $author)
    {
        $postrepository = $this->getDoctrine()->getRepository("AppBundle:Post");
        $posts = $postrepository->findByAuthor($author);

        return $this->render('default/posts_by_author.html.twig', [
            "postList" => $posts,
            "author" => $author,
            "condition" => $author->getFullName()
        ]);

    }

    /**
     * @Route("/inscription-auteur/", name="register_author")
     */
    public function registerAuthorAction(Request $request)
        // L'objet de type Resquest récupère la saisie
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        // Hydratation de l'entité à partir de l'objet Resquest
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $author->setPassword(password_hash($author->getPlainPassword(), PASSWORD_BCRYPT));
            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();
            // Message Flash
            $this->addFlash('info', 'Vous êtes inscrit');
            //dump($author);
            // Authentification de l'utilisateur qui vient de s'inscrire

            // Création d'un token à partir des données de l'auteur
            $token = new UsernamePasswordToken($author, null, 'main', $author->getRoles());
            // Stockage du token en session
            $this->get('security.token_storage')->setToken($token);
            return $this->redirectToRoute("homepage");
        }
        return $this->render('createAuthor.html.twig', [
            "form" => $form->createView()
        ]);


    }

    /**
     * @Route("posts-by-annee/{year}",name="post_by_year",requirements={"year"="\d{4}"})
     * @param $year
     */
    public function postsByYearsAction($year)
    {
        $postrepository = $this->getDoctrine()->getRepository("AppBundle:Post");
        $posts = $postrepository->getPostsByYear($year)->getResult();
        return $this->render("default/posts_by_author.html.twig", [

            "condition" => "l'année $year",
            "postList" => $posts
        ]);

    }

    /**
     * @Route("/admin-login",name="admin_login_route")
     */
    public function adminLoginAction()
    {
        //Récupération des erreurs éventuelles
        $securityUtils = $this->get('security.authentication_utils');
        return $this->render("default/login-form.html.twig",
            ["action" => $this->generateUrl("admin_check_route"),
                "error" => $securityUtils->getLastAuthenticationError(),
                "userName" => $securityUtils->getLastUsername()]
        );

    }

    /**
     * @Route("/author-login",name="author_login_route")
     */
    public function authorLoginAction()
    {
        //Récupération des erreurs éventuelles
        $securityUtils = $this->get('security.authentication_utils');
        return $this->render("default/login-form.html.twig",
            ["action" => $this->generateUrl("author_check_route"),
                "error" => $securityUtils->getLastAuthenticationError(),
                "userName" => $securityUtils->getLastUsername()]
        );

    }


    
}

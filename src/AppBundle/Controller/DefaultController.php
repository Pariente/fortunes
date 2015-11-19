<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Fortune;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Moderated;
use AppBundle\Form\FortuneType;
use AppBundle\Form\CommentType;
use Pagerfanta\Pagerfanta;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
      // Display all the stories
      $pagerfanta = new Pagerfanta($this->getDoctrine()->getRepository("AppBundle:Fortune")->findLast());
      $pagerfanta->setMaxPerPage(3);
      return $this->render('default/index.html.twig', ["stories" => $pagerfanta] );
    }

    /**
     * @Route("/last", name="last")
     */
    public function lastAction(Request $request)
    {
      // Display all the stories
      $pagerfanta = new Pagerfanta($this->getDoctrine()->getRepository("AppBundle:Fortune")->findLast());
      $pagerfanta
      ->setMaxPerPage(10)
      ->setCurrentPage($request->get("page", 1));
      return $this->render('default/last.html.twig', ["stories" => $pagerfanta] );
    }

    /**
     * @Route("/best", name="best")
     */
    public function bestAction(Request $request)
    {
      // Display all the stories
      return $this->render('default/best.html.twig', ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest(10, "DESC")] );
    }

    /**
     * @Route("/worst", name="worst")
     */
    public function worstAction(Request $request)
    {
      // Display all the stories
      return $this->render('default/worst.html.twig', ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest(10, "ASC")] );
    }

    /**
     * @Route("/random", name="random")
     */
    public function randomAction(Request $request)
    {
      $id = $this->getDoctrine()->getRepository("AppBundle:Fortune")->findRandom();
      return $this->redirectToRoute('story', ["id" => $id]);
    }

    public function showBestRatedAction($nb)
    {
      // Display the last N stories
      return $this->render('default/showBestRated.html.twig', ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest($nb, "DESC")]);
    }

    /**
     * @Route("/byauthor/{author}", name="byauthor")
     */
    public function showByAuthorAction($author)
    {
      // Display the stories of the author
      return $this->render('default/showByAuthor.html.twig', [
          "stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findByAuthor($author),
          "author" => $author
        ]);
    }

    /**
     * @Route("/story/{id}", name="story")
     */
    public function showStoryAction($id, Request $request)
    {
      // Display the stories of the author
      $fortune = $this->getDoctrine()->getRepository("AppBundle:Fortune")->find($id);
      $form = $this->createForm(new CommentType, new Comment);

      $form->handleRequest($request);

      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $comment = $form->getData();
        $comment->setFortune($fortune);
        $em->persist($comment);
        $em->flush();

        return $this->redirect($this->getRequest()->headers->get('referer'));
      }
      return $this->render('default/showStory.html.twig', [
        "story" => $fortune,
        "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function createAction(Request $request)
    {
      // Create a form
      $form = $this->createForm(new FortuneType, new Moderated);

      $form->handleRequest($request);

      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($form->getData());
        $em->flush();

        return $this->redirectToRoute('homepage');
      }

      return $this->render('default/create.html.twig', ["form" => $form->createView()]);
    }

    /**
     * @Route("/moderate", name="moderate")
     */
    public function moderateAction(Request $request)
    {
      // Display quotes to be moderated
      $pagerfanta = new Pagerfanta($this->getDoctrine()->getRepository("AppBundle:Moderated")->findLast());
      $pagerfanta
      ->setMaxPerPage(10)
      ->setCurrentPage($request->get("page", 1));
      return $this->render('default/moderate.html.twig', ["stories" => $pagerfanta] );
    }

    /**
     * @Route("/moderate/delete/{id}", name="moderate/delete")
     */

     public function deleteModerated($id)
     {
       $em = $this->getDoctrine()->getManager();
       $moderated = $em->getRepository("AppBundle:Moderated")->find($id);
       $em->remove($moderated);
       $em->flush();

       return $this->redirect($this->getRequest()->headers->get('referer'));
     }

     /**
      * @Route("/moderate/validate/{id}", name="moderate/validate")
      */

      public function validateModerated($id)
      {
        $em = $this->getDoctrine()->getManager();
        $moderated = $em->getRepository("AppBundle:Moderated")->find($id);
        $em->remove($moderated);
        $em->flush();

        return $this->redirect($this->getRequest()->headers->get('referer'));
      }

    /**
     * @Route("/voteup/story/{id}", name="voteup/story")
     */

     public function voteUpStoryAction($id)
     {
       $fortune = $this->getDoctrine()->getRepository("AppBundle:Fortune")->find($id);

       if (!$this->get('session')->has("hasVotedFor".$id)) {
         $this->get('session')->set("hasVotedFor".$id, "value");
         $fortune->voteUp();
         $this->getDoctrine()->getManager()->flush();
       }

       return $this->redirect($this->getRequest()->headers->get('referer'));
     }

     /**
      * @Route("/votedown/story/{id}", name="votedown/story")
      */

      public function voteDownStoryAction($id)
      {
        $fortune = $this->getDoctrine()->getRepository("AppBundle:Fortune")->find($id);

        if (!$this->get('session')->has("hasVotedFor".$id)) {
          $this->get('session')->set("hasVotedFor".$id, "value");
          $fortune->voteDown();
          $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->getRequest()->headers->get('referer'));
      }

      /**
       * @Route("/voteup/comment/{id}", name="voteup/comment")
       */

       public function voteUpCommentAction($id)
       {
         $fortune = $this->getDoctrine()->getRepository("AppBundle:Comment")->find($id);
         $fortune->voteUp();
         $this->getDoctrine()->getManager()->flush();

         return $this->redirect($this->getRequest()->headers->get('referer'));
       }

       /**
        * @Route("/votedown/comment/{id}", name="votedown/comment")
        */

        public function voteDownCommentAction($id)
        {
          $fortune = $this->getDoctrine()->getRepository("AppBundle:Comment")->find($id);
          $fortune->voteDown();
          $this->getDoctrine()->getManager()->flush();

          return $this->redirect($this->getRequest()->headers->get('referer'));
        }
}

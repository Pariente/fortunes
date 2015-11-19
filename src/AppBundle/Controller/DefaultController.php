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
      $session = $this->get('session')->all();
      $pagerfanta = new Pagerfanta($this->getDoctrine()->getRepository("AppBundle:Fortune")->findLast());
      $pagerfanta->setMaxPerPage(3);
      return $this->render('default/index.html.twig', ["stories" => $pagerfanta, "session" => $session] );
    }

    /**
     * @Route("/last", name="last")
     */
    public function lastAction(Request $request)
    {
      // Display all the stories
      $session = $this->get('session')->all();
      $pagerfanta = new Pagerfanta($this->getDoctrine()->getRepository("AppBundle:Fortune")->findLast());
      $pagerfanta
      ->setMaxPerPage(10)
      ->setCurrentPage($request->get("page", 1));
      return $this->render('default/last.html.twig', ["stories" => $pagerfanta, "session" => $session] );
    }

    /**
     * @Route("/best", name="best")
     */
    public function bestAction(Request $request)
    {
      // Display all the stories
      $session = $this->get('session')->all();
      return $this->render('default/best.html.twig',
      ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest(10, "DESC"),
      "session" => $session] );
    }

    /**
     * @Route("/worst", name="worst")
     */
    public function worstAction(Request $request)
    {
      // Display all the stories
      $session = $this->get('session')->all();
      return $this->render('default/worst.html.twig',
      ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest(10, "ASC"),
      "session" => $session] );
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
      $session = $this->get('session')->all();
      return $this->render('default/showBestRated.html.twig',
      ["stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findBest($nb, "DESC"),
      "session" => $session]);
    }

    /**
     * @Route("/byauthor/{author}", name="byauthor")
     */
    public function showByAuthorAction($author)
    {
      // Display the stories of the author
      $session = $this->get('session')->all();
      return $this->render('default/showByAuthor.html.twig', [
          "stories" => $this->getDoctrine()->getRepository("AppBundle:Fortune")->findByAuthor($author),
          "author" => $author,
          "session" => $session
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
      $session = $this->get('session')->all();

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
        "session" => $session,
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

     public function deleteModeratedAction($id)
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

      public function validateModeratedAction($id)
      {
        $em = $this->getDoctrine()->getManager();

        $moderated = $em->getRepository("AppBundle:Moderated")->find($id);

        $newFortune = new Fortune();
        $newFortune->setTitle($moderated->getTitle());
        $newFortune->setAuthor($moderated->getAuthor());
        $newFortune->setCreatedAt($moderated->getCreatedAt());
        $newFortune->setContent($moderated->getContent());

        $em->persist($newFortune);
        $em->remove($moderated);
        $em->flush();

        return $this->redirectToRoute('moderate');
      }

      /**
       * @Route("/edit/{id}", name="edit")
       */

       public function editFortuneAction($id, Request $request)
       {
         $em = $this->getDoctrine()->getManager();

         $fortune = $em->getRepository("AppBundle:Fortune")->find($id);

         // Create a form
         $form = $this->createForm(new FortuneType, $fortune);

         $form->handleRequest($request);

         if ($form->isValid()) {
           $editedFortune = $form->getData();
           $fortune->setTitle($editedFortune->getTitle());
           $fortune->setAuthor($editedFortune->getAuthor());
           $fortune->setContent($editedFortune->getContent());
           $em->flush();

           return $this->redirectToRoute('homepage');
         }

         return $this->render('default/edit.html.twig',
         ["form" => $form->createView(),
         "stories" => $fortune]);
       }

    /**
     * @Route("/voteup/story/{id}", name="voteup/story")
     */

     public function voteUpStoryAction($id)
     {
       $fortune = $this->getDoctrine()->getRepository("AppBundle:Fortune")->find($id);

       if (!$this->get('session')->has("hasVotedFor".$id)) {
         $this->get('session')->set("hasVotedFor".$id, true);
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

        if (!$this->get('session')->has("hasVotedAgainst".$id)) {
          $this->get('session')->set("hasVotedAgainst".$id, true);
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

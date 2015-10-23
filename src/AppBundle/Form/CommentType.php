<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentType extends AbstractType
{

  public function getName()
  {
    return "Comment";
  }

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('author', 'text', ['label' => false, 'attr' => ['placeholder' => 'Auteur']])
      ->add('content', 'textarea', ['label' => false, 'attr' => ['placeholder' => 'Commentaire']])
      ->add('submit', 'submit', ['label' => 'Commenter']);
  }

}

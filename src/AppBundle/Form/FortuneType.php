<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FortuneType extends AbstractType
{

  public function getName()
  {
    return "Fortune";
  }

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('title', 'text',  array('label' => 'Titre'))
      ->add('author', 'text',  array('label' => 'Auteur'))
      ->add('content', 'textarea',  array('label' => 'Quote'))
      ->add('submit', 'submit',  array('label' => 'Publier'));
  }

}

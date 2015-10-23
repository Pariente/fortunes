<?php

namespace AppBundle\Entity;

use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * FortuneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FortuneRepository extends \Doctrine\ORM\EntityRepository
{
  public function findLast()
  {
    $queryBuilder = $this->createQueryBuilder('f')
      ->orderBy("f.id", "DESC");

    return new DoctrineORMAdapter($queryBuilder);
  }

  public function findBest($nb, $order)
  {
    return
      $this->createQueryBuilder('f')
      ->setMaxResults($nb)
      ->orderBy("f.upVote - f.downVote", $order)
      ->getQuery()
      ->getResult();
  }

  public function findByAuthor($author)
  {
    return
      $this->createQueryBuilder('f')
      ->orderBy("f.createdAt", "DESC")
      ->where('f.author = :author')
      ->setParameter('author', $author)
      ->getQuery()
      ->getResult();
  }

  public function findRandom()
  {
    $count = $this->createQueryBuilder('f')
      ->select('COUNT(f)')
      ->getQuery()
      ->getSingleScalarResult();

    return
      $this->createQueryBuilder('f')
      ->select('f.id')
      ->setFirstResult(rand(1, $count))
      ->setMaxResults(1)
      ->getQuery()
      ->getSingleScalarResult();
  }

}
<?php

namespace TechForumBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;

/**
 * AnswerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnswerRepository extends \Doctrine\ORM\EntityRepository
{
    public function __construct(EntityManagerInterface $em,
                                Mapping\ClassMetadata $metadata = null)
    {
        parent::__construct ($em,
            $metadata == null ?
                new Mapping\ClassMetadata(Answer::class) :
                $metadata
        );
    }

    public function insert(Answer $answer): bool
    {
        try {
            $this->_em->persist($answer);
            $this->_em->flush();
            return true;
        } catch ( OptimisticLockException $e ) {
            return false;
        } catch ( ORMException $e ) {
            return false;
        }
    }

    public function merge(Answer $answer): bool
    {
        try {
            $this->_em->merge($answer);
            $this->_em->flush();
            return true;
        } catch ( OptimisticLockException $e ) {
            return false;
        } catch ( ORMException $e ) {
            return false;
        }
    }

    public function remove(Answer $answer): bool
    {
        try {
            $this->_em->remove($answer);
            $this->_em->flush();
            return true;
        } catch ( OptimisticLockException $e ) {
            return false;
        } catch ( ORMException $e ) {
            return false;
        }
    }

    public function findAnswersByQuestion(Question $question): array
    {
        return
            $this
                ->createQueryBuilder('answers')
                ->leftJoin('answers.likers', 'likers')
                ->where('answers.question = :question')
                ->setParameter('question', $question)
                ->groupBy('answers.id')
                ->addOrderBy('answers.isVerified', 'DESC')
                ->addOrderBy('COUNT(likers.id)', 'DESC')
                ->addOrderBy('answers.dateAdded', 'DESC')
                ->getQuery()
                ->getResult();
    }

    public function findAnswersByCurrentUser(User $user)
    {
        return
            $this
                ->createQueryBuilder('answers')
                ->where('answers.author = :user')
                ->setParameter('user', $user)
                ->addOrderBy('answers.dateAdded', 'DESC')
                ->getQuery()
                ->getResult();
    }
}

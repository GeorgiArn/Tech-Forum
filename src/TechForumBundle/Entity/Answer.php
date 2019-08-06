<?php

namespace TechForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table(name="answers")
 * @ORM\Entity(repositoryClass="TechForumBundle\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TechForumBundle\Entity\User", inversedBy="answers")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="TechForumBundle\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(name="quesiton_id", referencedColumnName="id")
     */
    private $question;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdded", type="datetime")
     */
    private $dateAdded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    private $isVerified;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="TechForumBundle\Entity\User")
     * @ORM\JoinTable(name="answers_likes",
     *     joinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="liker_id",referencedColumnName="id")}
     *     )
     */
    private $likers;

    public function __construct()
    {
        $this->dateAdded = new \DateTime('now');
        $this->isVerified = false;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Answer
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return Answer
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Question
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * @param Question $question
     * @return Answer
     */
    public function setQuestion(Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Set dateAdded.
     *
     * @param \DateTime $dateAdded
     *
     * @return Answer
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @return ArrayCollection
     */
    public function getLikers()
    {
        return $this->likers;
    }

    /**
     * @param ArrayCollection $likers
     */
    public function setLikers($likers)
    {
        $this->likers = $likers;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addLike(User $user)
    {
        $this->likers[] = $user;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeLike(User $user)
    {
        $this->likers->removeElement($user);

        return $this;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isLikedBy(User $user)
    {
        foreach ($this->getLikers() as $liker) {
            if ( $user->getId() === $liker->getId() ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     */
    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }
}

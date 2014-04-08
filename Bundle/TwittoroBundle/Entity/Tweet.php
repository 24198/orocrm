<?php

namespace Tfone\Bundle\TwittoroBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * Tweet item
 *
 * @ORM\Table(name="tfone_twittoro_tweet")
 * @ORM\Entity(repositoryClass="Tfone\Bundle\TwittoroBundle\Entity\Repository\TweetRepository")
 * @Oro\Loggable
 * @Config(
 *  defaultValues={
 *      "security"={
 *          "type"="ACL"
 *      },
 *      "entity"={"icon"="icon-tag"},
 *      "dataaudit"={"auditable"=true}
 *  }
 * )
 */
class Tweet
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     * @Oro\Versioned
     * @JMS\Type("string")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="tweet", type="string", length=140, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("string")
     */
    protected $tweet;

    /**
     * @var integer
     *
     * @ORM\Column(name="retweets", type="integer", length=10, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("integer")
     */
    protected $retweets;

    /**
     * @var \DateTime $tweetStamp
     *
     * @ORM\Column(name="tweet_stamp", type="datetime", nullable=true)
     * @JMS\Type("DateTime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $tweetStamp;
    
    /**
     * @var string
     *
     * @ORM\Column(name="hashtag", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("string")
     */
    protected $hashtag;
            
    /**
     * @var string
     *
     * @ORM\Column(name="max_id", type="string", length=255)
     * @Oro\Versioned
     * @JMS\Type("string")
     */
    protected $maxId;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     * @JMS\Type("DateTime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Type("DateTime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $updatedAt;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $tweet
     */
    public function setTweet($tweet)
    {
        $this->tweet = $tweet;
    }

    /**
     * @return string
     */
    public function getTweet()
    {
        return $this->tweet;
    }

    /**
     * @param int $retweets
     */
    public function setRetweets($retweets)
    {
        $this->retweets = $retweets;
    }

    /**
     * @return int count of reteets
     */
    public function getRetweets()
    {
        return $this->retweets;
    }    
    
    /**
     * @param \DateTime $tweetStamp
     */
    public function setTweetStamp($tweetStamp)
    {
        $this->tweetStamp = $tweetStamp;
    }

    /**
     * @return \DateTime
     */
    public function getTweetStamp()
    {
        return $this->tweetStamp;
    }    
    
    /**
     * @param string $hashtag
     */
    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
    }

    /**
     * @return string
     */
    public function getHashtag()
    {
        return $this->hashtag;
    }    

    /**
     * @param string $maxId
     */
    public function setMaxId($maxId)
    {
        $this->maxId = $maxId;
    }

    /**
     * @return string
     */
    public function getMaxId()
    {
        return $this->maxId;
    }    
    
    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
<?php

namespace Tfone\Bundle\TwittoroBundle\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;

use Tfone\Bundle\TwittoroBundle\Helpers\Formatter;
use Tfone\Bundle\TwittoroBundle\Provider\RESTTransport;
use Tfone\Bundle\TwittoroBundle\Entity\Tweet;

/**
 * Takes care of the following tasks:
 * 
 * - check if there is a latest tweet (maxId for tweet)
 * - make an api call via the RESTTransport
 * - if there no errors process the tweets and import them
 * - if there are errors let the user know about them 
 * - if there are no tweets to be updated, let the user know 
 */
class TweetProcessor {
    
    /** @var EntityManager */
    protected $em;

    /** @var Formatter */
    protected $helper;
    
    /** @var OutputLogger */
    protected $logger;

    /** @var RESTTransport */
    protected $transport;
    
    /** @var Registry */
    protected $doctrine;
    
    /** @var Translator */
    protected $translator;
    
    /**
     * Construct the TweetProcessor
     * 
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Oro\Bundle\TranslationBundle\Translation\Translator $translator
     * @param \Tfone\Bundle\TwittoroBundle\Helpers\Formatter $helper
     * @param \Tfone\Bundle\TwittoroBundle\Provider\RESTTransport $transport
     */
    public function __construct(
        Registry $doctrine,
        EntityManager $em,
        Translator $translator,
        Formatter $helper,
        RESTTransport $transport
    ) {    
        $this->doctrine = $doctrine;
        $this->em = $em;
        $this->translator = $translator;
        $this->helper = $helper;
        $this->transport = $transport;
    }
    
    /**
     * Startup the process for importing the tweets. Make the api call
     * and let the importTweets() function import all the tweets from the json string if any
     * 
     * @param array $config the config are all the config options from the system configuration
     */
    public function process($config) {               
        $config['maxid'] = $maxId = null;
        if($this->hashtagExists($config['hashtag']) > 0) {
           $config['maxid'] = $maxId = $this->getMaxIdForHashtag($config['hashtag']);
        }

        $tweetJsonData = $this->transport->call($config);
        $tweetData = json_decode($tweetJsonData);

        // if some error occured throw a new exception with the error code and the message
        // received from the api call.
        if(isset($tweetData->errors)) {
            foreach($tweetData->errors as $error){
                throw new \Exception('['.$error->code.'] => '.$error->message);
            }
        }   
        // if there are no tweets let the user know
        if(count($tweetData->statuses) == 0) {
            $this->logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.no_new_tweets_found'). ' '. $this->helper->formatHashtag($config['hashtag']));
            return;
        }
        $this->importTweets($tweetData, $config['hashtag']);

        $this->logger->notice(count($tweetData->statuses). ' ' .$this->getTranslator()->trans('tfone.twittoro.update_tweets.tweets_have_been_created'));
 
    }
    /**
     * Import the tweet data and store the values in the database.
     * 
     * @param type $tweetData json decoded data for processing the tweet and send it of to the db
     * @param string $hashtag hashtag from config, given by the user
     */
    private function importTweets($tweetData, $hashtag) {
        //get entitymanager
        $entityManager = $this->em;
        
        $hashtag = $this->helper->formatHashtag($hashtag);  
        //$tweetData is an object of the stdClass..
        //can't handle this object as an array..
        foreach($tweetData->statuses as $tweet) {          
           //creating new tweet entity
           $tweetEntity = new Tweet();
           
           $tweetEntity->setUsername(mb_convert_encoding($tweet->user->screen_name, "UTF-8"));
           $tweetEntity->setTweet(mb_convert_encoding($tweet->text, "UTF-8"));
           $tweetEntity->setRetweets((int)$tweet->retweet_count);          
           $tweetEntity->setTweetStamp(new \DateTime($tweet->created_at));
           $tweetEntity->setHashtag(mb_convert_encoding($hashtag, "UTF-8"));
           $tweetEntity->setMaxId($tweet->id_str);
           $tweetEntity->setCreatedAt(new \DateTime());
           $tweetEntity->setUpdatedAt(new \DateTime());

           try {
                
                $entityManager->persist($tweetEntity);
                $entityManager->flush();
                
                //do some logging if the record has been updated.
                $this->logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.record_created') . ' ' . $tweet->user->screen_name . ' ' . $tweet->text. ' '. $tweet->retweet_count . ' '. $tweet->created_at. ' '. $hashtag);
 
           } catch (Exception $e) {
               $this->logger->notice('Something went wrong');
               $this->logger->notice($e->getMessage());
               return;
           }           
       }
    } 
    
    /**
     * Get the translator instance
     * 
     * @return Translator The translator used for translating the messages
     */
    private function getTranslator() {
        return $this->translator;
    }

    /**
     * Get the doctrine registry
     * 
     * @return Registry The doctrine registry a.k.a $this->getContainer('doctrine')
     */
    private function getDoctrine() {
        return $this->doctrine;
    }
    
    /**
     * Check whether an hashtag already exists in the databse.
     * 
     * @param string $hashtag formatted hashtag as stored in the db
     * @return scalar hashtag exists TODO: proper documentation for this value 
     */
    private function hashtagExists($hashtag) {
        return $this->getDoctrine()->getRepository('TfoneTwittoroBundle:Tweet')
                        ->findOneByHashtag($hashtag);
    }
    
    /**
     * Get the maximum id for the specific hashtag. This will be used as an indicator
     * for where the api should start looking for new tweets (newer then the maximum id, since the maximum id
     * is the the latest tweet)
     * 
     * @param string $hashtag formatted hashtag as stored in the db
     * @return string maxId from the db. 
     */
    private function getMaxIdForHashtag($hashtag) {
        return $this->getDoctrine()->getRepository('TfoneTwittoroBundle:Tweet')
                       ->getMaxIdForHashtag($hashtag);
    }
    
    /**
     * set output logger for showing some messages in the console
     * when the command is run manually.
     * @param OutputInterface $output
     */
    public function setLogger($output)
    {
        $this->logger = new OutputLogger($output);
    }
}

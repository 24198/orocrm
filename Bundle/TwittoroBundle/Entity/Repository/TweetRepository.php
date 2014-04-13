<?php

namespace Tfone\Bundle\TwittoroBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Tfone\Bundle\TwittoroBundle\Helpers\Formatter; 

class TweetRepository extends EntityRepository
{
    /**
     * Get get tweets the tweet count grouped by the username
     * Some usernames have a couple of tweets and those need 
     * to be on top of the list. The list is ordered by tweet_count
     * per username in a descending order (max count first).
     *
     * @param $aclHelper AclHelper
     * @param string $hashtag unformatted hashtag given by the user
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     * Query for getting the tweets by username
     * SELECT  `username` , COUNT(  `tweet` ) AS  `tweet_count` 
     * FROM  `tfone_twittoro` 
     * GROUP BY  `username` 
     * ORDER BY  `tweet_count` DESC 
     */
    public function getTweetsByUsername(AclHelper $aclHelper, $hashtag)
    {
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('tweets');
        $qb->select('tweets.username', 'COUNT(tweets.tweet) as tweet_count')
             ->where('tweets.hashtag = :hashtag')
             ->setParameter('hashtag', $hashtag)
             ->groupBy('tweets.username')
             ->orderBy('tweet_count', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        $resultData = [];
        $labels = [];
        $counter = 1;
        foreach ($data as $index => $dataValue) {
            if($counter < 8) {
                $resultData[$index] = [$index, (int)$dataValue['tweet_count']];
                $labels[$index] = $dataValue['username'];
                $counter++;
            }
        }

        return ['hashtag' => $hashtag, 'data' => $resultData, 'labels' => $labels];
    }

    /**
     * Query for getting the tweets over time
     *
     * @param $aclHelper AclHelper
     * @param string $hashtag unformatted hashtag given by the user
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     * 
     * below is the original select statement, but it didn't work since there is no native support for the DATE_FORMAT() function
     * SELECT DATE_FORMAT(`tweet_stamp`, '%m-%d') AS  `tweetStamp` ,  `tweet_stamp` , COUNT(  `tweet` ) AS  `tweet_count` 
     * 
     * SELECT SUBSTRING(`tweet_stamp`,6,5) AS  `tweetStamp` ,  `tweet_stamp` , COUNT(  `tweet` ) AS  `tweet_count` 
     * FROM  `tfone_twittoro_tweet` 
     * GROUP BY  `tweetStamp` 
     * ORDER BY  `tweet_stamp` DESC 
     * LIMIT 0 , 10  
     */
    public function getTweetsOverTime(AclHelper $aclHelper, $hashtag, $timefilter)
    {
        //first store the user's default time zone so we can set it after we're done sniffing
        //through the database
        $defaultTimeZone = date_default_timezone_get();
       
        //we want to look for a value in the UTC timezone since the dates are stored this way :)
        //@reference https://dev.mysql.com/doc/refman/5.0/en/datetime.html
        date_default_timezone_set('UTC');
        
        //minute (last minute, option is meant for trends on twitter), hour (past hour), day (last day), week (last week)
        //limits are, 1, 7, 24 and 60
        switch($timefilter):
            case 'minute':
                $subStringCond = 'SUBSTRING(tt.tweetStamp,1,19) AS tweet_stamp';
                $limit = date('Y-m-d H:i:s', (strtotime('-1 minutes')));
                break;
            case 'hour':
                $subStringCond = 'SUBSTRING(tt.tweetStamp,1,16) AS tweet_stamp';
                $limit = date('Y-m-d H:i:s', (strtotime('-1 hour')));
                break;
            case 'day':
                $subStringCond = 'SUBSTRING(tt.tweetStamp,1,13) AS tweet_stamp';
                $limit = date('Y-m-d H:i:00', strtotime('-24 hour'));
                break;
            case 'week':
                $subStringCond = 'SUBSTRING(tt.tweetStamp,1,10) AS tweet_stamp';
                $limit = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            default:
                $subStringCond = 'SUBSTRING(tt.tweetStamp,1,10) AS tweet_stamp';
                $limit = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
        endswitch;
        
        $hashtag = $this->formatHashtag($hashtag);

        $qb = $this->createQueryBuilder('tt');
        $qb->select($subStringCond, 'COUNT(tt.tweet) AS tweet_count, tt.tweetStamp')
             ->where('tt.hashtag = :hashtag')
             ->andWhere('tt.tweetStamp >= :limit')
             ->setParameter('hashtag', $hashtag)
             ->setParameter('limit', $limit)   
             ->groupBy('tweet_stamp')
             ->orderBy('tt.tweetStamp', 'ASC');
        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        //add a fake value as minimum of the resultData array
        //otherwise the number of array indices are not corresponding (labels && resultData)
        $resultData[] = [ 0 , 0 ];
        
        //add limit as minimum of the labels array
        //format limit to correct date time and format it back as an string
        $limitDate = new \DateTime($limit);
        $limitDate->setTimezone(new \DateTimeZone($defaultTimeZone));
        $labels = [0 => $limitDate->format('Y-m-d H:i:s')];
        
        foreach ($data as $index => $dataValue) {
            $index++;
            $resultData[$index] = [$index, (int)$dataValue['tweet_count']];
            $date = $dataValue['tweetStamp'];          
            $date->setTimezone(new \DateTimeZone($defaultTimeZone));
            $labels[$index] = $date->format('Y-m-d H:i:s');

        }
        //reset the timezone to user specific time zone
        date_default_timezone_set($defaultTimeZone);
        return ['hashtag' => $hashtag, 'data' => $resultData, 'labels' => $labels];
    }    
    
    /**
     * Find a single result of a given hashtag
     * @param string $hashtag unformatted hashtag given by the user
     * @return result if any
     */
    public function findOneByHashtag($hashtag) {
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('h');
        $result = $qb->select('COUNT(h)')
            ->where('h.hashtag = :hashtag')
            ->setParameter('hashtag', $hashtag)    
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    /**
     * Get the max id for a given hashtag
     * the max id represents the latest tweet known in the db, if any
     * 
     * @param string $hashtag unformatted hashtag given by the user
     * @return result if any
     */
    public function getMaxIdForHashtag($hashtag) {
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('h');
        $result = $qb->select('h.maxId')
            ->where('h.hashtag = :hashtag')
            ->setParameter('hashtag', $hashtag)
            ->orderBy('h.maxId', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }
    
    /**
     * Get the total number of tweets for the given hashtag.
     * 
     * @param \Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper $aclHelper
     * @param string $hashtag unformatted hashtag given by the user
     * @return array
     *  [
     *      'numboftweets' => value
     *      'label' => label
     *  ]
     */
    public function getNumberOfTweets(AclHelper $aclHelper, $hashtag) {
        
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.tweet) as tweet_count')
             ->where('t.hashtag = :hashtag')
             ->setParameter('hashtag', $hashtag)
             ->orderBy('tweet_count', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getSingleScalarResult();
        
        if($data !== null) {
            $resultData[0] = [ 'numboftweets' => (int)$data, 'label' => 'Number of tweets for '.$hashtag];
        } else {
            $resultData[0] = [ 'numboftweets' => null ];
        }
        return $resultData;
    }
    
    /**
     * Get the latest tweet
     * 
     * @param \Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper $aclHelper
     * @param string $hashtag unformatted hashtag given by the user
     * @return array
     *  [
     *      'latestTweet' => value
     *      'label' => label
     *      'username' => value
     *  ]
     */ 
    public function getLatestTweet(AclHelper $aclHelper, $hashtag) {
        
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('lt');
        $qb->select('lt.username', 'lt.tweet')
             ->where('lt.hashtag = :hashtag')
             ->setParameter('hashtag', $hashtag)
             ->orderBy('lt.tweetStamp', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        $counter = 0;
        $resultData = []; 
        foreach ($data as $record) {
            if($counter < 1 && !isset($record)) { 
                $resultData[$counter] = [ 'latestTweet' => null ];
            } else if($counter < 1) {
                $resultData[$counter] = [ 'latestTweet' => $record['tweet'], 'label' => 'Latest tweet by', 'username' => $record['username']];
                $counter++;
            }
        }        
     
        return $resultData;
    }    

    /**
     * Get the tweeter username which has the most tweets for the given hashtag
     * 
     * @param \Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper $aclHelper
     * @param $hashtag unformatted hashtag given by the user
     * @return array
     *  [
     *      'topTweeter' => value
     *      'label' => label
     *      'tweetCount' => value
     *  ]
     */
    public function getTopTweeter(AclHelper $aclHelper, $hashtag) {
        
        $hashtag = $this->formatHashtag($hashtag);
        $qb = $this->createQueryBuilder('tt');
        $qb->select('tt.username','COUNT(tt.tweet) as tweet_count')
             ->where('tt.hashtag = :hashtag')
             ->setParameter('hashtag', $hashtag)
             ->groupBy('tt.username')
             ->orderBy('tweet_count', 'DESC');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();
        
        $counter = 0;
        $resultData = [];
        foreach ($data as $record) {
            if($counter < 1 && !isset($record)) { 
                $resultData[$counter] = [ 'topTweeter' => null, 'hashtag' => $hashtag ];
            } else if($counter < 1) {
                $resultData[$counter] = [ 'topTweeter' => $record['username'], 'label' => 'Top tweeter', 'tweetCount' => $record['tweet_count'], 'hashtag' => $hashtag];
                $counter++;
            }
        }        
     
        return $resultData;
    }    

    /**
     * Returns all known hashtags
     *
     * @return array
     *      key = formatted hashtag
     *      value = hashtag
     */
    public function getAllTweets() {
        $qb = $this->createQueryBuilder('at');
        $qb->select('at.hashtag')
            ->orderBy('at.hashtag', 'DESC')
            ->distinct();
                
        
        $data = $qb->getQuery()         
                    ->getResult();
        
        $hashtags = [];
             
        foreach($data as $record) {
            $tmp = str_replace('#', '', $record['hashtag']);
            $hashtags[$tmp] = $record['hashtag'];
            
        }
        
        return $hashtags;
    }
    
    
    private function formatHashtag($hashtag) {
        $formatter = new Formatter();
        
        return $formatter->formatHashtag($hashtag);
    }
}

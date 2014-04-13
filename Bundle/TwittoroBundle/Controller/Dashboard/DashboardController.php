<?php

namespace Tfone\Bundle\TwittoroBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/tweets_username/chart/{widget}",
     *      name="tfone_twittoro_dashboard_tweets_by_username_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:tweetsByUsername.html.twig")
     */
    public function tweetsByUsernameAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTweetsByUsername($this->get('oro_security.acl_helper'), $this->get('oro_config.user')->get('tfone_twittoro.update_tweets_hashtag'))
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }

    /**
     * @Route(
     *      "/tweets_over_time/chart/{widget}/{timeFilter}",
     *      name="tfone_twittoro_dashboard_tweets_over_time_chart",
     *      requirements={"widget"="[\w_-]+", "timefilter"="[\w-]+"},
     *      defaults={"timefilter" = ""}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:tweetsOverTime.html.twig")
     */
    public function tweetsOverTimeAction($widget, $timeFilter = null)
    {
        if (empty($timeFilter)) {
            $timeFilter = 'hour';
        } 
        
        if ($this->get('request')->get('switch_period', false)) {
            $this->get('session')->set('saved_timefilter', $timeFilter);
        } else {
            $timeFilter = $this->get('session')->get('saved_timefilter', $timeFilter);
        }

        $template = 'TfoneTwittoroBundle:Dashboard:tweetsOverTime.html.twig';
        
        $arrayMerge = array_merge( 
                [
                    'seletedTimeFilter' => $timeFilter,
                    'timeFilters'   => array_reverse(['hour' => 'Last hour', 'day' => 'Past 24 hours', 'week' => 'Last week'])
                ],
                [
                    'items' => $this->getDoctrine()
                            ->getRepository('TfoneTwittoroBundle:Tweet')
                            ->getTweetsOverTime($this->get('oro_security.acl_helper'), $this->get('oro_config.user')->get('tfone_twittoro.update_tweets_hashtag'), $timeFilter)
                ],
                $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
            );
        
        return $this->render(
            $template,
            $arrayMerge
        );
    }    
    
    /**
     * @Route(
     *      "/tweets_username/widget",
     *      name="tfone_twittoro_dashboard_number_of_tweets_widget"
     *      
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:numberOfTweets.html.twig")
     */
    public function numberOfTweetsAction()
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getNumberOfTweets($this->get('oro_security.acl_helper'), $this->get('oro_config.user')->get('tfone_twittoro.update_tweets_hashtag'))
            ];
    }

    /**
     * @Route(
     *      "/latest_tweet/widget",
     *      name="tfone_twittoro_dashboard_latest_tweet_widget"
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:latestTweet.html.twig")
     */
    public function latestTweetAction()
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getLatestTweet($this->get('oro_security.acl_helper'), $this->get('oro_config.user')->get('tfone_twittoro.update_tweets_hashtag'))
            ];
    }

    /**
     * @Route(
     *      "/top_tweet/widget/{hashtag}",
     *      name="tfone_twittoro_dashboard_top_tweeter_widget",
     *      requirements={"hashtag"="[\w-]*"},
     *      defaults={"hashtag" = ""}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:topTweeter.html.twig")
     */
    public function topTweeterAction($hashtag = null)
    {
        if (empty($hashtag)) {
            $hashtag = $this->get('oro_config.user')->get('tfone_twittoro.update_tweets_hashtag');
        }
        
        if ($this->get('request')->get('switch_hashtag', false)) {         
            $this->get('session')->set('saved_hashtag', $hashtag);
            
        } else {
            $hashtag = $this->get('session')->get('saved_hashtag', $hashtag);
        }

        $template = 'TfoneTwittoroBundle:Dashboard:topTweeter.html.twig';
        return $this->render(
            $template,
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTopTweeter($this->get('oro_security.acl_helper'), $hashtag),
                'hashtags' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getAllTweets()

            ]);       
    }
}

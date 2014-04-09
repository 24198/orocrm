<?php

namespace Tfone\Bundle\TwittoroBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/tweets_username/chart/{widget}/{hashtag}",
     *      name="tfone_twittoro_dashboard_tweets_by_username_chart",
     *      requirements={"widget"="[\w_-]+", "hashtag"="[\w-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:tweetsByUsername.html.twig")
     */
    public function tweetsByUsernameAction($widget, $hashtag)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTweetsByUsername($this->get('oro_security.acl_helper'), $hashtag)
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }

    /**
     * @Route(
     *      "/tweets_over_time/chart/{widget}/{hashtag}",
     *      name="tfone_twittoro_dashboard_tweets_over_time_chart",
     *      requirements={"widget"="[\w_-]+", "hashtag"="[\w-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:tweetsOverTime.html.twig")
     */
    public function tweetsOverTimeAction($widget, $hashtag)
    {
        return array_merge( 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTweetsOverTime($this->get('oro_security.acl_helper'), $hashtag)
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }    
    
    /**
     * @Route(
     *      "/tweets_username/widget/{hashtag}",
     *      name="tfone_twittoro_dashboard_number_of_tweets_widget",
     *      requirements={"hashtag"="[\w-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:numberOfTweets.html.twig")
     */
    public function numberOfTweetsAction($hashtag)
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getNumberOfTweets($this->get('oro_security.acl_helper'), $hashtag)
            ];
    }

    /**
     * @Route(
     *      "/latest_tweet/widget/{hashtag}",
     *      name="tfone_twittoro_dashboard_latest_tweet_widget",
     *      requirements={"hashtag"="[\w-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:latestTweet.html.twig")
     */
    public function latestTweetAction($hashtag)
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getLatestTweet($this->get('oro_security.acl_helper'), $hashtag)
            ];
    }

    /**
     * @Route(
     *      "/top_tweet/widget/{hashtag}",
     *      name="tfone_twittoro_dashboard_top_tweeter_widget",
     *      requirements={"hashtag"="[\w-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:topTweeter.html.twig")
     */
    public function topTweeterAction($hashtag)
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTopTweeter($this->get('oro_security.acl_helper'), $hashtag)
            ];
    }
}

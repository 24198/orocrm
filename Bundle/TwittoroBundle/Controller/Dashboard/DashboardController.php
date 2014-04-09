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
    public function tweetsByUsernameAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTweetsByUsername($this->get('oro_security.acl_helper'), 'orocrm')
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }

    /**
     * @Route(
     *      "/tweets_over_time/chart/{widget}",
     *      name="tfone_twittoro_dashboard_tweets_over_time_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:tweetsOverTime.html.twig")
     */
    public function tweetsOverTimeAction($widget)
    {
        return array_merge( 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTweetsOverTime($this->get('oro_security.acl_helper'), 'orocrm')
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }    
    
    /**
     * @Route(
     *      "/tweets_username/widget/",
     *      name="tfone_twittoro_dashboard_number_of_tweets_widget"
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:numberOfTweets.html.twig")
     */
    public function numberOfTweetsAction()
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getNumberOfTweets($this->get('oro_security.acl_helper'), 'orocrm')
            ];
    }

    /**
     * @Route(
     *      "/latest_tweet/widget/",
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
                        ->getLatestTweet($this->get('oro_security.acl_helper'), 'orocrm')
            ];
    }

    /**
     * @Route(
     *      "/top_tweet/widget/",
     *      name="tfone_twittoro_dashboard_top_tweeter_widget"
     * )
     * @Template("TfoneTwittoroBundle:Dashboard:topTweeter.html.twig")
     */
    public function topTweeterAction()
    {
        return 
            [
                'items' => $this->getDoctrine()
                        ->getRepository('TfoneTwittoroBundle:Tweet')
                        ->getTopTweeter($this->get('oro_security.acl_helper'), 'orocrm')
            ];
    }
}

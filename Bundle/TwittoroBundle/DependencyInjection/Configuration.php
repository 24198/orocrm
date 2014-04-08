<?php

namespace Tfone\Bundle\TwittoroBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
   const DEFAULT_UPDATE_TWEETS_CRON_SCHEDULE = '* 08,17 * * *';
   
   const DEFAULT_TWITTER_API_URL               = 'https://api.twitter.com/1.1/search/tweets.json';
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('tfone_twittoro');
        
        SettingsBuilder::append(
            $rootNode,            
            [
                'update_tweets_cron_schedule' => ['value' => self::DEFAULT_UPDATE_TWEETS_CRON_SCHEDULE],
                'update_tweets_api_url' => ['value' => self::DEFAULT_TWITTER_API_URL],
                'update_tweets_oauth_access_token' => ['value' => null],
                'update_tweets_oauth_access_token_secret' => ['value' => null],
                'update_tweets_consumer_key' => ['value' => null],
                'update_tweets_consumer_secret' => ['value' => null],                
                'update_tweets_enabled' => ['value' => false, 'type' => 'bool'],                
                'update_tweets_hashtag' => ['value' => null],               
            ]            
        );
        return $treeBuilder;
    }
}
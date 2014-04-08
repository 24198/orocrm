<?php

namespace Tfone\Bundle\TwittoroBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\ConfigBundle\Config\UserConfigManager;

use Tfone\Bundle\TwittoroBundle\Provider\TweetProcessor;

/**
 * Update tweets command class
 * This class represents the class for the updating tweets cron command.
 * 
 */

class UpdateTweetsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const COMMAND_NAME   = 'oro:cron:tfone:twittoro:update-tweets';

    /**
     * {@internaldoc}
     */
    public function getDefaultDefinition()
    {
        return $this->getConfig()->get('tfone_twittoro.update_tweets_cron_schedule');
    }

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Update tweets about a specific hashtag')
            ->addOption('hashtag', null,InputOption::VALUE_OPTIONAL, 'The hashtag you want to import...');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger             = new OutputLogger($output);
        $hashtag            = $input->getOption('hashtag');
        /** @var TweetProcessor $processor */
        $processor          = $this->getContainer()->get('tfone_twittoro.tweet_processor');
        $processor->setLogger($output);

        if (!$this->getConfig()->get('tfone_twittoro.update_tweets_hashtag') && !$hashtag) {
            $logger->notice(sprintf('You did not enable specify any tweets in the configuration or as a parameter in the cron command.'));
            return;
        }        
        
        //base hashtag, either from the config or from the commandline
        if(!$hashtag) {            
            $logger->notice(sprintf('Updating tweets for all hashtags..'));
            $hashtag = $this->getConfig()->get('tfone_twittoro.update_tweets_hashtag');
        }else {
            $logger->notice(sprintf('Updating tweets for hashtag #%s..',$hashtag));
        }
        
        if (!$this->getConfig()->get('tfone_twittoro.update_tweets_enabled')) {
            $logger->notice(sprintf('You did not enable the update tweet cron in the System Configuration Settings of your Application'));
            return;
        }
        
        //add the other configuration options from the backend to the config array. 
        $config['hashtags'] = [ 'base' => $this->getConfig()->get('tfone_twittoro.update_tweets_hashtag'), 'optional' => $hashtag];
        $config['oauthtoken'] = $oAuthToken = $this->getConfig()->get('tfone_twittoro.update_tweets_oauth_access_token');
        $config['oauthsecret'] = $oAuthTokenSecret = $this->getConfig()->get('tfone_twittoro.update_tweets_oauth_access_token_secret');
        $config['consumkey'] = $consumerKey = $this->getConfig()->get('tfone_twittoro.update_tweets_consumer_key');
        $config['conumsecret'] = $consumerSecret = $this->getConfig()->get('tfone_twittoro.update_tweets_consumer_secret');
        $config['apiurl'] = $url = $this->getConfig()->get('tfone_twittoro.update_tweets_api_url');
        
        if (!$oAuthToken) {
            $logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.oauth_access_token_not_configured'));
            return;
        }
        
        if (!$oAuthTokenSecret) {
            $logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.oauth_access_token_secret_not_configured'));
            return;
        }
        
        if (!$consumerKey) {
            $logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.consumer_key_not_configured'));
            return;
        }
        
        if (!$consumerSecret) {
            $logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.consumer_secret_not_configured'));
            return;
        }

        if (!$url) {
            $logger->notice($this->getTranslator()->trans('tfone.twittoro.update_tweets.url_not_configured'));
            return;
        }

        
        //make a call to the processor, he will handle it from here on
        $processor->process($config);
        
    }

    /**
     * @return UserConfigManager
     */
    protected function getConfig()
    {
        return $this->getContainer()->get('oro_config.user');
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('translator');
    }
}
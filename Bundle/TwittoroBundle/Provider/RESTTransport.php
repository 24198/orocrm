<?php

namespace Tfone\Bundle\TwittoroBundle\Provider;

use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;

use Tfone\Bundle\TwittoroBundle\Helpers\Formatter;
/**
 * Tweets class is for constructing the api call to twitter
 * via the twitter api. This will return all the tweets based on
 * the given parameters in a JSON string
 * 
 */
class RESTTransport {
    
    /* @var basic url for the api request */
    protected $_baseUrl;
    /* @var constructed url with additional parameters */
    protected $_url;
 
    /* @var Formatter */
    protected $helper;
    
    public function __construct(Formatter $helper) {
        $this->helper = $helper;
    }
    
    /**
     * Create the request based on the given parameters
     * and make the call to twitter api.
     * 
     * @return type raw JSON string
      */
    public function call($config) {
        $this->_baseUrl = $config['apiurl'];
        
        $oauth = array( 'oauth_consumer_key' => $config['consumkey'],
                'oauth_nonce' => time(),
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_token' => $config['oauthtoken'],
                'oauth_timestamp' => time(),
                'oauth_version' => '1.0');
        
        if($config['hashtag'] != 'all') {
            $hashtag = $this->helper->formatHashtag($config['hashtag']); //need to format the url
            $this->_url = $this->_baseUrl. '?q=' . rawurlencode($hashtag);
            $oauth = array_merge($oauth, array('q' => $hashtag, 'count' => '100'));
        }
        //if $maxId is set, there are results for this hashtag in the db
        //if $maxId is not set, there are no results for this hashtag in the db.
        if($config['maxid'] != null) {
            //$maxId is the latest id, so we have to search from this point on
            //the paremeter to use here is since_id to fetch every new tweet since the
            //latest ($maxId) tweet
            $this->_url = $this->_url . '&since_id=' . rawurlencode($config['maxid']);
            $oauth = array_merge($oauth, array('since_id' => $config['maxid']));
        }
        
        
        
        //add the count parameter as the last parameter
        $this->_url .= '&count='.rawurlencode('100');
        $oauthFull = array_merge($oauth, array('count' => '100'));
                
        $baseInfo = $this->buildBaseString($this->_baseUrl, 'GET', $oauthFull);
        $compositeKey = rawurlencode($config['conumsecret']) . '&' . rawurlencode($config['oauthsecret']);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseInfo, $compositeKey, true));
        $oauthFull['oauth_signature'] = $oauthSignature;

        $header = array($this->buildAuthorizationHeader($oauthFull), 'Expect:');
        $options = array(CURLOPT_HTTPHEADER => $header,
                         CURLOPT_HEADER => false,
                         CURLOPT_URL => $this->_url,
                         CURLOPT_RETURNTRANSFER => true,
                         CURLOPT_SSL_VERIFYPEER => false);
                   
        //make the call via curl
        $feed = curl_init();
        var_dump($options);
        
        curl_setopt_array($feed, $options);
        $responseJson = curl_exec($feed);        
        curl_close($feed);

        //return the raw json data
        return $responseJson;

    }
    
    /**
     * Build the correct url for making the api call
     * with all the corresponding parameters and method.
     * 
     * @param type $baseURI the url to make the call to
     * @param type $method api request method ('POST' || 'GEt')
     * @param type $params parameters for request
     * @return type String the basestring with request method and parameters
     */
    protected function buildBaseString($baseURI, $method, $params)
    {
        $r = array(); 
        ksort($params); 
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value); 
        }            

        return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); //return complete base string
    }
    
    /**
     * Build the authorization header for the request.
     * 
     * @param type $oauth encoded paramters for the request
     * @return type String the authorization header string.
     */
    protected function buildAuthorizationHeader($oauth)
    {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\""; 

        $r .= implode(', ', $values); 
        return $r; 
    }           
}
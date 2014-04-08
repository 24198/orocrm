<?php

namespace Tfone\Bundle\TwittoroBundle\Helpers;

/**
 * Helper class for formatting the hashtag
 */
class Formatter {
    //hashtag prefix, this will always be the # character
    const HASHTAG_PREFIX = '#';
    
    /**
     * Format the hashtag string
     * @param string $hashtag 
     * @return string formatted hashtag
     * @throws \Exception if hashtag is not specified or it is already being formatted
     */
    public function formatHashtag($hashtag) {
        
        if(!$hashtag) {
            throw new \Exception('Hashtag is not specified');
        }
        
        if(strpos($hashtag, '#') !== false && substr($hashtag, 0, 1) == '#') {
            throw new \Exception('Hashtag is already formatted');
        }
        
        $formattedHashtag = self::HASHTAG_PREFIX . $hashtag;
        
        return $formattedHashtag;
    }
}
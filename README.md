## Twittoro Bundle

The bundle will fetch tweets for a given hashtag. It will store these tweets in the db in the tfone_twittoro_tweets
table.

You will be able to see the statistics in a new dashboard named "Twitter Statistics". In this dashboard you will
see a number of widgets:

#Charts
- Tweets by username [ will show a bar chart with the names and total tweets including your hashtag ]
- Tweets over time [ will show a chart with the total tweets for every day for the last 10 days ]
#Normal widgets
- Top tweeter [ will show whom has the most tweets including your hashtag ]
- Number of Tweets [ will show you the total number of tweets including your hashtag ]
- Latest Tweet [ will display the lastest tweet including your hashtag]

## Requirements

The Twittoro Bundle requires an consumer key, consumer secret and oauth token and secret in order
to function properly. 

## Instructions
In order to update the tweets via command line the following command will do the trick:
i.e.
```bash
    php app/console oro:cron:tfone:twittoro:update-tweets --hashtag=orocrm
```

please note that the hashtag is specified without the '#' character, keep this in mind :)

##NOTES
Tweets from the api who are older than 9 days will not be updated.
Twitter only indexes the tweets between 0 - 9 days (source)
So if some of the tweets do not show up in your initial update, this is probably the cause.

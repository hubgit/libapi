A collection of text processing APIs.

* analyse: sentiment analysis

* bookmarks: list of bookmarks for the given identifier

* citedby: APIs that return citation counts for a DOI
Returns ((int) citedbycount, (string) url for citedby page) if successful.

* content: fetch content
Stores items in an output folder if defined, otherwise returns an array of items.

* entities: extract entities from a string of text (or sometimes XML)
Returns ((array) entities, (array) references): a list of entities found in the text and their positions.

* geocode: a port of Simon Willison's geocoders http://github.com/simonw/geocoders/
Returns ((string) place_name, ((float) lat, (float) lon)) if the string can be geocoded.

* metadata: metadata about an object, given an identifier

* search: search results for a given term
Returns an array of search results

===

REQUIRES PHP 5

To get started, copy Config-example.php to Config.php and add in your own API keys/database credentials.

See the scripts in the 'test' folder for an example of how to call a particular class of APIs.

===

Examples:

* To back up a user's Twitter posts: set TWITTER_AUTH in Config.php, edit the screen name in test/backup/twitter.php then run test/backup/twitter.php on the command line. The data will be saved in the 'data' directory, unless you've defined DATA in Config.php

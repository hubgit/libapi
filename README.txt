A collection of classes for accessing web APIs.

Each API may provide several standard (below) or non-standard methods.

* analyse: sentiment analysis

* bookmarks: a list of bookmarks for the given identifier

* citedby: citation counts for an identifier
Returns ((int) citedbycount, (string) url for citedby page) if successful.

* content: fetch content
Stores items in an output folder if defined, otherwise returns an array of items.

* entities: extract entities from a string of text (or sometimes XML)
Returns ((array) entities, (array) references): a list of entities found in the text and their positions.

* geocode: a port of Simon Willison's geocoders http://github.com/simonw/geocoders/
Returns ((string) place_name, ((float) lat, (float) lon)) if the string can be geocoded.

* metadata: metadata about an object, given an identifier

* search: search results for a given term
Returns ((array) search results, (array) metadata about the results e.g. the total number of results).

===

REQUIRES PHP 5

To get started, copy Config-example.php to Config.php and add in your own API keys/database credentials.

See the scripts in the 'test' folder for an example of how to call a particular class of APIs.

===

Examples:

A collection of text processing APIs.

* geocode: a port of Simon Willison's geocoders http://github.com/simonw/geocoders/
Returns ((string) place_name, ((float) lat, (float) lon)) if the string can be geocoded, and (FALSE, (FALSE, FALSE)) if it cannot.

* citedby: APIs that return citation counts for a DOI
Returns ((int) citedbycount, (string) url for citedby page) if successful, (FALSE, FALSE) if not.

* entities: extract entities from a string of text (or sometimes XML)
Returns ((array) entities, (array) references), list of entities found in the text and their positions.

===

To get started, copy config-example.inc.php to config.inc.php and add in your own API keys. Comment out the definition lines in this file for services you don't want to be active.

See the scripts in the 'test' folder for an example of how to call a particular class of APIs.



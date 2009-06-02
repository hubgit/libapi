A collection of text processing APIs.

* geocode: a port of Simon Willison's geocoders http://github.com/simonw/geocoders/
The geocode functions return (unicode_place_name, (float_lat, float_lon)) if the string can be geocoded, and (FALSE, (FALSE, FALSE)) if it cannot.

===

To get started, copy config-example.inc.php to config.inc.php and add in your own API keys. Comment out the definition lines in this file for services you don't want to be active.

See test.php for an example of how to call a particular class of APIs.



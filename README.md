openlp_ccli_number
==================

This collection of scripts will help you fill in missing CCLI numbers in your OpenLP database.

Requirements
------------

* Web server (tested with apache2) with PHP5.x, CURL, Sqlite3

Usage
-----

* Clone this repository somewhere under your web server's document root or download the .zip file and unpack it there.
* Put a copy of the songs/songs.sqlite file from your OpenLP data folder in the same directory.
* From your browser, hit index.php in your newly-created script directory.

What the script does
--------------------

The index.php script will get all songs without a CCLI number from your songs.sqlite database. 
It will then look them up using the de.songselect.com (you can change this in the ajax.php file, if necessary).
If a single result is found, the number will be directly imported into your database. For multiple results, you
will be presented with a form allowing you to choose. You can also enter a number manually or permanently mark
a song as "not found" (This is done by entering "--" in the ccli_number field in the database).

Contact
-------

This script was initially written by Christoph Fischer (christoph.fischer@volksmission.de) for Volksmission Freudenstadt (www.volksmission-freudenstadt.de). 
Feel free to use it under the conditions of the GPL v2 license.

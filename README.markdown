Lists Readme
========================

Lists is a simple app to make lists of things you need to remember. Based on code from Burden, Lists provides an attractive way to manage multiple lists and supports multiple users (W.I.P)

Features:
---------

* Multiple lists

Donations:
------------

If you like Lists and appreciate my hard work a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UYWJXFX6M4ADW) (no matter how small) would be appreciated. I code in my spare time and make no money formally from my scripts.

Screenshots:
------------

Releases:
------------

Installation:
-------------

1. Create a new database using your web hosts control panel (for instructions on how to do this please contact your web host)
2. Download and unzip Lists-xxxx.zip
3. Upload the Lists folder to your server via FTP or your hosts control panel
4. Open up http://yoursite.com/Lists/installer in your browser and enter your database/user details
5. Delete the "installer" folder from your server
6. Login to Lists using the username and password you set during the install process
7. Add your tasks
8. Lists should now be set up

Usage:
------

Updating:
---------

1. Before performing an update please make sure you backup your database
2. Download your config.php file (in the Lists folder) via FTP or your hosts control panel
3. Delete the Lists folder off your server
4. Download the latest version of Lists from [here](https://github.com/joshf/Lists/releases)
5. Unzip the file
6. Upload the unzipped Lists folder to your server via FTP or your hosts control panel
7. Upload your config.php file into the Lists folder
4. Open up http://yoursite.com/Lists/installer/upgrade.php in your browser and the upgrade process will start
9. You should now have the latest version of Lists

N.B: The upgrade will only upgrade from the previous version of Lists (e.g 0.5 to 0.6), it cannot be used to upgrade from a historic version.

Removal:
--------

To remove Lists, simply delete the Lists folder from your server and delete the "Data" table from your database.

Support:
-------------

For help and support post an issue on [GitHub](https://github.com/joshf/Lists/issues).

Contributing:
-------------

Feel free to fork and make any changes you want to Lists. If you want them to be added to master then send a pull request via GitHub.

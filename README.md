# authdrupal7 Plugin for DokuWiki

Fork that is planned to implement calls to mysqli instead of deprecated mysql.

Dokuwiki Authetication using Drupal7 accounts

This plugin might be useful if you are running a drupal website and want to give your drupal users access to a dokuwiki using the same login credentials.
In my case I am hosting a public website providing public information about a student's group. All members have an drupal account to do certain modifications on the website. For internal knowledge transfer we also host a dokuwiki running on the same server. I don't want to adminstrate double login credentials.
There are solutions for this problem, I found several offers. But I was not able to get any of them to work. That's why I decided to start my own project.

All documentation for this plugin can be found at
http://www.dokuwiki.org/plugin:authdrupal7

If you install this plugin manually, make sure it is installed in
lib/plugins/authdrupal7/ - if the folder is called different it
will not work!

Please refer to http://www.dokuwiki.org/plugins for additional info
on how to install plugins in DokuWiki.

----

This plugin is widely based on

Dokuwiki MySQL authentication backend by
* Andreas Gohr <andi@splitbrain.org>
* Chris Smith <chris@jalakai.co.uk>
* Matthias Grimm <matthias.grimmm@sourceforge.net>
* Jan Schumann <js@schumann-it.com>

and
DokuDrupal Drupal 7.x/MySQL authentication backend by
* Alex Shepherd <n00bATNOSPAMn00bsys0p.co.uk>

----
## Configuration
The plugin will only work if you have a drupal installtion accessible, as it tries to include some drupal code snippets (e.g. the hashing algorithm).

In configuration backend you have to edit at least the following entries:
* MySQL server
* MySQL user and password
* MySQL database holding your drupal tables
* The database prefix used for your drupal tables (including the underscore e.g. ```myprefix_```)
* The path to you drupal root directory (including a slash at the end e.g. ```../drupal/```)

**Before** setting your authentication mode to ```authdrupal7``` you should think about the following:
Dokuwiki might only grant access to users that are member of a defined user group. This plugin is reading the user groups from your database.
Make shure, that you use the exact same user group names in the ACL controls. Otherwise you might not be able to get access to your wiki or your adminstration panel although you are able to log in using correct credentials.

In the configuration menu you should consider changing the following entries:
* superuser: add a superuser group (with @) or username that matches one of your drupal roles or users
* manager: add a manager group (with @) or username that matches one of your drupal roles or users

Also think about necessary changes in the ACL config. For my case I configured my wiki as closed. So accounts that do not match a special group defined in the ACL controls are locked out. I added a new role "wiki" for all my users in drupal and made accessing the wiki exclusive for this user group (@wiki).

----
Copyright (C) Matthias Jung <matzekuh@web.de>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the COPYING file in your DokuWiki folder for details

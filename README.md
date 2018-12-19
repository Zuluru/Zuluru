Table of Contents
=================

   * [Installation](#installation)
      * [Install CakePHP](#install-cakephp)
      * [Install Zuluru](#install-zuluru)
      * [Configure web server](#configure-web-server)
      * [Configure Zuluru](#configure-zuluru)
      * [Cron Setup](#cron-setup)
      * [Leaguerunner Conversion](#leaguerunner-conversion)
      * [Updates](#updates)
         * [Version](#version)
   * [Development](#development)
      * [Debugging](#debugging)
      * [Customization](#customization)
      * [Administration](#administration)
      * [Themes](#themes)

# Installation

Note that this version of Zuluru is now obsolete, replaced by [version 3](https://github.com/Zuluru/Zuluru3). No further work is expected on the 1.x line.

## Install CakePHP

Acquire and install the CakePHP framework from http://cakephp.org/
Zuluru is known to work with version 1.3.6 of CakePHP. It will
probably work with later 1.3 releases, but will require modifications
to run under CakePHP 2.0 and later.

From the command line, you might use something like

```sh
$ cd /path/to/cake
$ git init
$ git pull git://github.com/cakephp/cakephp.git 1.3
```

More instructions for downloading and installing various configurations
are at
[Installation Preparation](https://book.cakephp.org/1.3/en/The-Manual/Developing-with-CakePHP/Installation-Preparation.html)
and
[Installation](https://book.cakephp.org/1.3/en/The-Manual/Developing-with-CakePHP/Installation.html)
in the CakePHP Cookbook.

## Install Zuluru

Acquire and install the Zuluru source code. The CakePHP installation
instructions will assume you're putting your application (i.e. Zuluru)
in the app folder, but that can cause problems when updating to a newer
version of CakePHP. Instead, it is recommended that you put Zuluru in a
folder called zuluru, next to the app folder. The remaining directions
will assume this is where you've put it. Adjust the steps you take when
installing CakePHP for this.

From the comand line, you might use something like

```sh
$ cd /path/to/cake
$ mkdir zuluru
$ cd zuluru
$ git init
$ git config remote.origin.url git://github.com/Zuluru/Zuluru.git
$ git pull
```

## Configure web server

If Zuluru is your primary application (e.g. you have only Zuluru and
some HTML files), Zuluru will be at http://www.example.com/. Update
your web server configuration to use `/path/to/cake/zuluru/webroot` as
the root.

If you are on a shared webhost, you may not have the level of control
required to change the root folder. In this case, see the URLs given in
the "Install CakePHP" section above for tips on how to proceed.

If Zuluru is co-existing with some kind of content management system,
a more common URL would be `http://www.example.com/zuluru/`. Again, see
the URLs in the "Install CakePHP" section above for advanced help.

## Configure Zuluru

As described above, the base URL for Zuluru may vary. Whatever it is
(e.g. `http://www.example.com/` or `http://www.example.com/zuluru`), we
will refer to that as <ZULURU> below. Note that if you do not have
mod_rewrite (or equivalent) to provide short URLs, your <ZULURU> path
may be more like `http://www.example.com/zuluru/index.php`; in this case,
you still follow this with a slash in the URLs you construct below
(e.g.  `http://www.example.com/zuluru/index.php/install`).

The following directories under `/path/to/cake/zuluru` must be writable
by the web server at all times (some of these can be modified in the
`folders` section of the `config/install.php` file):

    /tmp
      /cache
        /models
        /persistent
        /views
      /logs
      /sessions
    /upload
    /webroot/files/temp

The following directories and files under `/path/to/cake/zuluru` must be
writable by the web server during the install process, but can (and
perhaps should) be made read-only after installation is complete:

    /config
      /core.php

Typically, this can be ensured by setting the owner of the tmp and
config trees to the user that the web server runs as. If you get the
"An Internal Error Has Occurred" page, it's probably a permissions
problem.

At this point, you should (hopefully) be able to point your browser at
the Zuluru install, <ZULURU>/install. This will ask for information
about your database, and some other information about your site. The
database must have been created ahead of time, though it can be empty.

Before proceeding with database population, you should take a look at
a couple of the files in `/path/to/cake/zuluru/config/schema/data`. (The
installation procedure will remind you of this, and provide the exact
paths to these files.) In particular, most installations will want to
update the regions file, as this is specific to your geography, and
there is no functionality to edit this in the Zuluru interface. Anyone
outside North America should update the countries and provinces.

Anything in settings and people can more easily be altered later though
the Zuluru interface, so these two should be left alone. The days,
`event_types` and groups files should NOT be altered, as doing so may
break functionality.

The install process will create all of Zuluru's database tables and
insert default starting information where required, as well as creating
the `database.php` and `install.php` files in `zuluru/config`. Once this
process is done, you shouldn't need to touch those files, but you can
always make changes manually as required.

Once installation is completed and the database has been populated, log
in with the administrative user name and password provided, and go
through the various pages under the Settings menu to finalize your site
configuration.

## Cron Setup

There are some processes which, if run daily, will help your site run
more smoothly. We have found that fetching `<ZULURU>/all/cron` daily at
1pm works quite nicely. You should be able to set up something to do
this for you through the cron mechanism of UNIX/Linux systems, or the
Task Scheduler in Windows.

`<ZULURU>/all/cron` is safe to call multiple times; it remembers what it
has done (e.g. emails sent), and doesn't repeat it.

Currently, if you want to see the output from this, you will have to
take steps to capture and email it to yourself. Something like this in
the crontab file will work:

    0 13 * * * root htmlmail admin@zuluru.org "Zuluru daily report" http://demo.zuluru.org/all/cron > /dev/null

Where htmlmail is the following general-purpose script:

```php
#!/usr/bin/php
<?php
$email = $_SERVER['argv'][1];
$subject = $_SERVER['argv'][2];
$url = $_SERVER['argv'][3];

if (function_exists('curl_init')) {
  $curl_handle=curl_init();
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl_handle, CURLOPT_USERAGENT, 'htmlmail');
$i = 0;
do {
  $contents = curl_exec($curl_handle);
} while (empty($contents) && ++$i < 5);
  curl_close($curl_handle);
} else {
  $handle = fopen($url, 'rb');
  $contents = '';
  while (!feof($handle)) {
    $contents .= fread($handle, 8192);
  }
  fclose($handle);
}

$headers = 'Content-Type: text/html; charset="iso-8859-1"';
mail($email, $subject, $contents, $headers, "-f admin@zuluru.org");
?>
```

## Leaguerunner Conversion

If there is demand, the install procedure may be enhanced to detect an
existing Leaguerunner database and attempt to convert it to the Zuluru
format. Further details will be added here when that work is complete.
In the meantime, you can contact admin@zuluru.org for assistance with
a manual conversion.

## Updates

The source for Zuluru is updated on a regular basis, with bug fixes and
new features being added. Most of the time, you will be able to update
your local version just by executing
$ git pull
from your Zuluru folder. If you have made any changes to Zuluru
files, this may be more complex, requiring things like "stash", "merge"
or "branch". Hopefully, you can use themes and the `features_custom` and
`options_custom` config files to avoid any such changes.

When updates to Zuluru are released, there may also be changes to the
database required. After updating the code, go to
`<ZULURU>/install/install/update` and Zuluru will take care of these
changes for you. If you have updated the code but not run this process,
Zuluru will prompt you to do so.

### Version

See `config/version.php` for the version of Zuluru that this source code
represents, and `config/installed.php` (if it exists) for the version of
Zuluru that the system thinks is installed.

If these don't match, you should use `http://example.com/install/install/update`.

# Development

## Debugging

If you get blank pages or generic error messages, try changing the
debug level in `zuluru/config/core.php` from 0 to 1 or 2. This should
give you details on what's going wrong. Looking at your server's error
logs may also provide some help.

## Customization

You can customize the look of any part of the system using CakePHP's
"themes" functionality. See `README.themes` for more details.

## Administration

For help on setting up fields, leagues, registration events, and the
other details required for day-to-day use of the system, see the help
in the application.

## Themes

CakePHP applications such as Zuluru generate their output through the
use of "views". Each page in the system has a primary view, with a name
similar to the page. For example, the view for /people/edit is located
at `/cake/zuluru/views/people/edit.ctp`. The page /leagues is a shortform
for /leagues/index, with a view at `/cake/zuluru/views/leagues/index.ctp`.

Many views also make use of elements, which are like mini-views that
are needed in various places. The content for emails is also generated
by elements. Elements are all in `/cake/zuluru/views/elements` and folders
below there.

CakePHP provides a way for you to replace any of these views, without
actually editing them. This is important for when you install a Zuluru
update; it will keep you from losing your customizations. To use this,
you simply create a new folder under `/cake/zuluru/views/themed` with the
name of your theme. For example, if your league is called "XYZ", you
might create `/cake/zuluru/views/themed/xyz`. Edit `install.php` with the
name of your theme:

```php
$config['theme'] = 'xyz';
```

Now, copy and edit any view that you want to replace into your new xyz
folder. For example, to replace the membership waiver text, you would
copy `/cake/zuluru/views/elements/people/waiver/membership.ctp` into
`/cake/zuluru/views/themed/xyz/elements/people/waiver/membership.ctp` and
edit the resulting file. View files are PHP code, so you should have at
least a little bit of PHP knowledge if you are making complex changes.

Other common views to edit include the page header (the empty default is
found in `/cake/zuluru/views/elements/layout/header.ctp`) or the main
layout itself (`/cake/zuluru/views/layouts/default.ctp`). The layout is
built to be fairly customizable without needing to resort to theming;
for example you can add additional CSS files to include with an entry in
`install.php`.

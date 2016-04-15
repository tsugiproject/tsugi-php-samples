
Tsugi Samples
=============

This contains a number super-simple Tsugi applications.  For now, 
you still have to checkout the main Tsugi, install it and set up the
databases.  I have not yet pulled the table setup capability out separately.

But once data tables are built, a tool can be edited and run 
stand alone with its only run-time dependency on the `tsugi-php` library
loaded from Packagist.

**Note:** I still have more work to do to refactor the Tsugi repository into its various parts
that can be used independently.

Installation
------------

Check this out into a folder.  Then install composer from

    http://getcomposer.org/

I just do this in the folder:

    curl -O https://getcomposer.org/composer.phar

To install the dependencies into the `vendor` area, do:

    php composer.phar install

If you want to upgrade dependencies (perhaps after a `git pull`) do:

    php composer.phar update

Note that the `composer.lock` file and `vendor` folder are 
both in the `.gitignore` file and so they won'g be checked into
any repo.

Configuration
-------------

Edit the connfiguration file.

    cp config-dist.php config.php
    edit config.php with the editor of your choice.

You can also simply copy the `config.php` file from your
Tsugi installation into this folder.

Running
-------

Once it is installed and configured, you can do an LTI launch to

    http://localhost:8888/tsugi-php-samples/grade/index.php
    key: 12345
    secret: secret

You can use your Tsugi installation or my test harness at:

    https://online.dr-chuck.com/sakai-api-test/lms.php

And it should work!

Upgrading the Library Code
--------------------------

From time to time the library code in

    https://github.com/csev/tsugi-php

Will be upgraded and pulled into Packagist:

    https://packagist.org/packages/tsugi/lib

To get the latest version from Packagist, edit `composer.json` and
update the commit hash to the latest hash on the `packagist.org` site
and run:

    php composer.phar update




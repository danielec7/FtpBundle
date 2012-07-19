#FtpBundle

A Symfony2 Bundle to wrap the PHP ftp extension functionality in a more "classy" way.

[![Build Status](https://secure.travis-ci.org/iJanki/FtpBundle.png?branch=master)](http://travis-ci.org/iJanki/FtpBundle)

##Installation

### Step 1: Install the bundle

#### For Symfony 2.0:

Add the following entries to the deps in the root of your project file:

```
[IjankiFtpBundle]
    git=git://github.com/iJanki/FtpBundle.git
    target=bundles/Ijanki/Bundle/FtpBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

Then you need to add the Ijanki namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Ijanki' => __DIR__.'/../vendor/bundles',
));
```

#### For Symfony >= 2.1

Add the following dependency to your composer.json file:

    "require": {
        # ..
        "ijanki/ftp-bundle": "*"
        # ..
    }

### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Ijanki\Bundle\FtpBundle\IjankiFtpBundle(),
    );
}
```

## Usage

``` php
<?php

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

public function indexAction()
{
    //...
    try {
        $ftp = $this->container->get('ijanki_ftp');
    	$ftp->connect($host);
    	$ftp->login($username, $password);
    	$ftp->put($destination_file, $source_file, FTP_BINARY);

    } catch (FtpException $e) {
    	echo 'Error: ', $e->getMessage();
    }
    //...
}
```

All php [ftp functions](http://php.net/manual/en/ref.ftp.php) are wrapped in Ftp object:

```
For example:
ftp_mkdir becomes $ftp->mkdir or
ftp_put becomes $ftp->put
with the same arguments except the first one (resource $ftp_stream).
```

Check Ftp.php for other added methods.

## Credits

Inspired by https://github.com/dg/ftp-php



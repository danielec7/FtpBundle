#FtpBundle

A Symfony2 Bundle to wrap the PHP ftp extension functionality in a more "classy" way.

[![Latest Stable Version](https://poser.pugx.org/ijanki/ftp-bundle/v/stable)](https://packagist.org/packages/ijanki/ftp-bundle) [![Build Status](https://secure.travis-ci.org/iJanki/FtpBundle.png?branch=master)](http://travis-ci.org/iJanki/FtpBundle) [![Total Downloads](https://poser.pugx.org/ijanki/ftp-bundle/downloads)](https://packagist.org/packages/ijanki/ftp-bundle) [![License](https://poser.pugx.org/ijanki/ftp-bundle/license)](https://packagist.org/packages/ijanki/ftp-bundle)

##Installation

### Step 1: Install the bundle

Require the bundle with composer:

    $ composer require ijanki/ftp-bundle

### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new Ijanki\Bundle\FtpBundle\IjankiFtpBundle(),
    ];
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

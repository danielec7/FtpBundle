<?php

namespace Ijanki\Bundle\FtpBundle\Exception;

/**
 * Class FtpNotConnectedException
 * @package Ijanki\Bundle\FtpBundle\Exception
 */
class FtpNotConnectedException extends FtpException
{
    protected $message = 'Not connected to FTP server. Call connect() or ssl_connect() first.';
}
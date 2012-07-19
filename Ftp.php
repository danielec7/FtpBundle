<?php

namespace Ijanki\Bundle\FtpBundle;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

/**
*
*/
class Ftp
{
    private $resource;

    public function __construct()
    {
        if (!extension_loaded('ftp')) {
            throw new \Exception("PHP extension FTP is not loaded.");
        }
    }

    /**
    * wrapper for ftp functions.
    */
    public function __call($name, $args)
    {
        $name = strtolower($name);

        $func = 'ftp_' . $name;

        if (!function_exists($func)) {
            throw new \Exception("Call to undefined method Ftp::$name().");
        }

        if ($func === 'ftp_connect' || $func === 'ftp_ssl_connect') {
            try {
                $this->resource = call_user_func_array($func, $args);
            } catch (\ErrorException $e) {
                throw new FtpException($e->getMessage());
            }
            $res = null;

        } elseif (!is_resource($this->resource)) {
            #restore_error_handler();
            throw new FtpException("Not connected to FTP server. Call connect() or ssl_connect() first.");

        } else {
            array_unshift($args, $this->resource);
            try {
                $res = call_user_func_array($func, $args);
            } catch (\ErrorException $e) {
                throw new FtpException($e->getMessage());
            }

        }

        return $res;
    }

    /**
    * Recursive creates directories.
    * @param string
    * @return void
    */
    public function mkDirRecursive($dir)
    {
        $parts = explode('/', $dir);
        $path = '';
        while (!empty($parts)) {
            $path .= array_shift($parts);
            try {
                if ($path !== '') $this->mkdir($path);
            } catch (FtpException $e) {
                if (!$this->isDir($path)) {
                    throw new FtpException("Cannot create directory '$path'.");
                }
            }
            $path .= '/';
        }
    }

    /**
    * Recursive deletes path.
    * @param string
    * @return void
    */
    public function deleteRecursive($path)
    {
        foreach ((array) $this->nlist($path) as $file) {
            if ($file !== '.' && $file !== '..') {
                $this->deleteRecursive(strpos($file, '/') === FALSE ? "$path/$file" : $file);
            }
        }
        $this->rmdir($path);
    }
}

<?php

namespace Ijanki\Bundle\FtpBundle;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

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
            $result = null;

        } elseif (!is_resource($this->resource)) {
            throw new FtpException("Not connected to FTP server. Call connect() or ssl_connect() first.");
        } else {
            array_unshift($args, $this->resource);
            try {
                $result = call_user_func_array($func, $args);
            } catch (\ErrorException $e) {
                throw new FtpException($e->getMessage());
            }

        }

        return $result;
    }
    
    /**
     * Put a string in remote $file_name
     */
    
    public function putContents($file_name, $data, $mode = FTP_ASCII)
    {
        if (!is_resource($this->resource)) {
            throw new FtpException("Not connected to FTP server. Call connect() or ssl_connect() first.");
        }
        $temp = tmpfile();
        fwrite($temp, $data);
        fseek($temp, 0);
        return $this->fput($file_name, $temp, $mode);
    }
    
    public function isDir($dir)
    {
    	$current = $this->pwd();
    	try {
    		$this->chdir($dir);
    	} catch (FtpException $e) {
    	}
    	$this->chdir($current);
    	return empty($e);
    }
    
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

    public function deleteRecursive($path)
    {
        foreach ((array) $this->nlist($path) as $file) {
            if ($file !== '.' && $file !== '..') {
                $this->deleteRecursive(strpos($file, '/') === false ? "$path/$file" : $file);
            }
        }
        $this->rmdir($path);
    }
}

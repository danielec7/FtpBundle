<?php

namespace Ijanki\Bundle\FtpBundle;

use Ijanki\Bundle\FtpBundle\Exception\FtpException;

/**
 * Class Ftp
 *
 * @package Ijanki\Bundle\FtpBundle
 *
 * @method	bool		alloc ( int $filesize, string &$result = null )
 * @method	bool		cdup ( )
 * @method	bool		chdir ( string $directory )
 * @method	int		chmod ( int $mode , string $filename )
 * @method	bool		close ( )
 * @method	resource	connect ( string $host, int $port = 21, int $timeout = 90 )
 * @method	bool		delete ( string $path )
 * @method	bool		exec ( string $command )
 * @method	bool		fget ( resource $handle , string $remote_file , int $mode, int $resumepos = 0 )
 * @method	bool		fput ( string $remote_file , resource $handle , int $mode, int $startpos = 0 )
 * @method	mixed		get_option ( int $option )
 * @method	bool		get ( string $local_file , string $remote_file , int $mode, int $resumepos = 0 )
 * @method	bool		login ( string $username , string $password )
 * @method	int		mdtm ( string $remote_file )
 * @method	string		mkdir ( string $directory )
 * @method	int		nb_continue ( )
 * @method	int		nb_fget ( resource $handle , string $remote_file , int $mode, int $resumepos = 0 )
 * @method	int		nb_fput ( string $remote_file , resource $handle , int $mode, int $startpos = 0 )
 * @method	int		nb_get ( string $local_file , string $remote_file , int $mode, int $resumepos = 0 )
 * @method	int		nb_put ( string $remote_file , string $local_file , int $mode, int $startpos = 0 )
 * @method	array		nlist ( string $directory )
 * @method	bool		pasv ( bool $pasv )
 * @method	bool		put ( string $remote_file , string $local_file , int $mode, int $startpos = 0 )
 * @method	string		pwd ( )
 * @method	bool		quit ( )
 * @method	array		raw ( string $command )
 * @method	mixed		rawlist ( string $directory, bool $recursive = false )
 * @method	bool		rename ( string $oldname , string $newname )
 * @method	bool		rmdir ( string $directory )
 * @method	bool		set_option ( int $option , mixed $value )
 * @method	bool		site ( string $command )
 * @method	int		size ( string $remote_file )
 * @method	resource	ssl_connect ( string $host, int $port = 21, int $timeout = 90 )
 * @method	string		systype ( )
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
    	    $this->chdir($current);
    	}
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

    /**
     * Interpret connection info from url string
     */

    public function connectUrl($url)
    {
        if(!preg_match('!^ftp(?<ssl>s?)://(?<user>[^:]+):(?<pass>[^@]+)@(?<host>[^:/]+)(?:[:](?<port>\d+))?(?<path>.*)$!i', $url, $match)) {
            throw new \FtpException('Url must be in format: ftp[s]://username:password@hostname[:port]/[path]');
        }

        // default port if necessary
        if (empty($match['port'])) {
            $match['port'] = '21';
        }

        // determine and invoke connect method
        $connectMethod = (bool) $match['ssl'] ? 'ssl_connect' : 'connect';
        $this->$connectMethod($match['host'], $match['port']);

        // authenticate
        if (!$this->login($match['user'], $match['pass'])) {
            throw new \FtpException("Login failed as " . $match['user']);
        }

        // normalize and change to path, if one given
        $match['path'] = trim($match['path'], '/');
        if (!empty($match['path'])) {
            $this->chdir("/$match[path]/");
        }
    }
}

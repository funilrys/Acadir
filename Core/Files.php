<?php

/*
 * The MIT License
 *
 * Copyright 2017 Nissar Chababy <contact at funilrys dot com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Core;

use App\Config\Locations;

/**
 * File manipulations
 *
 * @author Nissar Chababy <contact at funilrys dot com>
 */
class Files
{

    /**
     * Return a message to inform developer that a directory is not found
     * 
     * @throws \Exception Directory does not exit
     */
    public static function checkVitalDirectories()
    {
        $vital = array(
            Locations::STYLESHEETS => static::isDir(Locations::STYLESHEETS),
            Locations::JAVASCRIPTS => static::isDir(Locations::JAVASCRIPTS),
            Locations::IMAGES => static::isDir(Locations::IMAGES)
        );

        foreach ($vital as $key => $value) {
            if ($value !== true) {
                throw new \Exception("The (vital) directory '" . Locations::PUBLIC_DIR . "/" . $key . "' is not found");
            }
        }
    }

    /**
     * Match a given film according to it's extension. 
     * Return the directory where it should be located.
     * 
     * @param string $file File to match with
     * 
     * @return string
     * @throws \Exception Extension is not matched
     */
    public static function matchExtensionToFileSystem($file)
    {
        $regx = array(
            Locations::STYLESHEETS => '/^.*\.(css)$/i',
            Locations::JAVASCRIPTS => '/^.*\.(js)$/i',
            Locations::IMAGES => '/^.*\.(jpg|jpeg|png|gif|ico)$/i'
        );

        foreach ($regx as $key => $value) {
            if (preg_match($value, $file)) {
                return $key;
            }
        }
        throw new \Exception("File extention of $file is not accepted.");
    }

    /**
     * Return a link or an HTML object linked to the desired file according 
     * to $type which come from matchExtensionToFileSystem()
     * 
     * @param string $file File to create Link to
     * @param string $type Type if the file (link =  Locations::STYLESHEETS | script = Locations::JAVASCRIPTS)
     * @param boolean $createHTMLObject
     * 
     * @throws \Exception Impossible to create HTML object|File not found
     */
    public static function createLinkToFile($file, $type, $createHTMLObject)
    {
        $http = \App\Config\Sessions::SECURED_COOKIES ? 'https://' : 'http://';
        $siteURL = $http . $_SERVER['HTTP_HOST'] . explode('index.php', $_SERVER['REDIRECT_URL'])[0];

        if (static::isFile($file) && $createHTMLObject) {
            if ($type === Locations::STYLESHEETS) {
                echo "<link href = \"" . $siteURL . $file . "\" rel=\"stylesheet\" type=\"text/css\">";
            } elseif ($type === Locations::JAVASCRIPTS) {
                echo "<script src=\"" . $siteURL . $file . "\" type=\"text/javascript\"></script>";
            } elseif ($type === Locations::IMAGES) {
                echo $siteURL . $file;
            } else {
                throw new \Exception("Impossible to create an HTLM object for '" . Locations::PUBLIC_DIR . "/$file'");
            }
        } elseif ($createHTMLObject !== true && static::isFile($file)) {
            echo $siteURL . $file;
        } else {
            throw new \Exception(Locations::PUBLIC_DIR . "/$file is not found");
        }
    }

    /**
     * Check if file exist and is readable
     * 
     * @param string $file File to check existence
     *  
     * @return boolean
     */
    public static function isFile($file)
    {
        if (is_file($file)) {
            return true;
        }
        return false;
    }

    /**
     * Check if file exist and is readable
     * 
     * @param string $dir Directory to check existence
     * 
     * @return boolean
     */
    public static function isDir($dir)
    {
        if (is_dir($dir)) {
            return true;
        }
        return false;
    }

    /**
     * get the hash of a file
     * 
     * @param string $algo Hash algorithm
     * @param string $path Path to the file
     * 
     * @return string
     */
    public static function hashFile($algo, $path)
    {
        return hash_file($algo, $path);
    }

    /**
     * Compare master file hash with local file
     * 
     * @param string $file Path to the file
     * @return boolean
     */
    public static function isHashSameAsSystem($file)
    {
        $currentHash = static::hashFile($file);

        $hashFile = dirname(__FILE__ . '../') . '/hashes.json';
        $Root = dirname(__DIR__, 1) . '/';

        $filepath = explode($Root, $file);
        $pathToAccessFile = explode('/', $filepath[1]);
        $fileToCheck = explode('.php', $pathToAccessFile[2])[0];

        $systemHash = static::getJSON($hashFile, $pathToAccessFile[1], $fileToCheck);

        if (hash_equals($currentHash, $systemHash)) {
            return true;
        }
        return false;
    }

    /**
     * Get and read the content of a JSON
     * 
     * @param string $file
     * @param string $toRead 
     * @param string $toGet
     * @return boolean
     */
    public static function getJSON($file, $toRead = null, $toGet = null)
    {
        $str = file_get_contents($file);
        $decoded = json_decode($str, true);

        if ($toRead === null && $toGet === null) {
            return $decoded;
        } elseif ($toRead !== null) {
            $readed = $decoded[$toRead];
            if ($toGet != null) {
                return $readed[$toGet];
            }
            return $readed;
        }
        return false;
    }

}

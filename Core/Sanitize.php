<?php

/*
 * The MIT License
 *
 * Copyright 2017-2018 Nissar Chababy <contact at funilrys dot com>.
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

/**
 * Class Sanitize | Core/Sanitize.php
 *
 * @package     funombi\Core
 * @author      Nissar Chababy <contact at funilrys dot com>
 * @version     1.0.0
 * @copyright   Copyright (c) 2017-2018, Nissar Chababy
 */

namespace Core;

/**
 * Can be used to satinize $_POST or $_GET.
 *
 * @author Nissar Chababy <contact at funilrys dot com>
 */
class Sanitize
{

    /**
     * Used to filter/sanitize 'post' ($_POST) and get ($_GET)
     * 
     * @note if variable match 'mail' we run static::mail().
     * 
     * @param string|array $option 'get' | 'post' | an array with the data we are working with.
     * @param string|array $toGet If specified return the value of the desired index.
     * @return boolean|string Sanitized $_POST or $_GET.
     */
    public static function filter($option, $toGet = null)
    {
        if ($option === 'get' && !empty($_GET)) {
            $data = $_GET;
        } elseif ($option === 'post' && !empty($_POST)) {
            $data = $_POST;
        } elseif (is_array($option) && Arrays::isAssociative($option)) {
            $data = $option;
        } else {
            return false;
        }

        foreach ($data as $key => $value) {
            if (preg_match("/mail/mi", $key)) {
                /**
                 * We sanitize in case the key have the word `mail`.
                 */
                $value = static::email($value);
            } elseif (preg_match("/url/mi", $key)) {
                /**
                 * We sanitize in case the key have the workd `url`.
                 */
                $value = static::url($value);
            } else {
                $value = static::data($value);
            }

            $data[$key] = $value;
        }

        if ($toGet !== null && !is_array($toGet)) {
            return $data[$toGet];
        } elseif (!Arrays::isAssociative($toGet) && is_array($toGet)) {
            $result = array();

            foreach ($toGet as $value) {
                $result[$value] = $data[$value];
            }
            return $result;
        }
        return $data;
    }

    /**
     * Sanitize a given email.
     * 
     * @param string $email the email to sanitize.
     * @return string
     */
    public static function email(string $email)
    {
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($sanitized, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Sanitize a given string.
     * 
     * @param string $data The data to sanitize.
     * @return string
     */
    public static function data(string $data)
    {
        return filter_var($data, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize a given url.
     * 
     * @param string $data The URL to sanitize.
     * @return mixed
     */
    public static function url(string $data)
    {
        return filter_var($data, FILTER_SANITIZE_URL);
    }

}

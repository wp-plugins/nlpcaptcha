<?php
/*
Plugin Name: NLPCaptcha
Plugin URI: http://www.nlpcaptcha.in/index.php/login
Description: Integrates NLPCaptcha anti-spam solutions with wordpress
Version: 1.12
Author: NLPCaptcha
Email: support@nlpcaptcha.com
Author URI: http://www.nlpcaptcha.in
*/
/* This code is based on code from,
 * and copied, modified and distributed with permission in accordance with its terms:

    Copyright (c) 2008 reCAPTCHA -- http://recaptcha.net
    AUTHORS:
      Mike Crawford
      Ben Maurer
      Jorge Peña

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/
// this is the 'driver' file that instantiates the objects and registers every hook

define('ALLOW_INCLUDE', true);

require_once('nlpcaptcha.php');

$nlpcaptcha = new NLPCaptcha('nlpcaptcha_options');

?>

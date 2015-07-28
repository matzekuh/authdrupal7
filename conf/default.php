<?php
/**
 * Default settings for the authdrupal7 plugin
 *
 * @author Matthias Jung <matzekuh@web.de>
 */

$conf['charset']          = 'utf8';
$conf['server']           = 'localhost';
$conf['user']             = '';
$conf['password']         = '';
$conf['database']         = '';
$conf['debug']            = 0;
$conf['drupalPrefix']     = '';
$conf['drupalRoot']       = '../'

$conf['checkPass']        = 'SELECT pass FROM %{drupal_prefix}users WHERE name=\'%{user}\'';
$conf['getUserInfo']      = 'SELECT name, mail FROM %{drupal_prefix}users WHERE name=\'%{user}\'';

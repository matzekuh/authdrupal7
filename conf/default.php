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

$conf['TablesToLock']     = array();

$conf['checkPass']        = 'SELECT pass FROM %{drupal_prefix}users WHERE name=\'%{user}\'';
$conf['getUserInfo']      = 'SELECT name, mail FROM %{drupal_prefix}users WHERE name=\'%{user}\'';
$conf['getGroups']        = 'SELECT roles.name FROM %{drupal_prefix}users users INNER JOIN %{drupal_prefix}users_roles userroles INNER JOIN %{drupal_prefix}role roles WHERE users.uid = userroles.uid AND roles.rid = userroles.rid AND users.name = \'%{user}\''

$conf['FilterLogin']      = '';
$conf['FilterName']       = '';
$conf['FilterEmail']      = '';
$conf['FilterGroup']      = '';

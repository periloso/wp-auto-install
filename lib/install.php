<?php
/**
 * WordPress Automatic Installator
 * *******************************
 *
 * Copyright (c) 2012 Cifro Nix
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     WpAutoInstall
 * @subpackage  install
 * @author      Cifro Nix <cifro.ni@gmail.com>
 * @copyright   2012 Cifro Nix
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://cifro.uniquebyte.com
 */



// ============================================================
// Load WpAutoInstall
// ------------------------------------------------------------

require_once __DIR__ . '/WpAutoInstall.php';


// ============================================================
// Help
// ------------------------------------------------------------

if(isset($argv[1]) and $argv[1] == "--help"){
	help();
}


// ============================================================
// Mixing config from CLI and from config file
// -----------------------------------------------------------

$configFile =  realpath(arg('config', __DIR__ . '/../config.php'));

if(file_exists($configFile)){
	require_once $configFile;
}else{
	echo "  Error: Config file '{$configFile}' doesn't exist";
	exit(1);
}


$baseUrl = isset($config['siteUrlBase']) ? $config['siteUrlBase'] : '';
$baseDir = isset($config['installDirBase']) ? $config['installDirBase'] : '';

$cliConfig = array(
	'siteTitle'  => arg('site-title'),
	'siteUrl'    => $baseUrl . arg('site-url', $config['siteUrl']),
	'installDir' => $baseDir . arg('install-dir', $config['installDir']),
	'adminEmail' => arg('admin-email'),
	'adminPass'  => arg('admin-pass'),
	'logFile'    => arg('log-file'),
);

$config = array_merge($config, array_filter($cliConfig));

$dbConfig = array(
	'name' => arg('db-name', $db['name']),
	'user' => arg('db-user', $db['user']),
	'pass' => arg('db-pass', isset($db['pass']) ? $db['pass'] : ''),
	'host' => arg('db-host', isset($db['host']) ? $db['host'] : 'localhost'),
);


$db = (object) array_merge($db, $dbConfig);

// Standard WordPress DB contants
define('DB_NAME',     $db->name);
define('DB_USER',     $db->user);
define('DB_PASSWORD', $db->pass);
define('DB_HOST',     $db->host);
define('DB_CHARSET',  'utf8');
define('DB_COLLATE',  '');

define('WPLANG', '');
$table_prefix  = 'wp_';

// cleanup
unset($db, $dbConfig, $cliConfig, $configFile, $baseUrl, $baseDir, $wp, $theme, $installator);


// ===========================================================
// Run the installator
// -----------------------------------------------------------

$installator = new WpAutoInstall($config);

$tasksFile = realpath(arg('tasks', __DIR__ . '/../tasks.php'));

if($tasksFile){
	require_once $tasksFile;
}else{
	echo "  Error: Tasks file '{$tasksFile}' doesn't exist";
	exit(1);
}

$installator->run();


// ===========================================================
// Helper functions
// -----------------------------------------------------------

function arg($arg, $default = null)
{
	global $argv;
	$index = array_search("--{$arg}", $argv);

	if($index !== false and isset($argv[$index + 1]))
		return $argv[$index + 1];
	else
		if(!is_null($default))
			return $default;
		else
			return '';
}



function help()
{
	$v = WpAutoInstall::$version;
echo <<<out

WordPress Automatic Installator v{$v}
====================================

Usage:
  install.bat
  (Without options, will be used default config.php and if exists tasks.php)

  install.bat --config path/to/config.php
  (Custom config.php)

  install.bat --db-name wp_mysite --db-user awsomeuser --db-pass hardcorepass
  (Use default options from config.php and database options from CLI)

Available options:
  --config      <file>    Custom config file
  --tasks       <file>    Custom tasks file
  --site-tile   <string>  Site title
  --site-url    <string>  URL to site without HTTP schema
  --install-dir <path>    Path to dir where will be WordPress installed
  --admin-email <string>  Admin email
  --admin-pass  <string>  Password for admin user
  --log-file    <file>    Logging file
  --db-name     <string>  Database name
  --db-user     <string>  Database user
  --db-pass     <string>  Database password
  --db-host     <string>  Database host
out;
exit(0);
}

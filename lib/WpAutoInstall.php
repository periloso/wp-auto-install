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
 * @author      Cifro Nix <cifro.nix@gmail.com>
 * @copyright   2012 Cifro Nix
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://cifro.uniquebyte.com
 */



/**
 * WordPress Automatic Installator
 *
 * @author Cifro Nix
 *
 * Usage:
 * $installator = new WpAutoInstall($config);
 * $installator->run();
 */
class WpAutoInstall
{
	/**
	 * WpAutoInstall version
	 * @var string
	 */
	public static $version = "1.0";

	/**
	 * Configuration
	 * @var stdClass
	 */
	protected $config;

	/**
	 * Path to downloaded WordPress ZIP file
	 * @var string
	 */
	protected $zipFile;

	/**
	 * Array of additional tasks as callbacks
	 * @var array
	 */
	public $tasks = array();



	/**
	 * Constructor
	 * You don't say O.o
	 */
	public function __construct($config)
	{
		$this->config = (object) $config;
	}



	/**
	 * Run Bitch! Run!
	 * http://youtu.be/ezYgTfRBWyA
	 * :)))
	 */
	public function run()
	{
		$this->downloadWordPress();

		$this->unzip();

		if(isset($this->config->deleteZipFile) and $this->config->deleteZipFile)
			$this->deleteZipFile();

		$this->createDb();

		$this->loadWordPressCore();

		$this->createWpConfigFile();

		$this->installWordPress();

		if(!empty($this->tasks))
			$this->runTasks();
	}



	/**
	 * Download WordPress task
	 */
	public function downloadWordPress()
	{
		$this->log('Download', 'Downloading latest WordPress', 1);

		try{
			$this->exec("{$this->config->wgetExe} -nc http://wordpress.org/latest.zip");
			$this->zipFile = realpath(__DIR__ . '/../latest.zip');
			$this->log('OK', 'Download was successfull');
		}catch(\Exception $e){
			$this->log('Error', $e->getMessage());
			exit(1);
		}
	}



	/**
	 * Unzip task
	 */
	public function unzip()
	{
		$this->log('Unzip', 'Extracting latest WordPress to target directory', 1);

		if(file_exists($this->zipFile)){
			$zip = new ZipArchive;
			if($zip->open($this->zipFile) === TRUE){
				$zip->extractTo(dirname($this->config->installDir));
				$zip->close();

				$this->log('Rename', sprintf('Renaming directory "%s" to "%s"', dirname($this->config->installDir) . '/wordpress', $this->config->installDir), 1);

				if(@rename(dirname($this->config->installDir) . "/wordpress", $this->config->installDir) === false){
					$this->log('Error', 'Renaming was not successfull. Installator is exiting now');
					exit(1);
				}else{
					$this->log('OK', 'Directory renamed successfully');
				}
			}else{
				$this->log('Error', 'Could not open zip file. Installator is exiting now');
				exit(1);
			}
		}else{
			$this->log('Error', "File '$this->zipFile' was not downloaded");
		}
	}



	/**
	 * Delete zip task
	 */
	public function deleteZipFile()
	{
		$this->log('Delete', 'Deleting zip file', 1);

		unlink($this->zipFile);
	}



	/**
	 * Create database task
	 */
	public function createDb()
	{
		$this->log('Database', 'Creating database', 1);

		$dbh = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);

		mysqli_set_charset($dbh, DB_CHARSET);
		mysqli_query($dbh, "SET storage_engine = INNODB;");
		mysqli_query($dbh, "DROP DATABASE IF EXISTS " . DB_NAME . ";");
		mysqli_query($dbh, "CREATE DATABASE " . DB_NAME . ";");
		mysqli_select_db($dbh, DB_NAME);
	}



	/**
	 * Load WordPress core files task
	 */
	public function loadWordPressCore()
	{
		define('ABSPATH', "{$this->config->installDir}/");
		define('WP_INSTALLING', true);
		global $wpdb, $wp_db_version, $current_site, $wp_current_db_version, $table_prefix;
		require_once ABSPATH . '/wp-settings.php';
	}



	/**
	 * Create wp-config.php file task
	 */
	public function createWpConfigFile()
	{
		$this->log('WP config', 'Creating wp-config.php file', 1);

		// mostly from wp-admin/setup-config.php

		define('WP_SETUP_CONFIG', true);

		if(!file_exists(ABSPATH . 'wp-config-sample.php')){
			$this->log('Error', 'Sorry, I need a wp-config-sample.php file to work from. Please re-upload this file from your WordPress installation.');
			exit(1);
		}

		$config_file = file(ABSPATH . 'wp-config-sample.php');

		$secret_keys = array();

		require_once(ABSPATH . WPINC . '/pluggable.php');

		for($i = 0; $i < 8; $i++){
			$secret_keys[] = wp_generate_password( 64, true, true );
		}

		$key = 0;
		foreach($config_file as &$line){
			if(!preg_match('/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match))
				continue;

			$constant = $match[1];
			$padding  = $match[2];

			switch ( $constant ) {
				case 'DB_NAME'     :
				case 'DB_USER'     :
				case 'DB_PASSWORD' :
				case 'DB_HOST'     :
					$line = "define('" . $constant . "'," . $padding . "'" . addcslashes(constant($constant), "\\'") . "');\r\n";
					break;
				case 'AUTH_KEY'         :
				case 'SECURE_AUTH_KEY'  :
				case 'LOGGED_IN_KEY'    :
				case 'NONCE_KEY'        :
				case 'AUTH_SALT'        :
				case 'SECURE_AUTH_SALT' :
				case 'LOGGED_IN_SALT'   :
				case 'NONCE_SALT'       :
					$line = "define('" . $constant . "'," . $padding . "'" . $secret_keys[$key++] . "');\r\n";
					break;
				case 'WP_DEBUG':
					// enables debug mode, it's very useful during development of theme or plugin
					if(isset($this->config->enableDevMode) and $this->config->enableDevMode){
						$line = "define('" . $constant . "', true);\r\n";
						$line .= "define('WP_DEBUG_LOG', true);\r\n";
						$line .= "define('WP_DEBUG_DISPLAY', true);\r\n";
						$line .= "@ini_set('display_errors',1);\r\n";
						$line .= "define('SCRIPT_DEBUG', true);\r\n";
						$line .= "define('SAVEQUERIES', true);\r\n";
						break;
					}
			}
		}
		unset($line);

		if(!is_writable(ABSPATH)){
			$this->log('Error', sprintf("Directory '%s' is not writable", ABSPATH));
		}else{
			$handle = fopen(ABSPATH . 'wp-config.php', 'w');
			foreach($config_file as $line){
				fwrite($handle, $line);
			}
			fclose($handle);
			@chmod(ABSPATH . 'wp-config.php', 0666);
		}

		if(file_exists(ABSPATH . 'wp-config.php')){
			$this->log('OK', 'wp-config.php was created successfully');
		}else{
			$this->log('Error', 'wp-config.php was not created');
		}
	}



	/**
	 * Install WordPress task
	 */
	public function installWordPress()
	{
		$this->log('Install', 'Installation of WordPress', 1);

		if(is_dir($this->config->installDir)){

			$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
			$_SERVER['HTTP_HOST'] = $this->config->siteUrl;
			$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
			require_once ABSPATH . '/wp-includes/wp-db.php';

			wp_install($this->config->siteTitle, 'admin', $this->config->adminEmail, true, null, $this->config->adminPass);

			$this->log('OK', 'Installation of WordPress was successfull');

		}else{
			$this->log('Error', sprintf('Target directory "%s" does not exist.', $this->config->installDir));
			exit(1);
		}
	}



	/**
	 * Runs additional tasks, for example SVN export of theme, etc.
	 * Additional tasks can be added in tasks.php file like this:
	 * $installator->tasks[] = function($installator){ ... some code ... };
	 */
	public function runTasks()
	{
		foreach($this->tasks as $task){
			if(is_callable($task))
				call_user_func($task, $this);
		}
	}




	// ============================================================
	// Helper methods
	// ------------------------------------------------------------


	/**
	 * Magic getter
	 * @param  string $name Name of a property
	 * @return mixed       Value of property
	 */
	public function __get($name)
	{
		if(property_exists($this, $name))
			return $this->$name;
	}



	/**
	 * exec
	 * @param  string $cmd Command
	 * @return string      Output from exec()
	 */
	public function exec($cmd)
	{
		exec($cmd, $output, $res);
		if ($res !== 0) {
			throw new \Exception("exec() exited with code $res");
		}
		return implode(PHP_EOL, $output);
	}



	/**
	 * Displays a message to the output and log it to the file
	 */
	public function log($label, $message, $task = 0)
	{
		$label = $task ? "\n[Task: {$label}]" : "    {$label}:";

		$message = "$label " . $message  . "\n";
		echo $message;
		flush();

		if($this->config->logFile) {
			file_put_contents($this->config->logFile, $message . PHP_EOL, FILE_APPEND);
		}
	}
}

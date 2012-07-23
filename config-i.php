<?php

/*
 * Sample config file for interactive CLI (install-i.bat)
 *
 * Here you can customize WordPress Auto Installator
 * These options can by used with options from command line,
 * where options from CLI takes precedence
 */

$wp = array(
	'siteTitle'      => 'Responsive WP.org Theme', // required
	'siteUrl'        => 'responsive-test', // required
	'installDir'     => 'responsive-test', // required

	'adminEmail'     => 'admin@localhost', // required
	'adminPass'      => 'admin', // required
);

$db = array(
	'name' => 'wp_resposnive_theme_test', // database name required
	'user' => 'root', // database user required
	// 'pass' => '', // database password optional, default: empty string
	// 'host' => 'localhost' // database host optional, default: localhost
);

// optional, this is used by additional SVN WP.org theme export task in tasks.php
$theme = array(
	'theme'        => 'responsive',
	'themeVersion' => '1.7.2',
);

$installator = array(
	'svnExe'        => 'svn.exe', // optional, for tasks with SVN
	'wgetExe'       => __DIR__ . '/tools/wget.exe', // required, needed for downloading WordPress

	'deleteZipFile' => false, // required
	'enableDevMode' => true, // optional

	'logFile'       => __DIR__ . '/install.log', // optional
);


/* Merge all arrays to one */
$config = $wp + $theme + $installator;

<?php

/*
 * Additional tasks to execute after installation of WordPress
 * Can be used for SVN checkout of theme, or plugins, or whatever...
 *
 */

$installator->tasks[] = function($installator) {

	$installator->log('SVN Export', 'Exporting theme from SVN repo', 1);

	try{
		$command  = "{$installator->config->svnExe} export ";
		$command .= "http://themes.svn.wordpress.org/{$installator->config->theme}/{$installator->config->themeVersion}/ ";
		$command .= "{$installator->config->installDir}/wp-content/themes/{$installator->config->theme}";

		$installator->exec($command);
		$installator->log('OK', 'Export from SVN was successfull');
	}catch(\Exception $e){
		$installator->log('Error', $e->getMessage());
		exit(1);
	}
};

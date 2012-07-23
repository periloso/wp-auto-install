# WordPress Automatic Installator
by [Cifro Nix](http://cifro.uniquebyte.com), 2012

## WTF and Why?
You know. You are developing some WordPress plugin or theme, and you have to test it on fresh installation of WordPress. So (in the worst scenario) you have to navigate to wordpress.org, download latest version of WP, extract WP archive somewhere, setup database, run WordPress installation from browser, enter all those stuff to installer form, and so on. You know that story. It's foking annoying! I know that feeling. I wanted on-click installation of WP. You probably too. So here it is your redemption :)

**WordPress Automatic Installator is CLI tool for quick automated installation of WordPress.** You can use prepared config file or use CLI arguments to modify defaults in config file.

## For development only
This script is for WordPress theme/plugin/website development. It is not "online installer on webhosting". For using it on the server you need access to shell and have writing and executing permissions.

## Requirements
* PHP 5.3+ with Zip extension (using ZipArchive class)
* `wget` utility (Linux have it, [Windows can have it too](http://downloads.sourceforge.net/gnuwin32/wget-1.11.4-1-setup.exe))
* it uses latest WordPress
* and all [WordPress requirements](http://wordpress.org/about/requirements/)

Additionally you can install `svn` CLI tools (for Windows: [win32svn v1.7.5](http://sourceforge.net/projects/win32svn/files/1.7.5/Setup-Subversion-1.7.5.msi)).

## Notes
Prepared shell scripts (batch files) are only for Windows for now. You, unix guys, are clever. You can make unix shell scripts easily ;-)
Pull requests are welcomed :)

## Licence
BSD http://www.opensource.org/licenses/bsd-license.php


## Screenshot

![Console input/output of WordPress Automatic Installator](http://files.ukaz.at/images/full/2qr.png)

# Documentation

## Usage

### At one-click!
Click on the `install.bat`! ^_^ wheeee

*Of course you have to change configuration in the `config.php` file* :))

### With config file and CLI options
You can have preconfigured config and optionaly overwrite some options via CLI.

`install.bat --db-name test_my_awsome_wp_site --db-user batman --site-url awsome-site --install-dir awsome-site`

This will override corresponding options in default config file `config.php`. It uses `siteUrlBase` and `installDirBase` options. See `config.php` in this repo.

### With custom config file

`install.bat --config mysite-config.php`

### Interactive CLI

`install-i.bat` is simple interactive CLI, where all needed information will script ask you.


## CLI options
```
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
```

# Tasks file

Whole installation process is divided to individual tasks. They are *core tasks*. You can specify your own tasks after installation of WordPress. For example, you can checkout or export your theme or plugin from SVN or Git repo. Or you can do what you need after installation of WordPress.

API for adding your tasks is simple. In the file `tasks.php` provide your task as closure:

```php
$installator->tasks[] = function($installator){
	// here in closure you have access to $installator object
	$installator->log('My Task', 'Exporting theme from svn');

	$installator->exec('svn export http://url/to/theme/repo');

	// ... and so on
};
```

There is sample `tasks.php` file in this repo.

# The end
That's all. Enjoy!  ^_^ wheeee

You can follow me on Twitter: [@Cifro](http://twitter.com/Cifro)
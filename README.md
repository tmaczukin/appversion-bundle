# AppVersionBundle

This bundle contains tools for managing and retrieving information about your application version.

Supported version format is compatibile with 'Semantic Versioning 2.0.0' specification. More info about 'Semantic Versioning' can be found at http://semver.org/ and https://github.com/mojombo/semver.

## Development

I am using "git-flow" for development workflow. More info about it can be found at https://github.com/nvie/gitflow/.

Version 1.0.0 was released at 2013-12-21.

There are still few tasks on todo list.

Bundle is released on MIT License. Feel free to fork and contribute to this project.

## Installation

Add changes in your composer.json...

	"require": {
		...
		"maczukin/appversion-bundle": "dev-master",
		...
	},
	"scripts": {
		"post-install-cmd": [
			...
			"Maczukin\\AppVersionBundle\\Composer\\ScriptHandler::prepareVersionFile",
			...
		],
		"post-update-cmd": [
			...
			"Maczukin\\AppVersionBundle\\Composer\\ScriptHandler::prepareVersionFile",
			...
		]
	},
	"extra": {
		"maczukin_appversion_parameters": {
			"version-file": "app/config/version.yml"
		}
	}

...app/config/config.yml...

	imports:
		...
		- { resource: parameters.yml }
		- { resource: security.yml }
		- { resource: version.yml }
		...

...and load bundle in app/AppKernel.php

	class AppKernel extends Kernel {

		public function registerBundles() {
			$bundles = array(
				...
				new Maczukin\AppVersionBundle\MaczukinAppVersionBundle(),
				...
			);

## Using

While installing with composer you will be asked to give information about current version, license and contributors of your application. Those information will be saved in config file (default in app/config/version.yml).

Version file schema:

	# This file is auto-generated
	appversion:
		version:
			major: 0
			minor: 5
			patch: 1
			preRelease: dev
			build: build.20131220004631
			deployTimestamp: '2013-12-20 00:51:13'
			license: MIT
			copyright: '2013 by Tomasz Maczukin'
			credits:
			  - 'Tomasz Maczukin <tomasz@maczukin.pl>'
    file: %kernel.root_dir%/config/version.yml

* version - informations about current version of application
  * major - major version number (read about Semantic Versioning for more informations)
  * minor - minor version number (from Semantic Versioning)
  * patch - patch version number (from Semantic Versioning)
  * preRelease - pre-release version label (from Semantic Versioning)
  * build - build version label (from Semantic Versioning)
  * deployTimestamp - timestamp of last application deploy; usefull when you deploy your applications often and you want your users to know when the last version was installed
  * license - identification of license under which you have released your application
  * copyright - your application copyright string
  * credits - array containing names and e-mails of the authors and contributors
* file - path to the version.yml file

You can change those informations at any time by editing the version.yml file or running a console command `$ php app/console appversion --set`. If you want more info about command syntax just run `$ php app/console appversion --help`.

This command executed without any parameters will output current version information. Configuration shown above will generate:

	-----------------------------------------------------------------
		Version info is provided by maczukin/appversion-bundle
	-----------------------------------------------------------------

		 Environment:  dev
			 Version:  0.5.1-dev+build.20131220004631
			   Major:  0
			   Minor:  5
			   Patch:  1
		  PreRelease:  dev
			   Build:  build.20131220004631
	 DeployTimestamp:  2013-12-20 00:51:13
			 License:  MIT
		   Copyright:  2013 by Tomasz Maczukin

	-----------------------------------------------------------------

	 Credits:

		   Tomasz Maczukin  tomasz@maczukin.pl

In addition to the command console there is also available a twig extension. In any of your twig templates you can use 'version_tools' object.

You can output a default version format by using `{{ appversion }}`, set your own format `{{ appversion.versionString('%major%.%minor%.%patch%%pre-release%%build%') }}` or use object like an entity and output individual object fields, eg. `{{ appversion.major }}`.

## TODO

* ~~[1.0.0] add copyrights informations to version info (version.yml and output)~~
* ~~[1.0.0] rename Maczukin\AppVersionBundle\Tool\Version to Maczukin\AppVersionBundle\Tool\VersionTool~~
* ~~[1.0.0] use appversion.file setting in VersionTool~~
* [?] add configuration for continuous integration software (travis or jenkins) with static code analysis and generating documentation from PHPDoc comment blocks
* [?] add unit tests and integrate them with CI software

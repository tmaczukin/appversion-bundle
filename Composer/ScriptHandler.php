<?php

/**
 * The MIT License
 *
 * Copyright (c) 2013 Tomasz Maczukin
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

namespace Maczukin\VersionToolsBundle\Composer;

use Composer\Script\Event,
	Composer\IO\IOInterface;
use Maczukin\VersionToolsBundle\Tool\VersionTool;

/**
 * ScriptHandler
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class ScriptHandler {

	/**
	 * @var IOInterface
	 */
	protected $io;

	/**
	 * @param Event $event
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	static public function prepareVersionFile(Event $event) {
		$instance = new self();
		$instance->doPrepareVersionFile($event);
	}

	/**
	 * @param Event $event
	 * @throws \InvalidArgumentException
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function doPrepareVersionFile(Event $event) {
		$this->io = $event->getIO();
		$extras = $event->getComposer()->getPackage()->getExtra();

		$this->isConfigurationValid($extras);
		if (($version = $this->getVersion($extras['maczukin_version_tools_parameters']['version-file'])) === null) {
			return null;
		}

		$this->printInformation();
		$this->setMainInformation($version);
		$this->setCreditsInformation($version);

		$version->dumpConfig();
	}

	/**
	 * @param array $extras
	 * @return boolean
	 * @throws \InvalidArgumentException
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function isConfigurationValid(array $extras) {
		if (isset($extras['maczukin_version_tools_parameters']) === false ||
				isset($extras['maczukin_version_tools_parameters']['version-file']) === false) {
			throw new \InvalidArgumentException('The extra.maczukin_version_tools_parameters.version-file setting is required to use this script handler.');
		}

		return true;
	}

	/**
	 * @param string $versioningFile
	 * @return VersionTool|null
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getVersion($versioningFile) {
		$version = new VersionTool();
		$version
				->setFile($versioningFile)
				->readFile();

		if ($version->isValid() === true) {
			$this->io->write("<info>Saving current version info existing in {$versioningFile}</info>");
			return null;
		}

		return $version;
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function printInformation() {
		$this->io->write("<info>Set version info:</info>\n");
		$this->io->write("<info>           major</info> - major version number; increment while publishing backward incompatible changes;");
		$this->io->write("<info>           minor</info> - minor version number; increment while publishing new features ant backward compatible changes;");
		$this->io->write("<info>           patch</info> - bugfix version number; increment while publishing hotfix;");
		$this->io->write("<info>     pre-release</info> - additional info, eg: -beta.1, -rc.2, -dev;");
		$this->io->write("<info>           build</info> - build number; increase after each build, eg +build.256, build.20131211100101;");
		$this->io->write("<info>deploy-timestamp</info> - timstamp of last deplop; timestamp format must be known for strtotime()");
		$this->io->write("<info>         license</info> - license identification, eg. MIT, GPL 2.0;");
		$this->io->write("<info>       copyright</info> - copyright informations;");
		$this->io->write("\nVersion information are compatible with 'Semantic Versioning 2.0.0' specification.");
		$this->io->write("More info about 'Semantic Versioning': http://semver.org/ and https://github.com/mojombo/semver\n");
	}

	/**
	 * @param VersionTool $version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setMainInformation(VersionTool $version) {
		$major = $this->getValue('*major', $version->getMajor());
		$minor = $this->getValue('*minor', $version->getMinor());
		$patch = $this->getValue('*patch', $version->getPatch());
		$preRelease = $this->getValue('pre-release', $version->getPreRelease());
		$build = $this->getValue('build', $version->getBuild());
		$deployTimestamp = $this->getValue('deploy-timestamp', $version->getDeployTimestamp());
		$license = $this->getValue('*license', $version->getLicense());
		$copyright = $this->getValue('*copyright', $version->getCopyright());

		$version
				->setMajor($major)
				->setMinor($minor)
				->setPatch($patch)
				->setPreRelease($preRelease)
				->setBuild($build)
				->setDeployTimestamp($deployTimestamp)
				->setLicense($license)
				->setCopyright($copyright);
	}

	/**
	 * @param VersionTool $version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setCreditsInformation(VersionTool $version) {
		$this->io->write("\n<info>Add authors (only [a-zA-Z0-9 ]+ for Author Name is allowed!)</info>");

		$credits = array();
		foreach ($version->getCredits() as $author => $email) {
			if (($author = $this->getValue('Author Name <author@email>', "{$author} <{$email}>")) !== null) {
				$credits[] = $author;
			}
		}
		$version
				->clearCredits()
				->setCredits($credits);

		do {
			if (($author = $this->io->ask('Author Name <author@email> (leave empty for continue):', null)) !== null) {
				$version->addAuthor($author);
			}
		} while ($author !== null);
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getValue($label, $default) {
		return $this->io->ask(sprintf('%s [<comment>%s</comment>]: ', $label, $default), $default);
	}

}

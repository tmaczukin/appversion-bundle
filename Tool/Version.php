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

namespace Maczukin\VersionToolsBundle\Tool;

use Symfony\Component\DependencyInjection\ContainerInterface,
	Symfony\Component\Filesystem\Filesystem,
	Symfony\Component\Yaml\Yaml;

/**
 * Version
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class Version {

	const STRING_FORMAT = '%major%.%minor%.%patch%%pre-release%%build% (deploy-ts: %deploy-timestamp%) %commit%';

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $configFile;

	/**
	 * @var string
	 */
	protected $commit;

	/**
	 * @var string
	 */
	protected $environment;

	/**
	 * @var integer
	 */
	protected $major = 0;

	/**
	 * @var integer
	 */
	protected $minor = 1;

	/**
	 * @var integer
	 */
	protected $patch = 0;

	/**
	 * @var string
	 */
	protected $preRelease;

	/**
	 * @var string
	 */
	protected $build;

	/**
	 * @var string
	 */
	protected $deployTimestamp;

	/**
	 * @var string
	 */
	protected $license;

	/**
	 * @var array
	 */
	protected $credits = array();

	/**
	 * @param ContainerInterface $container
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setContainer(ContainerInterface $container) {
		$this->container = $container;
		$this->configFile = realpath($this->container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'config').DIRECTORY_SEPARATOR.'version.yml';
		$this->environment = $this->container->get('kernel')->getEnvironment();

		$this->setCommit();
		$this->setMajor($this->getContainerParameter('major'));
		$this->setMinor($this->getContainerParameter('minor'));
		$this->setPatch($this->getContainerParameter('patch'));
		$this->setPreRelease($this->getContainerParameter('preRelease'));
		$this->setBuild($this->getContainerParameter('build'));
		$this->setDeployTimestamp($this->getContainerParameter('deployTimestamp'));
		$this->setLicense($this->getContainerParameter('license'));
		$this->setCredits($this->getContainerParameter('credits'));

		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	private function getContainerParameter($name) {
		$parameter = 'maczukin_version_tools.version.'.$name;

		if ($this->container->hasParameter($parameter) === true) {
			return $this->container->getParameter($parameter);
		}

		if (property_exists($this, $name) === true) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * @param string $configFile
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setConfigFile($configFile) {
		$this->configFile = $configFile;

		return $this;
	}

	/**
	 * @param string $format
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getVersionString($format = self::STRING_FORMAT) {
		$preRelease = $this->preRelease !== null ? '-'.$this->preRelease : null;
		$build = $this->build !== null ? '+'.$this->build : null;
		$deployTimestamp = $this->getDeployTimestamp() !== null ? $this->getDeployTimestamp() : null;
		$commit = $this->commit !== null ? "[commit: {$this->commit}]" : null;

		$replaces = array(
			'%major%' => $this->major,
			'%minor%' => $this->minor,
			'%patch%' => $this->patch,
			'%pre-release%' => $preRelease,
			'%build%' => $build,
			'%deploy-timestamp%' => $deployTimestamp,
			'%commit%' => $commit,
		);

		return trim(str_replace(array_keys($replaces), $replaces, $format));
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function __toString() {
		return $this->getVersionString();
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getCommit() {
		return $this->commit;
	}

	/**
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setCommit() {
		if ($this->environment !== 'dev') {
			return $this;
		}

		$commit = system('git describe 2>/dev/null', $return);

		$error = $return !== 0 ? 'Error occured or git is not supported!' : null;
		$this->commit = empty($commit) !== true ? $commit : $error;

		return $this;
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * @return integer
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getMajor() {
		return $this->major;
	}

	/**
	 * @param integer $major
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setMajor($major) {
		$this->major = (integer) $major;

		return $this;
	}

	/**
	 * @return integer
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getMinor() {
		return $this->minor;
	}

	/**
	 * @param integer $minor
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setMinor($minor) {
		$this->minor = (integer) $minor;

		return $this;
	}

	/**
	 * @return integer
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getPatch() {
		return $this->patch;
	}

	/**
	 * @param integer $patch
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setPatch($patch) {
		$this->patch = (integer) $patch;

		return $this;
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getPreRelease() {
		return $this->preRelease;
	}

	/**
	 * @param string $preRelease
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setPreRelease($preRelease) {
		$preRelease = preg_replace('/[^A-Za-z0-9\.]/', '.', trim($preRelease));
		$preRelease = trim(preg_replace('/\.+/', '.', $preRelease), '.');

		$this->preRelease = empty($preRelease) === true ? null : $preRelease;

		return $this;
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getBuild() {
		return $this->build;
	}

	/**
	 * @param string $build
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setBuild($build) {
		$build = preg_replace('/[^A-Za-z0-9\.]/', '.', trim($build));
		$build = trim(preg_replace('/\.+/', '.', $build), '.');

		$this->build = empty($build) === true ? null : $build;

		return $this;
	}

	/**
	 * @return string
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getDeployTimestamp() {
		return $this->deployTimestamp;
	}

	/**
	 * @param mixed $deployTimestamp
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setDeployTimestamp($deployTimestamp) {
		$this->deployTimestamp = empty($deployTimestamp) !== true ? date('Y-m-d H:i:s', strtotime($deployTimestamp)) : null;

		return $this;
	}

	/**
	 * @return string
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getLicense() {
		return $this->license;
	}

	/**
	 * @param string $license
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setLicense($license) {
		$this->license = $license;

		return $this;
	}

	/**
	 * @return array
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getCredits() {
		return $this->credits;
	}

	/**
	 * @param array $credits
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setCredits(array $credits) {
		foreach ($credits as $author) {
			$this->addAuthor($author);
		}

		return $this;
	}

	/**
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function clearCredits() {
		$this->credits = array();

		return $this;
	}

	/**
	 * @param string $author
	 * @return Version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function addAuthor($author) {
		if (preg_match('/([a-zA-Z0-9 ]+)\s*(<([^>]+)>)?/', $author, $matches) > 0) {
			$this->credits[trim($matches[1])] = trim(isset($matches[3]) === true ? $matches[3] : null);
		}

		return $this;
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function dumpConfig() {
		$credits = array();
		foreach ($this->credits as $author => $email) {
			$email = empty($email) === false ? " <{$email}>" : null;
			$credits[] = "{$author}{$email}";
		}

		$config = array(
			'maczukin_version_tools' => array(
				'version' => array(
					'major' => $this->major,
					'minor' => $this->minor,
					'patch' => $this->patch,
					'preRelease' => $this->preRelease,
					'build' => $this->build,
					'deployTimestamp' => $this->deployTimestamp,
					'license' => $this->license,
					'credits' => $credits,
				),
			),
		);

		$fileSystem = new Filesystem();
		$fileSystem->dumpFile($this->configFile, "# This file is auto-generated\n".Yaml::dump($config, 4));
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function readFile() {
		$fileSystem = new Filesystem();
		if ($fileSystem->exists($this->configFile) === true) {
			$yaml = Yaml::parse(file_get_contents($this->configFile));
			$config = &$yaml['maczukin_version_tools']['version'];

			$this
					->setMajor($this->getValueOrDefault($config['major'], $this->major))
					->setMinor($this->getValueOrDefault($config['minor'], $this->minor))
					->setPatch($this->getValueOrDefault($config['patch'], $this->patch))
					->setPreRelease($this->getValueOrDefault($config['preRelease'], $this->preRelease))
					->setBuild($this->getValueOrDefault($config['build'], $this->build))
					->setDeployTimestamp($this->getValueOrDefault($config['deployTimestamp'], $this->deployTimestamp))
					->setLicense($this->getValueOrDefault($config['license'], $this->license))
					->setCredits($this->getValueOrDefault($config['credits'], $this->credits));
		}
	}

	/**
	 * @param mixed $value
	 * @param mixed $default
	 * @return mixed
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getValueOrDefault($value, $default) {
		return isset($value) === true ? $value : $default;
	}

	/**
	 * @return boolean
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function isValid() {
		return $this->major !== null &&
				$this->minor !== false &&
				$this->patch !== null &&
				$this->license !== null;
	}

}

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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * VersionTool
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class VersionTool {

	const STRING_FORMAT = '%major%.%minor%.%patch%%pre-release%%build% (deploy-ts: %deploy-timestamp%) %commit%';

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var string
	 */
	protected $realFile;

	/**
	 * @var string
	 */
	protected $commit;

	/**
	 * @var string
	 */
	protected $copyright;

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
	 * @var VersionFileTool
	 */
	protected $versionFileTool;

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function __construct() {
		$this->versionFileTool = new VersionFileTool($this);
	}

	/**
	 * @param ContainerInterface $container
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setContainer(ContainerInterface $container) {
		$this->container = $container;
		$this->environment = $this->container->get('kernel')->getEnvironment();

		$this->setCommit()
				->setMajor($this->getContainerParameter('version.major'))
				->setMinor($this->getContainerParameter('version.minor'))
				->setPatch($this->getContainerParameter('version.patch'))
				->setPreRelease($this->getContainerParameter('version.preRelease'))
				->setBuild($this->getContainerParameter('version.build'))
				->setDeployTimestamp($this->getContainerParameter('version.deployTimestamp'))
				->setLicense($this->getContainerParameter('version.license'))
				->setCopyright($this->getContainerParameter('version.copyright'))
				->setCredits($this->getContainerParameter('version.credits'))
				->setFile($this->getContainerParameter('file'));

		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	private function getContainerParameter($name) {
		$parameter = 'maczukin_version_tools.'.$name;

		if ($this->container->hasParameter($parameter) === true) {
			return $this->container->getParameter($parameter);
		}

		$name = str_replace('version.', '', $name);
		if (property_exists($this, $name) === true) {
			return $this->$name;
		}

		return null;
	}

	/**
	 * @return string
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @param string $file
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setFile($file) {
		$this->realFile = dirname($file).DIRECTORY_SEPARATOR.basename($file);

		list($rootDir, $appDir) = $this->getRootAndAppDir();

		if (strpos($file, $appDir) === 0) {
			$this->file = str_replace($appDir, '%kernel.root_dir%', $file);
		}
		elseif (strpos($file, $rootDir) === 0) {
			$this->file = str_replace($rootDir, '%kernel.root_dir%/..', $file);
		}
		elseif (preg_match('#^/#', $file) < 1) {
			$file = $rootDir.DIRECTORY_SEPARATOR.$file;
			return $this->setFile($file);
		}
		elseif (preg_match('#^/#', $file) >= 1) {
			$this->file = $file;
		}

		return $this;
	}

	/**
	 * @return array
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	private function getRootAndAppDir() {
		if ($this->container === null) {
			$rootDir = getcwd();
			$appDir = realpath($rootDir.DIRECTORY_SEPARATOR.'app');
		}
		else {
			$appDir = realpath($this->container->get('kernel')->getRootDir());
			$rootDir = realpath($appDir.DIRECTORY_SEPARATOR.'..');
		}

		return array($rootDir, $appDir);
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
			'%copyright%' => $this->copyright,
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
	 * @return VersionTool
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
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function getCopyright() {
		return $this->copyright;
	}

	/**
	 * @param string $copyright
	 * @return VersionTool
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setCopyright($copyright) {
		$this->copyright = $copyright;

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
	 * @return VersionTool
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
	 * @return VersionTool
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
	 * @return VersionTool
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
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setPreRelease($preRelease) {
		$this->preRelease = $this->normalizeLabelString($preRelease);

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
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setBuild($build) {
		$this->build = $this->normalizeLabelString($build);

		return $this;
	}

	/**
	 * @param string $label
	 * @return string
	 * @author	Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function normalizeLabelString($label) {
		$label = preg_replace('/[^A-Za-z0-9\.]/', '.', trim($label));
		$label = trim(preg_replace('/\.+/', '.', $label), '.');

		return empty($label) === true ? null : $label;
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
	 * @return VersionTool
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
	 * @return VersionTool
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
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function setCredits(array $credits) {
		foreach ($credits as $author) {
			$this->addAuthor($author);
		}

		return $this;
	}

	/**
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function clearCredits() {
		$this->credits = array();

		return $this;
	}

	/**
	 * @param string $author
	 * @return VersionTool
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
		$this->versionFileTool->dump($this->realFile);
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function readFile() {
		$this->versionFileTool->read($this->realFile);
	}

	/**
	 * @return boolean
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function isValid() {
		return $this->major !== null &&
				$this->minor !== false &&
				$this->patch !== null &&
				$this->license !== null &&
				$this->copyright !== null;
	}

}

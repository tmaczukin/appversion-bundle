<?php

/**
 * The MIT License
 *
 * Copyright (c) 2013 Tomasz Maczukin <tomasz@maczukin.pl>
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

namespace Maczukin\AppVersionBundle\Tool;

use Symfony\Component\Filesystem\Filesystem,
	Symfony\Component\Yaml\Yaml;

/**
 * VersionFileTool
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class VersionFileTool {

	/**
	 * @var VersionTool
	 */
	protected $versionTool;

	/**
	 * @param VersionTool $versionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function __construct(VersionTool $versionTool) {
		$this->versionTool = $versionTool;
	}

	/**
	 * @param string $file
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function read($file) {
		$fileSystem = new Filesystem();
		if ($fileSystem->exists($file) === false) {
			return;
		}

		$yaml = Yaml::parse(file_get_contents($file));
		$config = &$yaml['maczukin_app_version']['version'];
		$applyAssets = &$yaml['maczukin_app_version']['applyAssets'];

		$this->versionTool
				->setMajor($this->getValueOrDefault($config['major'], $this->versionTool->getMajor()))
				->setMinor($this->getValueOrDefault($config['minor'], $this->versionTool->getMinor()))
				->setPatch($this->getValueOrDefault($config['patch'], $this->versionTool->getPatch()))
				->setPreRelease($this->getValueOrDefault($config['preRelease'], $this->versionTool->getPreRelease()))
				->setBuild($this->getValueOrDefault($config['build'], $this->versionTool->getBuild()))
				->setDeployTimestamp($this->getValueOrDefault($config['deployTimestamp'], $this->versionTool->getDeployTimestamp()))
				->setLicense($this->getValueOrDefault($config['license'], $this->versionTool->getLicense()))
				->setCopyright($this->getValueOrDefault($config['copyright'], $this->versionTool->getCopyright()))
				->setCredits($this->getValueOrDefault($config['credits'], $this->versionTool->getCredits()))
				->setApplyAssets($this->getValueOrDefault($applyAssets, $this->versionTool->getApplyAssets()));
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
	 * @param string $file
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function dump($file) {
		$credits = array();
		foreach ($this->versionTool->getCredits() as $author => $email) {
			$email = empty($email) === false ? " <{$email}>" : null;
			$credits[] = "{$author}{$email}";
		}

		$config = array(
			'maczukin_app_version' => array(
				'version' => array(
					'major' => $this->versionTool->getMajor(),
					'minor' => $this->versionTool->getMinor(),
					'patch' => $this->versionTool->getPatch(),
					'preRelease' => $this->versionTool->getPreRelease(),
					'build' => $this->versionTool->getBuild(),
					'deployTimestamp' => $this->versionTool->getDeployTimestamp(),
					'license' => $this->versionTool->getLicense(),
					'copyright' => $this->versionTool->getCopyright(),
					'credits' => $credits,
				),
				'file' => $this->versionTool->getFile(),
				'applyAssets' => $this->versionTool->getApplyAssets(),
			),
		);

		$message = sprintf("# This file is auto-generated\n# Last modified on %s\n", date('c'));

		$fileSystem = new Filesystem();
		$fileSystem->dumpFile($file, $message.Yaml::dump($config, 4));
	}

}

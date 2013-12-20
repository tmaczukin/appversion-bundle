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

namespace Maczukin\VersionToolsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
	Symfony\Component\Console\Formatter\OutputFormatterStyle,
	Symfony\Component\Console\Helper\DialogHelper,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;
use Maczukin\VersionToolsBundle\Tool\VersionTool;

/**
 * VersionCommand
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class VersionCommand extends ContainerAwareCommand {

	/**
	 * @var DialogHelper
	 */
	protected $dialogHelper;

	/**
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var VersionTool
	 */
	protected $versionTool;

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function configure() {
		$this->setName('appversion')
				->setDescription('Show or set version informations.')
				->addOption('set', null, InputOption::VALUE_NONE, 'Use to enter write mode')
				->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Set write mode to interactive')
				->addOption('add-authors', null, InputOption::VALUE_NONE, 'Use to run interactive add authors mode')
				->addOption('major', null, InputOption::VALUE_REQUIRED, 'Set major version part')
				->addOption('minor', null, InputOption::VALUE_REQUIRED, 'Set minor version part')
				->addOption('patch', null, InputOption::VALUE_REQUIRED, 'Set patch version part')
				->addOption('pre-release', null, InputOption::VALUE_REQUIRED, 'Set preRelease version part')
				->addOption('build', null, InputOption::VALUE_REQUIRED, 'Set build version part')
				->addOption('deploy-timestamp', null, InputOption::VALUE_REQUIRED, 'Set deploy timestamp')
				->addOption('license', null, InputOption::VALUE_REQUIRED, 'Set application license id')
				->addOption('copyright', null, InputOption::VALUE_REQUIRED, 'Set application copyright');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$this->prepareFormatter();
		$this->addAuthors();
		$this->setVersionInfo();
		$this->getVersionInfo();
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function prepareFormatter() {
		$formatter = $this->output->getFormatter();
		$formatter->setStyle('border', new OutputFormatterStyle('green', 'black'));
		$formatter->setStyle('row', new OutputFormatterStyle('cyan', 'black'));
		$formatter->setStyle('value', new OutputFormatterStyle('white', 'black', array('bold')));
		$formatter->setStyle('value-important', new OutputFormatterStyle('red', 'black', array('bold')));
		$formatter->setStyle('value-additional', new OutputFormatterStyle('green', 'black', array('bold')));
		$formatter->setStyle('warning', new OutputFormatterStyle('white', 'red', array('bold')));
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	public function addAuthors() {
		if ((boolean) $this->input->getOption('add-authors') !== true) {
			return null;
		}

		$dialog = $this->getDialogHelper();
		$version = $this->getVersionTool();

		$this->output->writeln('<info>Insert user data or live empty to exit');

		do {
			if (($author = $dialog->ask($this->output, '<row>Author Name <author@email>: <value>')) !== null) {
				$version->addAuthor($author);
			}
		} while ($author !== null);

		$version->dumpConfig();
		$this->output->writeln("\n<warning>                       ");
		$this->output->writeln('<warning>  Credits info saved!  ');
		$this->output->writeln('<warning>                       ');
		$this->output->writeln('');
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setVersionInfo() {
		if ((boolean) $this->input->getOption('set') !== true) {
			return null;
		}

		if ((boolean) $this->input->getOption('interactive') === true) {
			$this->setVersionInfoInteractive();
		}
		else {
			$this->setVersionInfoFromArguments();
		}

		$this->getVersionTool()->dumpConfig();
		$this->output->writeln("\n<warning>                       ");
		$this->output->writeln('<warning>  Version info saved!  ');
		$this->output->writeln('<warning>                       ');
		$this->output->writeln('');
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setVersionInfoFromArguments() {
		$version = $this->getVersionTool();

		$this->setValue($this->input->getOption('major'), array($version, 'setMajor'));
		$this->setValue($this->input->getOption('minor'), array($version, 'setMinor'));
		$this->setValue($this->input->getOption('patch'), array($version, 'setPatch'));
		$this->setValue($this->input->getOption('pre-release'), array($version, 'setPreRelease'));
		$this->setValue($this->input->getOption('build'), array($version, 'setBuild'));
		$this->setValue($this->input->getOption('deploy-timestamp'), array($version, 'setDeployTimestamp'));
		$this->setValue($this->input->getOption('license'), array($version, 'setLicense'));
		$this->setValue($this->input->getOption('copyright'), array($version, 'setCopyright'));
	}

	/**
	 * @param mixed $value
	 * @param callable $callback
	 * @return VersionCommand
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	private function setValue($value, $callback) {
		if ($value !== null) {
			call_user_func($callback, $value);
		}

		return $this;
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setVersionInfoInteractive() {
		$dialog = $this->getDialogHelper();
		$version = $this->getVersionTool();

		$defaultMajor = $version->getMajor();
		$version->setMajor($dialog->ask($this->output, sprintf('<value>major [<value-important>%s<value>]: ', $defaultMajor), $defaultMajor));

		$defaultMinor = $version->getMinor();
		$version->setMinor($dialog->ask($this->output, sprintf('<value>minor [<value-important>%s<value>]: ', $defaultMinor), $defaultMinor));

		$defaultPatch = $version->getPatch();
		$version->setPatch($dialog->ask($this->output, sprintf('<value>patch [<value-important>%s<value>]: ', $defaultPatch), $defaultPatch));

		$defaultPreRelease = $version->getPreRelease();
		$version->setPreRelease($dialog->ask($this->output, sprintf('<value>pre-release [<value-important>%s<value>]: ', $defaultPreRelease), $defaultPreRelease));

		$defaultBuild = $version->getBuild();
		$version->setBuild($dialog->ask($this->output, sprintf('<value>build [<value-important>%s<value>]: ', $defaultBuild), $defaultBuild));

		$defaultDeployTimestamp = $version->getDeployTimestamp();
		$version->setDeployTimestamp($dialog->ask($this->output, sprintf('<value>deploy-timestamp [<value-important>%s<value>]: ', $defaultDeployTimestamp), $defaultDeployTimestamp));

		$defaultLicense = $version->getLicense();
		$version->setLicense($dialog->ask($this->output, sprintf('<value>license [<value-important>%s<value>]: ', $defaultLicense), $defaultLicense));

		$defaultCopyright = $version->getCopyright();
		$version->setCopyright($dialog->ask($this->output, sprintf('<value>copyright [<value-important>%s<value>]: ', $defaultCopyright), $defaultCopyright));
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getVersionInfo() {
		$version = $this->getVersionTool();

		$this->printHeader();
		$this->printBasicInfo($version);
		$this->printDetailedInfo($version);
		$this->printAuthorsInfo($version);
	}

	/**
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function printHeader() {
		$this->output->writeln("<border>-----------------------------------------------------------------");
		$this->output->writeln("<info>    Version info is provided by maczukin/version-tools-bundle");
		$this->output->writeln("<border>-----------------------------------------------------------------");
	}

	/**
	 * @param VersionTool $version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function printBasicInfo(VersionTool $version) {
		$this->output->writeln('');
		$this->output->writeln(sprintf("<row>     Environment:  <value>%s", $version->getEnvironment()));
		$this->output->writeln($version->getVersionString("<row>         Version:  <value-important>%major%.%minor%.%patch%%pre-release%<value-additional>%build%"));
	}

	/**
	 * @param VersionTool $version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function printDetailedInfo(VersionTool $version) {
		$this->printField("<row>           Major:  <value>%s", $version->getMajor());
		$this->printField("<row>           Minor:  <value>%s", $version->getMinor());
		$this->printField("<row>           Patch:  <value>%s", $version->getPatch());
		$this->printField("<row>      PreRelease:  <value>%s", $version->getPreRelease());
		$this->printField("<row>           Build:  <value>%s", $version->getBuild());
		$this->printField("<row> DeployTimestamp:  <value>%s", $version->getDeployTimestamp());
		$this->printField("<row>         License:  <value>%s", $version->getLicense());
		$this->printField("<row>       Copyright:  <value>%s", $version->getCopyright());
		$this->printField("<row>          Commit:  <value>%s", $version->getCommit());
	}

	/**
	 * @param string $pattern
	 * @param mixed $value
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	private function printField($pattern, $value) {
		if ($value !== null) {
			$this->output->writeln(sprintf($pattern, $value));
		}
	}

	/**
	 * @param VersionTool $version
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function printAuthorsInfo(VersionTool $version) {
		$credits = $version->getCredits();
		if (empty($credits) === true) {
			return null;
		}

		$this->output->writeln("\n<border>-----------------------------------------------------------------");
		$this->output->writeln("\n<row> Credits:\n");
		foreach ($credits as $author => $email) {
			$this->output->writeln(sprintf("<value>  %s  <row>%s", str_pad($author, 20, ' ', STR_PAD_LEFT), $email));
		}
	}

	/**
	 * @return DialogHelper
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getDialogHelper() {
		if ($this->dialogHelper === null) {
			$this->dialogHelper = new DialogHelper();
		}

		return $this->dialogHelper;
	}

	/**
	 * @return VersionTool
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function getVersionTool() {
		if ($this->versionTool === null) {
			$this->versionTool = $this->getContainer()->get('maczukin_version_tools.version');
		}

		return $this->versionTool;
	}

}

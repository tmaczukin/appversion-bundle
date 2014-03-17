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

namespace Maczukin\AppVersionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
	Symfony\Component\DependencyInjection\DefinitionDecorator,
	Symfony\Component\Config\FileLocator,
	Symfony\Component\HttpKernel\DependencyInjection\Extension,
	Symfony\Component\DependencyInjection\Loader;

/**
 * MaczukinAppVersionExtension
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class MaczukinAppVersionExtension extends Extension {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container) {
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$this->setVersionParameters($container, $config);
		$this->setFileParameter($container, $config);
		$this->applyAssetsVersion($container, $config);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
	}

	/**
	 * @param ContainerBuilder $container
	 * @param array $config
	 * @return boolean
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setVersionParameters(ContainerBuilder $container, array $config) {
		if (isset($config['version']) !== true) {
			return false;
		}

		foreach ($config['version'] as $key => $value) {
			$container->setParameter('appversion.version.'.$key, $value);
		}
	}

	/**
	 * @param ContainerBuilder $container
	 * @param array $config
	 * @return boolean
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function setFileParameter(ContainerBuilder $container, array $config) {
		if (isset($config['file']) !== true) {
			return false;
		}

		$container->setParameter('appversion.file', $config['file']);
	}

	/**
	 * @param ContainerBuilder $container
	 * @param array $config
	 * @return boolean
	 * @author Tomasz Maczukin <tomasz@maczukin.pl>
	 */
	protected function applyAssetsVersion(ContainerBuilder $container, array $config) {
	        if (isset($config['applyAssets']) !== true) {
			return false;
		}

		$container->setParameter('appversion.applyAssets', $config['applyAssets']);

		if ($config['applyAssets'] !== true || isset($config['version']['deployTimestamp']) !== true) {
			return false;
		}

		$package = new DefinitionDecorator('templating.asset.url_package');
		$package
				->setPublic(false)
				->replaceArgument(1, strtotime($config['version']['deployTimestamp']));
		$container->setDefinition('templating.asset.default_package', $package);
	}

}

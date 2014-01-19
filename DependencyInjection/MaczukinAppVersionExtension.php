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

		if (isset($config['version']) === true) {
			foreach ($config['version'] as $key => $value) {
				$container->setParameter('appversion.version.'.$key, $value);
			}
		}

		if (isset($config['file']) === true) {
			$container->setParameter('appversion.file', $config['file']);
		}

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
	}

}

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

namespace Maczukin\VersionToolsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
	Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 *
 * @author Tomasz Maczukin <tomasz@maczukin.pl>
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('maczukin_version_tools');

		$rootNode
				->children()
				->arrayNode('version')
				->children()
				->integerNode('major')->defaultValue(0)->min(0)->end()
				->integerNode('minor')->defaultValue(1)->min(0)->end()
				->integerNode('patch')->defaultValue(0)->min(0)->end()
				->scalarNode('preRelease')->defaultNull()->end()
				->scalarNode('build')->defaultNull()->end()
				->scalarNode('deployTimestamp')->defaultNull()->end()
				->scalarNode('license')->defaultNull()->end()
				->scalarNode('copyright')->defaultNull()->end()
				->arrayNode('credits')->prototype('scalar')->defaultNull()->end()->end()
				->end()
				->end()
				->scalarNode('file')->defaultNull('%kernel.root_dir%/config/version.yml')->end()
				->end();

		return $treeBuilder;
	}

}

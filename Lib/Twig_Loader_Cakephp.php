<?php

class Twig_Loader_Cakephp implements Twig_LoaderInterface {

/**
 * @{inheritDoc}
 */
    public function getSource($name) {
        $name = $this->resolveFileName($name);
        return file_get_contents($name);
    }

/**
 * @{inheritDoc}
 */
    public function getCacheKey($name) {
        return $this->resolveFileName($name);
    }

/**
 * @{inheritDoc}
 */
    public function isFresh($name, $time) {
        $name = $this->resolveFileName($name);
        return filemtime($name) < $time;
    }

/**
 * @{inheritDoc}
 */
		private function resolveFileName($name) {
				$name = str_replace('/', DS, $name);
				$viewPaths = App::path('View');
				foreach ($viewPaths as $path) {
						if (strpos($name, $path) !== false && strpos($name, $path . 'Layouts') === false) {
								$this->_lastView = substr($name, 0, strrpos($name, DS) + 1);
						}
				}

				if (file_exists($name)) {
						return $name;
				}

				foreach ($viewPaths as $path) {
						if (file_exists($path . DS. $name)) {
								return $path . DS. $name;
						} elseif ($this->_lastView && file_exists($this->_lastView . $name)) {
								return $this->_lastView . $name;
						} elseif (file_exists($path . DS . 'Layouts' . DS . $name)) {
								return $path . DS . 'Layouts' . DS . $name;
						}
				}

				list($plugin, $file) = pluginSplit($name);
				if ($plugin === null || !CakePlugin::loaded($plugin)) {
						$paths = App::path('View');
						foreach ($paths as $path) {
								$filePath = $path . $file . TwigView::EXT;
								if (file_exists($filePath)) {
										return $filePath;
								}
						}

						throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
				}

				$filePath = CakePlugin::path($plugin) . 'View' . DS . $file . TwigView::EXT;
				if (file_exists($filePath)) {
						return $filePath;
				}

				throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
		}
}

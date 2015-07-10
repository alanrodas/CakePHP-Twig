<?php
/**
 * TwigView for CakePHP
 *
 * About Twig
 *  http://www.twig-project.org/
 *
 * @package TwigView
 * @subpackage TwigView.View
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @link http://github.com/m3nt0r My GitHub
 * @link http://twitter.com/m3nt0r My Twitter
 * @author Graham Weldon (http://grahamweldon.com)
 * @author Cees-Jan Kiewiet (http://wyrihaximus.net)
 * @license The MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('View', 'View');
App::uses('Twig_Loader_Cakephp', 'TwigView.Lib');

/**
 * TwigView for CakePHP
 *
 * @version 0.5
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @link http://github.com/m3nt0r/cakephp-twig-view GitHub
 * @package app.views
 * @subpackage app.views.twig
 */
class TwigView extends View {

/**
 * File extension
 *
 * @var string
 */
	const EXT = '.twig';

/**
 * File extension
 *
 * @var string
 */
	public $ext = self::EXT;

/**
 * Twig Environment Instance
 *
 * @var Twig_Environment
 */
	public $Twig;

/**
 * Collection of paths.
 * These are stripped from $___viewFn.
 *
 * @var array
 */
	public $templatePaths = array();

/**
 * Constructor
 * Overridden to provide Twig loading
 *
 * @param Controller $Controller Controller
 */
	public function __construct(Controller $Controller = null) {
		$this->Twig = new Twig_Environment(new Twig_Loader_Cakephp(array()), array(
			'cache' => Configure::read('TwigView.Cache'),
			'charset' => strtolower(Configure::read('App.encoding')),
			'auto_reload' => Configure::read('debug') > 0,
			'autoescape' => false,
			'debug' => Configure::read('debug') > 0
		));

		CakeEventManager::instance()->dispatch(new CakeEvent('Twig.TwigView.construct', $this, array(
			'TwigEnvironment' => $this->Twig,
		)));

		parent::__construct($Controller);

		if (isset($Controller->theme)) {
			$this->theme = $Controller->theme;
		}
		$this->ext = self::EXT;
	}

/**
 * Render the view
 *
 * @param string $___viewFn
 * @param string $___dataForView
 * @return void
 */
	protected function _render($___viewFn, $___dataForView = array()) {
		$isCtpFile = (substr($___viewFn, -3) === 'ctp');

		if (empty($___dataForView)) {
			$___dataForView = $this->viewVars;
		}

		if ($isCtpFile) {
			$out = parent::_render($___viewFn, $___dataForView);
		} else {
			ob_start();
			// Setup the helpers from the new Helper Collection
			$helpers = array();
			$loadedHelpers = $this->Helpers->loaded();
			foreach ($loadedHelpers as $helper) {
				$name = Inflector::variable($helper);
				$helpers[$name] = $this->loadHelper($helper);
			}

			$data = array_merge($___dataForView, $helpers);
			$data['_view'] = $this;
			$data['config'] = Configure::read();

			$relativeFn = str_replace($this->templatePaths, '', $___viewFn);
			$template = $this->Twig->loadTemplate($relativeFn);
			echo $template->render($data);
			$out = ob_get_clean();
		}

		return $out;
	}
}

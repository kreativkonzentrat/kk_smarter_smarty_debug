<?php
/**
 * helper for generating debug output
 *
 * @package     kk_smarter_smarty_debug
 * @createdAt   10.06.13 - 14:32
 * @author      Felix Moche <felix@kreativkonzentrat.de>
 * @copyright   2013 Kreativkonzentrat GbR - Niels Baumbach, Felix Moche, Martin Zilz
 */

/**
 * Class kk_smarter_smarty_debug_util
 */
class kk_smarter_smarty_debug_util
{
	private $userDebug = array();

	/**
	 * custom debug output
	 *
	 * @param $var
	 * @param $name
	 */
	function dump ($var, $name = 'dumped_var') {
		$this->userDebug[$name] = $var;
	}

	/**
	 * @return array
	 */
	function getUserDebug () {
		return $this->userDebug;
	}
}

/**
 * Class kk_smarter_smarty_debug
 */
class kk_smarter_smarty_debug
{
	private $sections = array();
	private $timings = array();
	private $oPlugin;
	private $userDebugger = null;
	private $errors = array();
	protected $additional = array();
	private static $instance = null;

	/**
	 * initialize plugin
	 *
	 * @param $oPlugin - the plugin object for initialization
	 */
	public function __construct ($oPlugin) {
		$this->oPlugin  = $oPlugin;
		self::$instance = $this;
		return $this;
	}

	/**
	 * singleton
	 *
	 * @param null $oPlugin - the plugin object for initialization
	 * @return kk_smarter_smarty_debug|null
	 */
	public static function getInstance ($oPlugin = null) {
		//check if class was initialized before
		if (self::$instance !== null) {
			return self::$instance;
		}
		//instantiate class
		return new kk_smarter_smarty_debug($oPlugin);
	}

	/**
	 * check if plugin output is activated
	 *
	 * @return bool
	 */
	public function getIsActivated () {
		$options = $this->oPlugin->oPluginEinstellungAssoc_arr;
		if (($options['kk_smarter_smarty_debug_enable'] === 'Y') && //debug has to be enabled
			(($options['kk_smarter_smarty_debug_show_on_query_string'] === 'N') || //and always be active
				($options['kk_smarter_smarty_debug_show_on_query_string'] === 'Y' && isset($_GET[$options['kk_smarter_smarty_debug_query_string']])) || //or get parameter has to be set
				(isset($_COOKIE['SMARTER_SMARTY_DEBUG_ENABLED']) && $_COOKIE['SMARTER_SMARTY_DEBUG_ENABLED'] === '1') //or cookie ist set
			)
		) {
			return true;
		}
		return false;
	}

	/**
	 * custom sort function to add smarter smarty debug hook files at the end
	 *
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public function kkDebugSort ($a, $b) {
		if (strpos($a->cDateiname, 'resource_optimizer') !== false || strpos($a->cDateiname, 'kk_smarter_smarty_debug') !== false) {
			return 1;
		}
		if (strpos($b->cDateiname, 'resource_optimizer') !== false || strpos($b->cDateiname, 'kk_smarter_smarty_debug') !== false) {
			return -1;
		}
		return 0;
	}

	/**
	 * custom error handler
	 *
	 * @param $errNo
	 * @param $errStr
	 * @param $errFile
	 * @param $errLine
	 * @return bool
	 */
	public function kkErrorHandler ($errNo, $errStr, $errFile, $errLine) {
		switch ($errNo) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = 'Notice';
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = 'Warning';
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = 'Fatal Error';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$errors = 'Deprecated';
				break;
			default:
				$errors = 'Unknown Error';
				break;
		}
		$msg                                      = array(
			'NO'   => $errNo,
			'MSG'  => $errStr,
			'FILE' => $errFile,
			'LINE' => $errLine,
		);
		$this->errors[$errFile][$errors][$errStr] = $msg;
		return true;
	}

	/**
	 * set custom error handler
	 *
	 * @return $this
	 */
	public function setErrorHandler () {
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_errors'] === 'Y') {
			set_error_handler(array($this, 'kkErrorHandler'), E_ALL);
		}
		return $this;
	}

	/**
	 * ensures that kk_smarter_smarty_debug hooks are executed last to get all the information needed from other plugins
	 *
	 * @return $this
	 */
	public function makeMeLast () {
		//sort global hook list array so that smarter smarty debug hooks are executed last
		$hookList = array_reverse($GLOBALS['oPluginHookListe_arr']['140']);
		uasort($hookList, array($this, 'kkDebugSort'));
		$GLOBALS['oPluginHookListe_arr']['140'] = $hookList;
		return $this;
	}

	/**
	 * initialize the custom user debugger
	 *
	 * @return $this
	 */
	public function initUserDebugger () {
		$this->userDebugger = new kk_smarter_smarty_debug_util();
		$GLOBALS['dbg']     = $this->userDebugger;
		return $this;
	}

	/**
	 * check if array is associative
	 *
	 * @param array $array
	 * @return bool
	 */
	private function is_assoc (array $array) {
		foreach (array_keys($array) as $k => $v) {
			if ($k !== $v) {
				return true;
			}
		}
		return false;
	}

	/**
	 * gather output from phpinfo()
	 *
	 * @return array
	 */
	private function getPhpInfo () {
		ob_start();
		phpinfo();
		$phpInfo = array('version' => phpversion(), 'phpinfo' => array());
		if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if (strlen($match[1])) {
					$phpInfo[$match[1]] = array();
				} elseif (isset($match[3])) {
					if ($match[2] !== 'Directive') {
						$phpInfo[end(array_keys($phpInfo))][$match[2]] = isset($match[4]) ? array('global' => strip_tags($match[3]), 'local' => strip_tags($match[4])) : strip_tags($match[3]);
					}
				}
			}
		}
		return $phpInfo;
	}

	/**
	 * gather active hooks information
	 *
	 * @return array
	 */
	private function getHooks () {
		$hooks = array();
		//get some additional information about hooking plugins
		if (class_exists('Plugin')) {
			foreach ($GLOBALS['oPluginHookListe_arr'] as $hookId => $hookArray) {
				foreach ($hookArray as $hookObject) {
					if (isset($hookObject->kPlugin) && isset($hookObject->cDateiname)) {
						$oPluginTmp                              = new Plugin($hookObject->kPlugin);
						$tmpPlugin                               = new stdClass();
						$tmpPlugin->ID                           = $hookObject->kPlugin;
						$tmpPlugin->Version                      = $hookObject->nVersion;
						$tmpPlugin->Path                         = PFAD_ROOT . PFAD_PLUGIN . $oPluginTmp->cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $oPluginTmp->nVersion . '/' . PFAD_PLUGIN_FRONTEND;
						$tmpPlugin->Filename                     = $hookObject->cDateiname;
						$tmpPlugin->Author                       = $oPluginTmp->cAutor;
						$tmpPlugin->CreatedDate                  = $oPluginTmp->dErstellt;
						$tmpPlugin->InstallDate                  = $oPluginTmp->dInstalliert;
						$tmpPlugin->Description                  = $oPluginTmp->cBeschreibung;
						$hooks[$hookId][$hookObject->cDateiname] = $tmpPlugin;
					}
				}
			}
		} else {
			$hooks['ERROR'] = 'Konnte Plugin-Klasse nicht instanziieren.';
		}
		return $hooks;
	}

	/**
	 * store json in session
	 *
	 * @param $json
	 * @return $this
	 */
	private function storeOutputAjax ($json) {
		$_SESSION['kk-debug-session'] = $json;
		return $this;
	}

	/**
	 * output json for ajax call
	 */
	private function getOutputAjax () {
		if (isset($_SESSION['kk-debug-session'])) {
			ob_end_clean();
			header('Content-type: application/json; charset=utf-8');
			echo $_SESSION['kk-debug-session'];
			unset($_SESSION['kk-debug-session']);
			die();
		} else {
			echo('Invalid debug session');
			die();
		}
	}

	/**
	 * transform output to an object that is easy to consume by the javascript frontend
	 *
	 * @param array|object $node
	 * @param $key
	 * @param null $parent
	 * @param bool $showPath
	 * @return array
	 */
	private function transform ($node, $key, $parent = null, $showPath = false) {
		$key = utf8_encode($key);
		$res = array(
			'type' => gettype($node),
			'key'  => $key,
		);
		//test for assoc array
		if ($res['type'] === 'array' && $this->is_assoc($node)) {
			$res['type'] = 'assoc_array';
		}
		//we don't care what numeric type it is, we just want to know if it is a number
		if (is_numeric($node)) {
			$res['type'] = 'number';
		}
		//build path
		if ($showPath === true && isset($parent) && isset($parent['path']) && isset($key)) {
			if ($parent['path'] === '') {
				$res['path'] = '$' . $key;
			} elseif ($parent['type'] === 'array') {
				$res['path'] = $parent['path'] . '[' . $key . ']';
			} elseif ($parent['type'] === 'assoc_array') {
				$res['path'] = $parent['path'] . '.' . $key;
			} elseif ($parent['type'] === 'object') {
				$res['path'] = $parent['path'] . '->' . $key;
			} else {
				$res['path'] = '$' . $key;
			}
		} else {
			$res['path'] = '';
		}
		if ($res['type'] === 'object' || $res['type'] === 'array' || $res['type'] === 'assoc_array') {
			//build children array recursively
			$res['children'] = array();
			$res['length']   = 0;
			foreach ($node as $cKey => $value) {
				$res['children'][utf8_encode($cKey)] = $this->transform($value, $cKey, $res, $showPath);
				$res['length']                       = $res['length'] + 1;
			}
		} else {
			//simple data type
			if (is_string($node)) {
				$res['value'] = utf8_encode($node);
			} else {
				$res['value'] = $node;
			}
		}
		return $res;
	}

	/**
	 * add a section
	 *
	 * @param array|object $input
	 * @param string $sectionName
	 * @param bool $showPath
	 */
	private function addSection ($input, $sectionName, $showPath = false) {
		$startTime                                = microtime(true);
		$this->sections[$sectionName]             = $this->transform($input, null, null, $showPath);
		$this->sections[$sectionName]['type']     = 'section';
		$this->sections[$sectionName]['name']     = $sectionName;
		$this->sections[$sectionName]['showPath'] = $showPath;
		$this->timings[$sectionName]              = microtime(true) - $startTime;
	}

	/**
	 * return the sections as JSON object
	 *
	 * @return string
	 */
	private function getSectionsJSON () {
		return json_encode($this->sections);
	}

	/**
	 * gather output
	 */
	public function run () {
		//serve debug output via ajax
		if (isset($_GET['kk-debug-session'])) {
			$this->getOutputAjax();
		}

		//set cookie if option enabled
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_save_cookie'] === 'Y' &&
			$this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_on_query_string'] === 'Y' &&
			isset($_GET[$this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_query_string']])
		) {
			if (!isset($_COOKIE['SMARTER_SMARTY_DEBUG_ENABLED'])) {
				@setcookie('SMARTER_SMARTY_DEBUG_ENABLED', '1');
			}
		}
		global $smarty;
		$languageVars = $this->oPlugin->oPluginSprachvariableAssoc_arr;

		//add user debug output
		if (count($this->userDebugger->getUserDebug()) > 0) {
			$this->addSection($this->userDebugger->getUserDebug(), $languageVars['section_user_debug']);
		}

		//add smarty variables
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_smarty_vars'] === 'Y') {
			//get template vars from smarty
			$assignedVars = $smarty->get_template_vars();
			ksort($assignedVars);
			//create smarty debug output
			$this->addSection($assignedVars, $languageVars['section_smarty_variables'], true);
		}

		//add templates in use
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_loaded_templates'] === 'Y') {
			$templates     = array();
			$usedTemplates = $smarty->_smarty_debug_info;
			//we want the template name as heading - so rebuild the array
			foreach ($usedTemplates as $template) {
				if (isset($template['filename'])) {
					$_filename = $template['filename'];
					unset($template['filename']);
					$templates[$_filename] = $template;
				}
			}
			$this->addSection($templates, $languageVars['section_loaded_templates'] . '(' . count($templates) . ')');
		}

		//add error log
		if (count($this->errors) > 0) {
			//remove duplicate errors
			$this->errors = array_map('unserialize', array_unique(array_map('serialize', $this->errors)));
			$errorCount   = 0;
			foreach ($this->errors as $errorFile) {
				foreach ($errorFile as $errorType) {
					$errorCount += count($errorType);
				}
			}
			$this->addSection($this->errors, $languageVars['section_php_errors'] . '(' . $errorCount . ')');
		}

		//add session output
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_session'] === 'Y') {
			$this->addSection($_SESSION, '$_SESSION');
		}

		//add get parameters
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_get'] === 'Y') {
			$this->addSection($_GET, '$_GET');
		}

		//add post output
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_post'] === 'Y') {
			$this->addSection($_POST, '$_POST');
		}

		//add cookie output
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_cookie'] === 'Y') {
			$this->addSection($_COOKIE, '$_COOKIE');
		}

		//add phpinfo() - thanks to jon @ http://www.php.net/manual/en/function.phpinfo.php
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_php_info'] === 'Y') {
			$this->addSection($this->getPhpInfo(), 'phpinfo()');
		}

		//add registered hooks output
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_hooks'] === 'Y') {
			$this->addSection($this->getHooks(), $languageVars['section_registered_hooks'] . '(' . count($GLOBALS['oPluginHookListe_arr']) . ')');
		}

		//add mem usage output
		if ($this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_show_mem_usage'] === 'Y') {
			$maxMem = number_format(memory_get_peak_usage(true) / 1024 / 1024, 2, ',', '');
			$this->addSection(null, 'Mem: ' . $maxMem . ' MB');
		}

		//add user debug output
		if (count($this->userDebugger->getUserDebug()) > 0) {
			$this->addSection($this->userDebugger->getUserDebug(), 'User Debug');
		}

		//add smarty debug timing for debugging of this plugin itself only
		//uncomment next line for internal time debugging (adds a new section with the transform speed of every section)
		//$this->addSection($this->timings, 'Smarty Debug Timing');

		//add ajax session
		$this->storeOutputAjax($this->getSectionsJSON());
		$enableSmartyDebugParam = $this->oPlugin->oPluginEinstellungAssoc_arr['kk_smarter_smarty_debug_query_string'];
		$getDebugSessionParam   = 'kk-debug-session';

		//add css and js
		pq('head')->append('<link type="text/css" rel="stylesheet" href="' . $this->oPlugin->cFrontendPfadURLSSL . 'css/kk-debug.css" />');
		pq('head')->append('<script data-ignore="true" type="text/javascript">var kk_smarter_smarty_debug = {}; kk_smarter_smarty_debug.kk_lang_var_search_results = "' . $languageVars['search_results'] . '"; kk_smarter_smarty_debug.enableSmartyDebugParam = "' . $enableSmartyDebugParam . '"; kk_smarter_smarty_debug.getDebugSessionParam = "' . $getDebugSessionParam . '";</script>');
		pq('head')->append('<script data-ignore="true" type="text/javascript" src="' . $this->oPlugin->cFrontendPfadURLSSL . 'js/kk-debug.js"></script>');

		//render template
		pq('body')->append($smarty->fetch($this->oPlugin->cFrontendPfad . 'template/kk_smarter_smarty_display.tpl'));

		//unset sections so it can be garbage collected quickly
		$this->sections = null;
	}

}
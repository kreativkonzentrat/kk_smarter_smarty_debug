<?php
/**
 * hook for handling custom variable logging
 *
 * @package     kk_smarter_smarty_debug
 * @createdAt   11.06.13 - 15:01
 * @author      Felix Moche <felix@kreativkonzentrat.de>
 * @copyright   2013 Kreativkonzentrat GbR - Niels Baumbach, Felix Moche, Martin Zilz
 */

require_once($oPlugin->cFrontendPfad . 'inc/kk_smarter_smarty_debug.php');
$smarterSmartyDebug = new kk_smarter_smarty_debug($oPlugin);

if ($smarterSmartyDebug->getIsActivated() === true) {
	$smarterSmartyDebug->makeMeLast()->initUserDebugger()->setErrorHandler();
}
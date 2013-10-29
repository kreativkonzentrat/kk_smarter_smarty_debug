<?php
/**
 * hook for rendering output
 *
 * @package     kk_smarter_smarty_debug
 * @createdAt   10.06.13 - 14:31
 * @author      Felix Moche <felix@kreativkonzentrat.de>
 * @copyright   2013 Kreativkonzentrat GbR - Niels Baumbach, Felix Moche, Martin Zilz
 */

require_once($oPlugin->cFrontendPfad . 'inc/kk_smarter_smarty_debug.php');
$smarterSmartyDebug = kk_smarter_smarty_debug::getInstance($oPlugin);

if ($smarterSmartyDebug->getIsActivated() === true) {
	$smarterSmartyDebug->run();
}
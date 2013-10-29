<?php
/**
 * hook for enabling smarty debugging and setting template
 *
 * @package     kk_smarter_smarty_debug
 * @createdAt   11.06.13 - 15:02
 * @author      Felix Moche <felix@kreativkonzentrat.de>
 * @copyright   2013 Kreativkonzentrat GbR - Niels Baumbach, Felix Moche, Martin Zilz
 */

require_once($oPlugin->cFrontendPfad . 'inc/kk_smarter_smarty_debug.php');
$smarterSmartyDebug = kk_smarter_smarty_debug::getInstance($oPlugin);

if ($smarterSmartyDebug->getIsActivated() === true) {
	global $smarty;
	//enable smarty debugging
	$smarty->debugging = true;
	//set debug template to empty file to avoid the default popup (our own logic is in hook 140)
	$smarty->debug_tpl = $oPlugin->cFrontendPfad . 'template/empty.tpl';
}
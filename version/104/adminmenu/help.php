<?php
/**
 * help page
 *
 * @package     kk_smarter_smarty_debug
 * @createdAt   18.06.13 - 18:01
 * @author      Felix Moche <felix@kreativkonzentrat.de>
 * @copyright   2013 Kreativkonzentrat GbR - Niels Baumbach, Felix Moche, Martin Zilz
 */

global $smarty, $oPlugin;
$smarty->display($oPlugin->cAdminmenuPfad . 'template/help.tpl');
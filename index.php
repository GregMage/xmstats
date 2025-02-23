<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * xmstats module
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author          Mage Gregory (AKA Mage)
 */

//use \Xmf\Request;
use Xmf\Module\Helper;

include_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'xmstats_index.tpl';
include_once XOOPS_ROOT_PATH . '/header.php';

$xoTheme->addStylesheet(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/assets/css/styles.css', null);

$xoopsTpl->assign('index_module', $helper->getModule()->getVar('name'));

$curent_years = date('Y');
$month = date('m');

if ($month < 8){
    $years = $curent_years - 1;
} else {
    $years = $curent_years;
}
echo '<br>$curent_years: ' . $curent_years;
echo '<br>$month: ' . $month;
echo '<br>$years: ' . $years;

if (xoops_isActiveModule('xmstock')){
    $xoopsTpl->assign('xmstock', true);
    $helper_xmstock = Helper::getHelper('xmstock');
    $orderHandler = $helper_xmstock->getHandler('xmstock_order');
    $xoopsTpl->assign('order_all', $orderHandler->getCount());
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('order_dorder', ''));
    $xoopsTpl->assign('order_year', $orderHandler->getCount($criteria));

} else {
    $xoopsTpl->assign('xmstock', false);
}





$keywords = '';
//SEO
// pagetitle
$xoopsTpl->assign('xoops_pagetitle', $xoopsModule->name());
//keywords
$xoTheme->addMeta('meta', 'keywords', $keywords);
include XOOPS_ROOT_PATH . '/footer.php';

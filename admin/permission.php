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

use Xmf\Module\Admin;

require __DIR__ . '/admin_header.php';
include_once XOOPS_ROOT_PATH.'/class/xoopsform/grouppermform.php';
$moduleAdmin = Admin::getInstance();
$moduleAdmin->displayNavigation('permission.php');

$global_perms_array    = [
	'2' => _MA_XMSTATS_PERMISSION_OTHER_2 ,
	'4' => _MA_XMSTATS_PERMISSION_OTHER_4 ,
	'8' => _MA_XMSTATS_PERMISSION_OTHER_8 ,
	'16' => _MA_XMSTATS_PERMISSION_OTHER_16
];

$permissionsForm = new XoopsGroupPermForm(_MA_XMSTATS_PERMISSION, $helper->getModule()->getVar('mid'), 'xmstats_other', _MA_XMSTATS_PERMISSION_DSC, 'admin/permission.php');
foreach ($global_perms_array as $perm_id => $permissionName) {
	$permissionsForm->addItem($perm_id , $permissionName) ;
}

$xoopsTpl->assign('form', $permissionsForm->render());


$xoopsTpl->display("db:xmstats_admin_permission.tpl");

require __DIR__ . '/admin_footer.php';

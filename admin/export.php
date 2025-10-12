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
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author          Mage Gregory (AKA Mage)
 */
use Xmf\Module\Admin;
use Xmf\Request;

require __DIR__ . '/admin_header.php';

$moduleAdmin = Admin::getInstance();
$moduleAdmin->displayNavigation('export.php');

// Get Action type
$op = Request::getCmd('op', 'list');
switch ($op) {
    case 'list':
        // Define Stylesheet
        $xoTheme->addStylesheet(XOOPS_URL . '/modules/system/css/admin.css');
        $xoTheme->addScript('modules/system/js/admin.js');
        // Module admin
        $moduleAdmin->addItemButton(_MA_XMSTATS_EXPORT_FID_ADD, 'export.php?op=add', 'add');
        $xoopsTpl->assign('renderbutton', $moduleAdmin->renderButton());
        // Get start pager
        $start = Request::getInt('start', 0);
        // Criteria
        $criteria = new CriteriaCompo();
        $criteria->setSort('export_id');
        $criteria->setOrder('ASC');
        $criteria->setStart($start);
        $criteria->setLimit($nb_limit);
		$exportHandler->table_link = $exportHandler->db->prefix("xmarticle_field");
        $exportHandler->field_link = "field_id";
        $exportHandler->field_object = "export_fid";
        $export_arr = $exportHandler->getByLink($criteria);
        $export_count = $exportHandler->getCountByLink($criteria);
        $xoopsTpl->assign('export_count', $export_count);
        $type = array(
            'CPS' => _MA_XMSTATS_EXPORT_TYPE_0,
            'STO' => _MA_XMSTATS_EXPORT_TYPE_1,
            'TRA' => _MA_XMSTATS_EXPORT_TYPE_2,
            'PRE' => _MA_XMSTATS_EXPORT_TYPE_3,
            'DEC' => _MA_XMSTATS_EXPORT_TYPE_4,
            'CMD' => _MA_XMSTATS_EXPORT_TYPE_5
        );
        if ($export_count > 0) {
            foreach (array_keys($export_arr) as $i) {
                $export_id               = $export_arr[$i]->getVar('export_id');
                $export['id']            = $export_id;
                $export['type']          = $type[$export_arr[$i]->getVar('export_type')];
                $export['name']          = $export_arr[$i]->getVar('field_name');
                $export['status']        = $export_arr[$i]->getVar('export_status');
                $xoopsTpl->appendByRef('exports', $export);
                unset($export);
            }
            // Display Page Navigation
            if ($export_count > $nb_limit) {
                $nav = new XoopsPageNav($export_count, $nb_limit, $start, 'start');
                $xoopsTpl->assign('nav_menu', $nav->renderNav(4));
            }
        } else {
            $xoopsTpl->assign('error_message', _MA_XMSTATS_ERROR_NOFIELDEXPORT);
        }
        break;

    // Add
    case 'add':
        // Module admin
        $moduleAdmin->addItemButton(_MA_XMSTATS_EXPORT_FID_LIST, 'export.php', 'list');
        $xoopsTpl->assign('renderbutton', $moduleAdmin->renderButton());
        // Form
        $obj  = $exportHandler->create();
        $form = $obj->getForm();
        $xoopsTpl->assign('form', $form->render());
        break;

    // Edit
    case 'edit':
        // Module admin
        $moduleAdmin->addItemButton(_MA_XMSTATS_EXPORT_FID_LIST, 'export.php', 'list');
        $xoopsTpl->assign('renderbutton', $moduleAdmin->renderButton());
        // Form
        $export_id = Request::getInt('export_id', 0);
        if ($export_id == 0) {
            $xoopsTpl->assign('error_message', _MA_XMSTATS_ERROR_NOFIELDEXPORT);
        } else {
            $obj = $exportHandler->get($export_id);
            $form = $obj->getForm();
            $xoopsTpl->assign('form', $form->render());
        }

        break;
    // Save
    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('export.php', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $export_id = Request::getInt('export_id', 0);
        if ($export_id == 0) {
            $obj = $exportHandler->create();
        } else {
            $obj = $exportHandler->get($export_id);
        }
        $error_message = $obj->saveExport($exportHandler, 'export.php');
        if ($error_message != ''){
            $xoopsTpl->assign('error_message', $error_message);
            $form = $obj->getForm();
            $xoopsTpl->assign('form', $form->render());
        }
        break;

    // del
    case 'del':
        $export_id = Request::getInt('export_id', 0);
        if ($export_id == 0) {
            $xoopsTpl->assign('error_message', _MA_XMSTATS_ERROR_NOFIELDEXPORT);
        } else {
            $surdel = Request::getBool('surdel', false);
            $obj  = $exportHandler->get($export_id);
            if ($surdel === true) {
                if (!$GLOBALS['xoopsSecurity']->check()) {
                    redirect_header('export.php', 3, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
                }
                if ($exportHandler->delete($obj)) {
                        redirect_header('export.php', 2, _MA_XMSTATS_REDIRECT_SAVE);
                } else {
                    $xoopsTpl->assign('error_message', $obj->getHtmlErrors());
                }
            } else {
                xoops_confirm(['surdel' => true, 'export_id' => $export_id, 'op' => 'del'], $_SERVER['REQUEST_URI'], sprintf(_MA_XMSTATS_EXPORT_SUREDEL, $obj->getVar('export_id')));
            }
        }
        break;

    // Update status
    case 'export_update_status':
        $export_id = Request::getInt('export_id', 0);
        if ($export_id > 0) {
            $obj = $exportHandler->get($export_id);
            $old = $obj->getVar('export_status');
            $obj->setVar('export_status', !$old);
            if ($exportHandler->insert($obj)) {
                exit;
            }
            $xoopsTpl->assign('error_message', $obj->getHtmlErrors());
        }
        break;

}

$xoopsTpl->display("db:xmstats_admin_export.tpl");

require __DIR__ . '/admin_footer.php';

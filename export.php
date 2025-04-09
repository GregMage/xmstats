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

use \Xmf\Request;
use Xmf\Module\Helper;

include_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'xmstats_export.tpl';
include_once XOOPS_ROOT_PATH . '/header.php';

$xoTheme->addStylesheet(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/assets/css/styles.css', null);

$xoopsTpl->assign('index_module', $helper->getModule()->getVar('name'));
$xoopsTpl->assign('xmstock', xoops_isActiveModule('xmstock'));
$xoopsTpl->assign('xmarticle', xoops_isActiveModule('xmarticle'));

//options
$separator 	= ';';

$op = Request::getString('op', '', 'REQUEST');

switch ($op) {
    case 'kardex':
        if (xoops_isActiveModule('xmarticle')){
            $name_csv 	= 'Export_kardex_' . time() . '.csv';
            $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/' . $name_csv;
            $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/kardex/' . $name_csv;

            //supression des anciens fichiers
            $csv_list = XoopsLists::getFileListByExtension(XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/', array('csv'));
            foreach ($csv_list as $file) {
                unlink(XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/' . $file);
            }

            // Création du fichier d'export
            $sql = "SELECT o.*, l.* , k.* , m.* FROM " . $xoopsDB->prefix('xmarticle_article') . " AS o LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS l ON o.article_cid = l.category_id";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_stock') . " AS k ON o.article_id = k.stock_articleid";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS m ON k.stock_areaid = m.area_id";
            $sql .= " GROUP BY article_id ORDER BY article_id ASC";
            $article_arr = $xoopsDB->query($sql);
            $csv = fopen($path_csv, 'w+');
            //add BOM to fix UTF-8 in Excel
            fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            while($myrow = $xoopsDB->fetchArray($article_arr)){
                if ($myrow['area_name'] != ''){
                    $stock = $myrow['area_name'];
                    if ($myrow['stock_location'] != ''){
                        $stock .= "-" . $myrow['stock_location'];
                    }
                    if ($myrow['stock_amount'] != ''){
                        $amount = $myrow['stock_amount'];
                    }
                } else {
                    $stock = '';
                    $amount = '';
                }
                $name = $myrow['article_name'];
                if (strlen($name) > 70){
                    $name = substr($name, 0, 70) . '...';
                }
                fputcsv($csv, array($myrow['article_reference'], $name, $myrow['category_name'], $stock, $amount, 'Standard'), $separator);
            }
            fclose($csv);
            header("Location: $url_csv");
        }
        break;

    case 'article':
        if (xoops_isActiveModule('xmarticle')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');
            $articleHandler  = $helper_xmarticle->getHandler('xmarticle_article');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // categorie
            $categorie = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_CATEGORIE, 'filter_categorie', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('category_status', 1));
            $criteria->setSort('category_weight ASC, category_name');
            $criteria->setOrder('ASC');
            $categorie_arr = $categorieHandler->getall($criteria);
            $categorie->addOption(0, _MA_XMSTATS_EXPORT_FILTER_ALLF);
            foreach (array_keys($categorie_arr) as $i) {
                $categorie->addOption($categorie_arr[$i]->getVar('category_id'), $categorie_arr[$i]->getVar('category_name'));
            }
            $categorie->setDescription(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_CATEGORIE_DESC);
            $form->addElement($categorie, true);

            // user
            $user = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_USER, 'filter_user', 0, 4, true);
            $user->setDescription(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_USER_DESC);
            $sql = "SELECT DISTINCT u.uid, u.uname
                    FROM " . $xoopsDB->prefix('xmarticle_article') . " a
                    JOIN " . $xoopsDB->prefix('users') . " u ON a.article_userid = u.uid
                    ORDER BY u.uname ASC";
            $result = $xoopsDB->query($sql);
            $user->addOption(0, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            if ($result) {
                while ($row = $xoopsDB->fetchArray($result)) {
                    $user->addOption($row['uid'], $row['uname']);
                }
            }
            $form->addElement($user, true);

            // name
            $name = new XoopsFormText(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME, 'filter_name_desc', 50, 255, '');
            $name->setDescription(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME_DESC);
            $form->addElement($name, false);

            // status
            $status = new XoopsFormRadio(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_STATUS, 'filter_status', 1);
            $status->addOption(0, _MA_XMPROD_EXPORT_STATUS_NA);
            $status->addOption(1, _MA_XMPROD_EXPORT_STATUS_A);
            $status->addOption(2, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            $form->addElement($status, true);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_article'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

    case 'export_article':
        echo '<h3>article</h3>';

        break;
    case 'stock':
        echo '<h3>stock</h3>';

        break;
    case 'transfer':
        echo '<h3>transfer</h3>';

        break;
    default:
        break;
}



$keywords = '';
//SEO
// pagetitle
$xoopsTpl->assign('xoops_pagetitle', _MI_XMSTATS_SUB_EXPORT . ' - ' . $xoopsModule->name());
//keywords
$xoTheme->addMeta('meta', 'keywords', $keywords);
include XOOPS_ROOT_PATH . '/footer.php';

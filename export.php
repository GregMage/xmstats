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

$op = Request::getString('op', '', 'GET');

switch ($op) {
    case 'kardex':
        echo '<h3>kardex</h3>';
        if (xoops_isActiveModule('xmarticle')){
            $xoopsTpl->assign('xmstock', true);
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $name_csv 	= 'Export_' . time() . '.csv';
            $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/' . $name_csv;
            $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/kardex/' . $name_csv;
            $separator 	= ';';

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

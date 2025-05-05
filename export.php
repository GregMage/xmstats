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

//permissions
$moduleHandler = $helper->getModule();
$groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
$gpermHandler = xoops_getHandler('groupperm');
$perm_kardex = $gpermHandler->checkRight('xmstats_other', 2, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_article = $gpermHandler->checkRight('xmstats_other', 4, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_stock = $gpermHandler->checkRight('xmstats_other', 8, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_transfert = $gpermHandler->checkRight('xmstats_other', 16, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$xoopsTpl->assign('perm_kardex', $perm_kardex);
$xoopsTpl->assign('perm_article', $perm_article);
$xoopsTpl->assign('perm_stock', $perm_stock);
$xoopsTpl->assign('perm_transfert', $perm_transfert);
if ($perm_kardex == false && $perm_article == false && $perm_stock == false && $perm_transfert == false){
    redirect_header('index.php', 5, _NOPERM);
}

//options
$separator 	= ';';

$op = Request::getString('op', '', 'REQUEST');

switch ($op) {
    case 'kardex':
        if ($perm_kardex == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle')){
            $name_csv 	= 'Export_kardex_' . time() . '.csv';
            $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/' . $name_csv;
            $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/kardex/' . $name_csv;

            //supression des anciens fichiers
            XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/kardex/', 'csv');

            // Création de la requête
            $sql = "SELECT o.*, l.* , k.* , m.* FROM " . $xoopsDB->prefix('xmarticle_article') . " AS o LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS l ON o.article_cid = l.category_id";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_stock') . " AS k ON o.article_id = k.stock_articleid";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS m ON k.stock_areaid = m.area_id";
            $sql .= " WHERE o.article_status = 1";
            $sql .= " GROUP BY article_id ORDER BY article_id ASC";
            $article_arr = $xoopsDB->query($sql);

            // Création du fichier d'export
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
        if ($perm_article == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');
            //$articleHandler  = $helper_xmarticle->getHandler('xmarticle_article');

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
            $name = new XoopsFormText(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME, 'filter_name', 50, 255, '');
            $name->setDescription(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME_DESC);
            $form->addElement($name, false);

            // status
            $status = new XoopsFormRadio(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_STATUS, 'filter_status', 1);
            $status->addOption(0, _MA_XMSTATS_EXPORT_STATUS_NA);
            $status->addOption(1, _MA_XMSTATS_EXPORT_STATUS_A);
            $status->addOption(2, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            $form->addElement($status, true);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_article'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

    case 'export_article':
        if ($perm_article == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $helper_xmarticle->loadLanguage('main');
            $fieldHandler  = $helper_xmarticle->getHandler('xmarticle_field');
            $categoryHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            // récupération des valeurs du formulaire
            $categories = Request::getArray('filter_categorie', 0, 'POST');
            $user = Request::getArray('filter_user', 0, 'POST');
            $name = Request::getString('filter_name', '', 'POST');
            $status = Request::getInt('filter_status', 0, 'POST');

            // récupération des ids des champs supémentaires utilisés
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('category_status', 1));
            if (!in_array(0, $categories)){
                $criteria->add(new Criteria('category_id', '(' . implode(',', $categories) . ')', 'IN'));
            }
            $category_arr = $categoryHandler->getall($criteria);
            $categoryFields = [];
            foreach (array_keys($category_arr) as $i) {
                $categoryFields = array_merge($categoryFields, !empty($category_arr[$i]->getVar('category_fields')) ? $category_arr[$i]->getVar('category_fields') : []);
            }
            $categoryFields = array_values(array_unique($categoryFields));
            // options d'export
            $name_csv 	= 'Export_article_' . time() . '.csv';
            $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/article/' . $name_csv;
            $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/article/' . $name_csv;
            //supression des anciens fichiers
            XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/article/', 'csv');
            // En-tête fixe du CSV
            $header = [_MA_XMARTICLE_ARTICLE_REFERENCE, _MA_XMARTICLE_INDEX_ARTICLE, _MA_XMARTICLE_ARTICLE_DESC, _MA_XMARTICLE_ARTICLE_CATEGORY,
                       _MA_XMARTICLE_AUTHOR, _MA_XMARTICLE_DATE, _MA_XMARTICLE_READING, _MA_XMARTICLE_STATUS];
            // En-tête champs personalisés du CSV
            if (!empty($categoryFields)) {
                $criteria = new CriteriaCompo();
                $criteria->add(new Criteria('field_status', 1));
                $criteria->setSort('field_weight ASC, field_name');
                $criteria->add(new Criteria('field_id', '(' . implode(',', $categoryFields) . ')', 'IN'));
                $criteria->setOrder('ASC');
                $field_arr = $fieldHandler->getall($criteria);
                $fields_label = [];
                foreach (array_keys($field_arr) as $i) {
                    $header[] = $field_arr[$i]->getVar('field_name');
                    $fields[$i] = $field_arr[$i]->getVar('field_name');
                    if ($field_arr[$i]->getVar('field_type') == 'label'){
                        $fields_label[$i] = $field_arr[$i]->getVar('field_default');
                    }
                }
            } else {
                $fields = [];
            }
            // Récupération des articles avec leurs catégories et champs
            $sql  = "SELECT a.*, c.category_name, c.category_fields, u.uname, f.*, fd.*";
            $sql .= " FROM " . $xoopsDB->prefix('xmarticle_article') . " AS a";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS c ON a.article_cid = c.category_id";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('users') . " AS u ON a.article_userid = u.uid";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_fielddata') . " AS fd ON a.article_id = fd.fielddata_aid";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_field') . " AS f ON fd.fielddata_fid = f.field_id";
            if (!in_array(0, $categories)){
                $sql_where[] = "a.article_cid IN (" . implode(',', $categories) . ")";
            }
            if ($status === 0 || $status === 1) {
                $sql_where[] = "a.article_status = $status";
            }
            if (!empty($name)) {
                $sql_where[] = "a.article_name LIKE '%" . $xoopsDB->escape($name) . "%'";
            }
            if (!in_array(0, $user)){
                $sql_where[] = "a.article_userid IN (" . implode(',', $user) . ")";
            }
            if (!empty($sql_where)) {
                $sql .= " WHERE " . implode(' AND ', $sql_where);
            }
            $sql .= " ORDER BY a.article_id ASC";
            $result = $xoopsDB->query($sql);
            // Préparation des données pour chaque article
            $articles = [];
            while ($row = $xoopsDB->fetchArray($result)) {
                $articleId = $row['article_id'];
                if (!isset($articles[$articleId])) {
                    // Initialisation de l'article
                    $articles[$articleId] = [
                        'article_reference' => $row['article_reference'],
                        'article_name' => $row['article_name'],
                        'article_description' => $row['article_description'],
                        'category_name' => $row['category_name'],
                        'article_uname' => $row['uname'],
                        'article_date' => date('d-m-Y', $row['article_date']),
                        'article_counter' => $row['article_counter'],
                        'article_status' => $row['article_status'],
                        'fields' => array_fill_keys(array_keys($fields), '') // Champs initialisés à vide
                    ];
                }
                // Remplissage des champs si une valeur existe
                if (!empty($row['fielddata_fid']) && isset($fields[$row['fielddata_fid']])) {
                    switch ($row['field_type']) {
                        case 'vs_text':
                        case 's_text':
                        case 'm_text':
                        case 'l_text':
                            $articles[$articleId]['fields'][$row['fielddata_fid']] = $row['fielddata_value1'];
                            break;

                        case 'radio_yn':
                            if ($row['fielddata_value1'] == 0) {
                                $articles[$articleId]['fields'][$row['fielddata_fid']] = _NO;
                            } else {
                                $articles[$articleId]['fields'][$row['fielddata_fid']] = _YES;
                            }
                            break;

                        case 'select':
                        case 'radio':
                            $field_options = unserialize($row['field_options']);
                            $articles[$articleId]['fields'][$row['fielddata_fid']] = $field_options[$row['fielddata_value1']];
                            break;

                        case 'text':
                            $articles[$articleId]['fields'][$row['fielddata_fid']] = $row['fielddata_value2'];
                            break;

                        case 'select_multi':
                        case 'checkbox':
                            $field_options = unserialize($row['field_options']);
                            if (empty($articles[$articleId]['fields'][$row['fielddata_fid']])){
                                $articles[$articleId]['fields'][$row['fielddata_fid']] = $field_options[$row['fielddata_value3']];
                            } else {
                                $articles[$articleId]['fields'][$row['fielddata_fid']] = $articles[$articleId]['fields'][$row['fielddata_fid']] .
                                $helper_xmarticle->getConfig('general_separator', '-') . $field_options[$row['fielddata_value3']];
                            }
                            break;

                        case 'number':
                            $articles[$articleId]['fields'][$row['fielddata_fid']] = $row['fielddata_value4'];
                            break;
                        // provisoir
                        default:
                            echo 'default<br>';
                            $articles[$articleId]['fields'][$row['fielddata_fid']] = $row['fielddata_value1'];
                            break;
                    }
                }
                // remplissage des champs label
                if (!empty($fields_label)) {
                    $category_fields = unserialize($row['category_fields']);
                    foreach (array_keys($fields_label) as $i) {
                        if (in_array($i, $category_fields)) {
                            $articles[$articleId]['fields'][$i] = $fields_label[$i];
                        }
                    }
                }
            }
            // Création du fichier d'export
            $csv = fopen($path_csv, 'w+');
            //add BOM to fix UTF-8 in Excel
            fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
            // En-tête du CSV
            fputcsv($csv, $header, $separator);
            // Écriture des données dans le CSV
            foreach ($articles as $article) {
                $line = [
                    $article['article_reference'],
                    $article['article_name'],
                    $article['article_description'],
                    $article['category_name'],
                    $article['article_uname'],
                    $article['article_date'],
                    $article['article_counter'],
                    $article['article_status'] == 1 ? _MA_XMARTICLE_STATUS_A : _MA_XMARTICLE_STATUS_NA
                ];
                // Ajout des valeurs des champs
                foreach ($fields as $fieldId => $fieldName) {
                    $line[] = $article['fields'][$fieldId];
                }
                fputcsv($csv, $line, $separator);
            }
            fclose($csv);
            header("Location: $url_csv");
        }
        break;
    case 'stock':
        if ($perm_stock == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_STOCK_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // area
            $area = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_AREA, 'filter_area', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('area_status', 1));
            $criteria->setSort('area_weight ASC, area_name');
            $criteria->setOrder('ASC');
            $area_arr = $areaHandler->getall($criteria);
            $area->addOption(0, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            foreach (array_keys($area_arr) as $i) {
                $area->addOption($area_arr[$i]->getVar('area_id'), $area_arr[$i]->getVar('area_name'));
            }
            $area->setDescription(_MA_XMSTATS_EXPORT_FILTER_AREA_DESC);
            $form->addElement($area, true);

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

            // name
            $name = new XoopsFormText(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME, 'filter_name', 50, 255, '');
            $name->setDescription(_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME_DESC);
            $form->addElement($name, false);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_stock'));

            $xoopsTpl->assign('form', $form->render());
        }


        break;
    case 'transfer':
        if ($perm_transfert == false){
            redirect_header('export.php', 5, _NOPERM);
        }
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

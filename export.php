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
$perm_kardex = $gpermHandler->checkRight('xmstats_other', 1, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_article = $gpermHandler->checkRight('xmstats_other', 2, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_stock = $gpermHandler->checkRight('xmstats_other', 3, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_transfer = $gpermHandler->checkRight('xmstats_other', 4, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_loan = $gpermHandler->checkRight('xmstats_other', 5, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_overdraft= $gpermHandler->checkRight('xmstats_other', 6, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$perm_order= $gpermHandler->checkRight('xmstats_other', 7, $groups, $moduleHandler->getVar('mid'), false) ? true : false;
$xoopsTpl->assign('perm_kardex', $perm_kardex);
$xoopsTpl->assign('perm_article', $perm_article);
$xoopsTpl->assign('perm_stock', $perm_stock);
$xoopsTpl->assign('perm_transfer', $perm_transfer);
$xoopsTpl->assign('perm_loan', $perm_loan);
$xoopsTpl->assign('perm_overdraft', $perm_overdraft);
$xoopsTpl->assign('perm_order', $perm_order);
if ($perm_kardex == false && $perm_article == false && $perm_stock == false && $perm_transfer == false){
    redirect_header('index.php', 5, _NOPERM);
}
if (xoops_isActiveModule('xmarticle')){
        xoops_load('utility', 'xmarticle');
		$viewPermissionCat = XmarticleUtility::getPermissionCat('xmarticle_view');
} else {
        $viewPermissionCat = array();
}
if (xoops_isActiveModule('xmstock')){
    xoops_load('utility', 'xmstock');
    $managePermissionArea = XmstockUtility::getPermissionArea('xmstock_manage');
} else {
    $managePermissionArea = array();
}

//options
$separator 	= ';';

$op = Request::getString('op', '', 'REQUEST');
$xoopsTpl->assign('op', $op);

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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('category_id', '(' . implode(',', $viewPermissionCat) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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
                    $header[] = htmlspecialchars_decode($field_arr[$i]->getVar('field_name'));
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
            } else {
                $sql_where[] = "a.article_cid IN (" . implode(',', $viewPermissionCat) . ")";
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
            if ($xoopsDB->getRowsNum($result) > 0) {
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
            } else {
                $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
            }
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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('area_id', '(' . implode(',', $managePermissionArea) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('category_id', '(' . implode(',', $viewPermissionCat) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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

    case 'export_stock':
        if ($perm_stock == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $helper_xmarticle->loadLanguage('main');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $helper_xmstock->loadLanguage('main');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            // récupération des valeurs du formulaire
            $areas = Request::getArray('filter_area', 0, 'POST');
            $categories = Request::getArray('filter_categorie', 0, 'POST');
            $name = Request::getString('filter_name', '', 'POST');

            // options d'export
            $name_csv 	= 'Export_stock_' . time() . '.csv';
            $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/stock/' . $name_csv;
            $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/stock/' . $name_csv;
            //supression des anciens fichiers
            XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/stock/', 'csv');
            // En-tête fixe du CSV
            $header = [_MA_XMSTOCK_STOCK_AREA, _MA_XMARTICLE_ARTICLE_REFERENCE, _MA_XMARTICLE_INDEX_ARTICLE, _MA_XMARTICLE_ARTICLE_DESC, _MA_XMARTICLE_ARTICLE_CATEGORY
                       , _MA_XMSTOCK_TRANSFER_PRICE, _MA_XMSTOCK_STOCK_LOCATION, _MA_XMSTOCK_STOCK_MINI, _MA_XMSTOCK_STOCK_AMOUNT, _MA_XMSTOCK_STOCK_TYPE, _MA_XMSTATS_EXPORT_STOCK_CANORDER];

            // Récupération des stock avec leurs articles
            $sql  = "SELECT a.article_name, a.article_description, a.article_reference, c.category_name, t.area_name, s.*";
            $sql .= " FROM " . $xoopsDB->prefix('xmarticle_article') . " AS a";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS c ON a.article_cid = c.category_id";
            $sql .= " INNER JOIN " . $xoopsDB->prefix('xmstock_stock') . " AS s ON a.article_id = s.stock_articleid";
            $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS t ON s.stock_areaid = t.area_id";
            $sql_where[] = "a.article_status = 1";
            $sql_where[] = "t.area_status = 1";
            if (!in_array(0, $areas)){
                $sql_where[] = "s.stock_areaid IN (" . implode(',', $areas) . ")";
            }
            if (!in_array(0, $categories)){
                $sql_where[] = "a.article_cid IN (" . implode(',', $categories) . ")";
            } else {
                $sql_where[] = "a.article_cid IN (" . implode(',', $viewPermissionCat) . ")";
            }
            if (!empty($name)) {
                $sql_where[] = "a.article_name LIKE '%" . $xoopsDB->escape($name) . "%'";
            }
            if (!empty($sql_where)) {
                $sql .= " WHERE " . implode(' AND ', $sql_where);
            }
            $sql .= " ORDER BY t.area_name ASC, a.article_name ASC";
            $result = $xoopsDB->query($sql);
            if ($xoopsDB->getRowsNum($result) > 0) {
                // Création du fichier d'export
                $csv = fopen($path_csv, 'w+');
                //add BOM to fix UTF-8 in Excel
                fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                // En-tête du CSV
                fputcsv($csv, $header, $separator);
                // Écriture des données dans le CSV
                while ($row = $xoopsDB->fetchArray($result)) {
                    switch ($row['stock_type']) {
                        case 1:
                            $stockTypeText = _MA_XMSTOCK_STOCK_STANDARD;
                            break;
                        case 2:
                            $stockTypeText = _MA_XMSTOCK_STOCK_ML;
                            break;
                        case 3:
                            $stockTypeText = _MA_XMSTATS_EXPORT_STOCK_LOAN;
                            break;
                        case 4:
                            $stockTypeText = _MA_XMSTOCK_STOCK_FREE;
                            break;
                        case 5:
                            $stockTypeText = _MA_XMSTOCK_STOCK_SURFACE;
                            break;
                    }
                    $line = [
                        $row['area_name'],
                        $row['article_reference'],
                        $row['article_name'],
                        $row['article_description'],
                        $row['category_name'],
                        $row['stock_price'],
                        $row['stock_location'],
                        $row['stock_mini'],
                        $row['stock_amount'],
                        $stockTypeText,
                        $row['stock_order'] == 0 ? _YES : _NO
                    ];
                    fputcsv($csv, $line, $separator);
                }
                fclose($csv);
                header("Location: $url_csv");
            } else {
                $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
            }
        }
        break;

    case 'transfer':
        if ($perm_transfer == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $helper_xmstock->loadLanguage('main');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            $helper_xmprod = Helper::getHelper('xmprod');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_TRANSFER_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // area
            $area = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_AREA, 'filter_area', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('area_status', 1));
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('area_id', '(' . implode(',', $managePermissionArea) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('category_id', '(' . implode(',', $viewPermissionCat) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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

            // Date
            $currentYear = date('Y');
            if (xoops_isActiveModule('xmprod')){
                $helper_xmprod = Helper::getHelper('xmprod');
                $month = $helper_xmprod->getConfig('general_month', 0);
                $years = date('m') < $month ? $currentYear - 1 : $currentYear;
            } else {
                $years = $currentYear;
                $month = 1;
            }

            $dateTray = new XoopsFormElementTray(_MA_XMSTATS_EXPORT_FILTER_DATE_RANGE);
            $dateRadio = new XoopsFormRadio("<div class='form-inline'>", 'filter_date_range', 0);
            $dateRadio->addOption(0, _NO);
            $dateRadio->addOption(1, _YES);
            $dateTray->addElement($dateRadio);
            $dateFrom = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_FROM, 'filter_date_from', 15, mktime(0, 0, 0, $month, 1, $years));
            $dateTo = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_TO, 'filter_date_to', 15, time());
            $dateTray->addElement($dateFrom);
            $dateTray->addElement($dateTo);
            $dateTray->addElement(new XoopsFormLabel("</div>"));
            $form->addElement($dateTray);

            // status
            $status = new XoopsFormRadio(_MA_XMSTATS_EXPORT_FILTER_TRANSFER_STATUS, 'filter_status', 2);
            $status->addOption(0, _MA_XMSTOCK_STATUS_WAITING);
            $status->addOption(1, _MA_XMSTOCK_STATUS_EXECUTED);
            $status->addOption(2, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            $form->addElement($status, true);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_transfer'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

        case 'export_transfer':
            if ($perm_transfer == false){
                redirect_header('export.php', 5, _NOPERM);
            }
            if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
                $helper_xmarticle = Helper::getHelper('xmarticle');
                $helper_xmarticle->loadLanguage('main');
                $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

                $helper_xmstock = Helper::getHelper('xmstock');
                $helper_xmstock->loadLanguage('main');
                $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

                // récupération des valeurs du formulaire
                $areas = Request::getArray('filter_area', 0, 'POST');
                $categories = Request::getArray('filter_categorie', 0, 'POST');
                $name = Request::getString('filter_name', '', 'POST');
                $date_range = Request::getInt('filter_date_range', 0, 'POST');
                $date_from = strtotime(Request::getString('filter_date_from', '', 'POST'));
                $date_to = strtotime(Request::getString('filter_date_to', '', 'POST'));
                $status = Request::getInt('filter_status', 0, 'POST');

                // options d'export
                $name_csv 	= 'Export_transfer_' . time() . '.csv';
                $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/transfer/' . $name_csv;
                $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/transfer/' . $name_csv;
                //supression des anciens fichiers
                XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/transfer/', 'csv');
                // En-tête fixe du CSV
                $header = [_MA_XMSTATS_EXPORT_TRANSFER_N0, _MA_XMSTOCK_TRANSFER_REF, _MA_XMSTOCK_TRANSFER_DESC, _MA_XMSTATS_EXPORT_TRANSFER_REFARTICLE, _MA_XMSTOCK_TRANSFER_ARTICLE,
                           _MA_XMSTATS_EXPORT_TRANSFER_CAT, _MA_XMSTOCK_TRANSFER_TYPE, _MA_XMSTOCK_TRANSFER_STAREA, _MA_XMSTOCK_TRANSFER_DESTINATION, _MA_XMSTOCK_AREA_AMOUNT,
                           _MA_XMSTOCK_TRANSFER_DATE, _MA_XMSTOCK_TRANSFER_TIME, _MA_XMSTOCK_TRANSFER_USER, _MA_XMSTOCK_TRANSFER_NEEDSYEAR, _MA_XMSTOCK_STATUS];
                // Récupération des transferts avec les informations
                $sql  = "SELECT t.*, u.uname AS user_name, aru.uname AS ar_user_name, a.article_name, a.article_reference, c.category_name, st.area_name AS st_area_name, ar.area_name AS ar_area_name, o.output_name";
                $sql .= " FROM " . $xoopsDB->prefix('xmstock_transfer') . " AS t";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('users') . " AS u ON t.transfer_userid = u.uid";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('users') . " AS aru ON t.transfer_outputuserid = aru.uid";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS st ON t.transfer_st_areaid = st.area_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS ar ON t.transfer_ar_areaid = ar.area_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_output') . " AS o ON t.transfer_outputid = o.output_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_article') . " AS a ON t.transfer_articleid = a.article_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS c ON a.article_cid = c.category_id";
                if (!in_array(0, $areas)){
                    $areas_ids = implode(',', $areas);
                    $sql_where[] = "(t.transfer_st_areaid IN (" . $areas_ids . ") OR t.transfer_ar_areaid IN (" . $areas_ids . "))";
                } else {
                    $sql_where[] = "(t.transfer_st_areaid IN (" . implode(',', $managePermissionArea) . ") OR t.transfer_ar_areaid IN (" . implode(',', $managePermissionArea) . "))";
                }
                if (!in_array(0, $categories)){
                    $sql_where[] = "a.article_cid IN (" . implode(',', $categories) . ")";
                } else {
                    $sql_where[] = "a.article_cid IN (" . implode(',', $viewPermissionCat) . ")";
                }
                if (!empty($name)) {
                    $sql_where[] = "a.article_name LIKE '%" . $xoopsDB->escape($name) . "%'";
                }
                if ($date_range == 1){
                    $sql_where[] = "(t.transfer_date >= " . $date_from . " AND t.transfer_date <= " . $date_to . ")";
                }
                if ($status === 0 || $status === 1) {
                    $sql_where[] = "t.transfer_status = $status";
                }
                if (!empty($sql_where)) {
                    $sql .= " WHERE " . implode(' AND ', $sql_where);
                }
                $sql .= " ORDER BY t.transfer_date DESC";
                $result = $xoopsDB->query($sql);
                if ($xoopsDB->getRowsNum($result) > 0) {
                    // Création du fichier d'export
                    $csv = fopen($path_csv, 'w+');
                    //add BOM to fix UTF-8 in Excel
                    fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                    // En-tête du CSV
                    fputcsv($csv, $header, $separator);
                    // Écriture des données dans le CSV
                    while ($row = $xoopsDB->fetchArray($result)) {
                        switch ($row['transfer_type']) {
                            case 'E':
                                $transferTypeText = _MA_XMSTOCK_TRANSFER_ENTRYINSTOCK;
                                $transferStAreaText = '';
                                $transferArAreaText = _MA_XMSTOCK_TRANSFER_STOCK . $row['ar_area_name'];
                                break;
                            case 'O':
                                $transferTypeText = _MA_XMSTOCK_TRANSFER_OUTOFSTOCK;
                                $transferStAreaText =$row['st_area_name'];
                                if ($row['transfer_outputuserid'] == 0){
                                    if ($row['transfer_outputid'] != 0){
                                        $transferArAreaText = $row['output_name'];
                                    } else {
                                        $transferArAreaText = '';
                                    }
                                } else {
                                    $transferArAreaText = $row['ar_user_name'];
                                }
                                break;
                            case 'T':
                                $transferTypeText = _MA_XMSTOCK_TRANSFER_TRANSFEROFSTOCK;
                                $transferStAreaText =$row['st_area_name'];
                                $transferArAreaText = _MA_XMSTOCK_TRANSFER_STOCK . $row['ar_area_name'];
                                break;
                        }
                        $line = [
                            $row['transfer_id'],
                            $row['transfer_ref'],
                            $row['transfer_description'],
                            $row['article_reference'],
                            $row['article_name'],
                            $row['category_name'],
                            $transferTypeText,
                            $transferStAreaText,
                            $transferArAreaText,
                            $row['transfer_amount'],
                            formatTimestamp($row['transfer_date'], 's'),
                            substr(formatTimestamp($row['transfer_date'], 'm'), -5),
                            $row['user_name'],
                            $row['transfer_needsyear'],
                            $row['transfer_status'] == 1 ? _MA_XMSTOCK_STATUS_EXECUTED : _MA_XMSTOCK_STATUS_WAITING
                        ];
                        fputcsv($csv, $line, $separator);
                    }
                    fclose($csv);
                    header("Location: $url_csv");
                } else {
                    $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
                }
            }
            break;

    case 'loan':
        if ($perm_loan == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $helper_xmstock->loadLanguage('main');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            $helper_xmprod = Helper::getHelper('xmprod');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_LOAN_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // area
            $area = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_AREA, 'filter_area', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('area_status', 1));
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('area_id', '(' . implode(',', $managePermissionArea) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('category_id', '(' . implode(',', $viewPermissionCat) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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

            // Date
            $currentYear = date('Y');
            if (xoops_isActiveModule('xmprod')){
                $helper_xmprod = Helper::getHelper('xmprod');
                $month = $helper_xmprod->getConfig('general_month', 0);
                $years = date('m') < $month ? $currentYear - 1 : $currentYear;
            } else {
                $years = $currentYear;
                $month = 1;
            }

            $dateTray = new XoopsFormElementTray(_MA_XMSTATS_EXPORT_FILTER_DATE_RANGE);
            $dateRadio = new XoopsFormRadio("<div class='form-inline'>", 'filter_date_range', 0);
            $dateRadio->addOption(0, _NO);
            $dateRadio->addOption(1, _YES);
            $dateTray->addElement($dateRadio);
            $dateFrom = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_FROM, 'filter_date_from', 15, mktime(0, 0, 0, $month, 1, $years));
            $dateTo = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_TO, 'filter_date_to', 15, time());
            $dateTray->addElement($dateFrom);
            $dateTray->addElement($dateTo);
            $dateTray->addElement(new XoopsFormLabel("</div>"));
            $form->addElement($dateTray);

            // status
            $status = new XoopsFormRadio(_MA_XMSTOCK_LOAN_STATUS, 'filter_status', 2);
            $status->addOption(0, _MA_XMSTOCK_LOAN_STATUS_C);
            $status->addOption(1, _MA_XMSTOCK_LOAN_STATUS_L);
            $status->addOption(2, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            $form->addElement($status, true);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_loan'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

        case 'export_loan':
            if ($perm_loan == false){
                redirect_header('export.php', 5, _NOPERM);
            }
            if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
                $helper_xmarticle = Helper::getHelper('xmarticle');
                $helper_xmarticle->loadLanguage('main');
                $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

                $helper_xmstock = Helper::getHelper('xmstock');
                $helper_xmstock->loadLanguage('main');
                $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

                // récupération des valeurs du formulaire
                $areas = Request::getArray('filter_area', 0, 'POST');
                $categories = Request::getArray('filter_categorie', 0, 'POST');
                $name = Request::getString('filter_name', '', 'POST');
                $date_range = Request::getInt('filter_date_range', 0, 'POST');
                $date_from = strtotime(Request::getString('filter_date_from', '', 'POST'));
                $date_to = strtotime(Request::getString('filter_date_to', '', 'POST'));
                $status = Request::getInt('filter_status', 0, 'POST');

                // options d'export
                $name_csv 	= 'Export_loan_' . time() . '.csv';
                $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/loan/' . $name_csv;
                $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/loan/' . $name_csv;
                //supression des anciens fichiers
                XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/loan/', 'csv');
                // En-tête fixe du CSV
                $header = [_MA_XMSTATS_EXPORT_LOAN_N0, _MA_XMSTATS_EXPORT_LOAN_REFARTICLE, _MA_XMSTOCK_LOAN_ARTICLE, _MA_XMSTATS_EXPORT_LOAN_CAT,
                           _MA_XMSTOCK_LOAN_AREA, _MA_XMSTOCK_LOAN_AMOUNT, _MA_XMSTOCK_LOAN_DATE, _MA_XMSTOCK_LOAN_RDATE, _MA_XMSTOCK_LOAN_USERID,
                           _MA_XMSTOCK_LOAN_STATUS];
                // Récupération des prêts avec les informations
                $sql  = "SELECT l.*, u.uname AS user_name, a.article_name, a.article_reference, c.category_name, s.area_name";
                $sql .= " FROM " . $xoopsDB->prefix('xmstock_loan') . " AS l";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('users') . " AS u ON l.loan_userid = u.uid";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS s ON l.loan_areaid = s.area_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_article') . " AS a ON l.loan_articleid = a.article_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS c ON a.article_cid = c.category_id";
                if (!in_array(0, $areas)){
                    $areas_ids = implode(',', $areas);
                    $sql_where[] = "l.loan_areaid IN (" . $areas_ids . ")";
                } else {
                    $sql_where[] = "l.loan_areaid IN (" . implode(',', $managePermissionArea) . ")";
                }
                if (!in_array(0, $categories)){
                    $sql_where[] = "a.article_cid IN (" . implode(',', $categories) . ")";
                } else {
                    $sql_where[] = "a.article_cid IN (" . implode(',', $viewPermissionCat) . ")";
                }
                if (!empty($name)) {
                    $sql_where[] = "a.article_name LIKE '%" . $xoopsDB->escape($name) . "%'";
                }
                if ($date_range == 1){
                    $sql_where[] = "(l.loan_date >= " . $date_from . " AND l.loan_date <= " . $date_to . ")";
                }
                if ($status === 0 || $status === 1) {
                    $sql_where[] = "l.loan_status = $status";
                }
                if (!empty($sql_where)) {
                    $sql .= " WHERE " . implode(' AND ', $sql_where);
                }
                $sql .= " ORDER BY l.loan_date ASC";
                $result = $xoopsDB->query($sql);
                if ($xoopsDB->getRowsNum($result) > 0) {
                    // Création du fichier d'export
                    $csv = fopen($path_csv, 'w+');
                    //add BOM to fix UTF-8 in Excel
                    fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                    // En-tête du CSV
                    fputcsv($csv, $header, $separator);
                    // Écriture des données dans le CSV
                    while ($row = $xoopsDB->fetchArray($result)) {
                        $line = [
                            $row['loan_id'],
                            $row['article_reference'],
                            $row['article_name'],
                            $row['category_name'],
                            $row['area_name'],
                            $row['loan_amount'],
                            formatTimestamp($row['loan_date'], 's'),
                            ($row['loan_status'] == 0) ? formatTimestamp($row['loan_rdate'], 's') : '',
                            $row['user_name'],
                            $row['loan_status'] == 1 ? _MA_XMSTOCK_LOAN_STATUS_L : _MA_XMSTOCK_LOAN_STATUS_C
                        ];
                        fputcsv($csv, $line, $separator);
                    }
                    fclose($csv);
                    header("Location: $url_csv");
                } else {
                    $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
                }
            }
            break;

    case 'overdraft':
        if ($perm_overdraft == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $helper_xmstock->loadLanguage('main');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_OVERDRAFT_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // area
            $area = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_AREA, 'filter_area', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('area_status', 1));
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('area_id', '(' . implode(',', $managePermissionArea) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('category_id', '(' . implode(',', $viewPermissionCat) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
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

            $form->addElement(new XoopsFormHidden('op', 'export_overdraft'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

        case 'export_overdraft':
            if ($perm_overdraft == false){
                redirect_header('export.php', 5, _NOPERM);
            }
            if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
                $helper_xmarticle = Helper::getHelper('xmarticle');
                $helper_xmarticle->loadLanguage('main');
                $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

                $helper_xmstock = Helper::getHelper('xmstock');
                $helper_xmstock->loadLanguage('main');
                $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

                // récupération des valeurs du formulaire
                $areas = Request::getArray('filter_area', 0, 'POST');
                $categories = Request::getArray('filter_categorie', 0, 'POST');
                $name = Request::getString('filter_name', '', 'POST');

                // options d'export
                $name_csv 	= 'Export_overdraft_' . time() . '.csv';
                $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/overdraft/' . $name_csv;
                $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/overdraft/' . $name_csv;
                //supression des anciens fichiers
                XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/overdraft/', 'csv');
                // En-tête fixe du CSV
                $header = [_MA_XMSTATS_EXPORT_REFARTICLE, _MA_XMSTATS_EXPORT_ARTICLE, _MA_XMSTATS_EXPORT_CATARTICLE,
                           _MA_XMSTATS_EXPORT_FILTER_AREA, _MA_XMSTATS_EXPORT_OVERDRAFT_STOCKMINI, _MA_XMSTOCK_LOAN_AMOUNT];
                // Récupération des informations
                $sql  = "SELECT s.*, a.article_name, a.article_reference, c.category_name,  ar.area_name";
                $sql .= " FROM " . $xoopsDB->prefix('xmstock_stock') . " AS s";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS ar ON s.stock_areaid = ar.area_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_article') . " AS a ON s.stock_articleid = a.article_id";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmarticle_category') . " AS c ON a.article_cid = c.category_id";
                $sql_where[] = "s.stock_amount <= s.stock_mini";
                $sql_where[] = "s.stock_mini != 0";
                if (!in_array(0, $areas)){
                    $areas_ids = implode(',', $areas);
                    $sql_where[] = "s.stock_areaid IN (" . $areas_ids . ")";
                } else {
                    $sql_where[] = "s.stock_areaid IN (" . implode(',', $managePermissionArea) . ")";
                }
                if (!in_array(0, $categories)){
                    $sql_where[] = "a.article_cid IN (" . implode(',', $categories) . ")";
                } else {
                    $sql_where[] = "a.article_cid IN (" . implode(',', $viewPermissionCat) . ")";
                }
                if (!empty($name)) {
                    $sql_where[] = "a.article_name LIKE '%" . $xoopsDB->escape($name) . "%'";
                }
                if (!empty($sql_where)) {
                    $sql .= " WHERE " . implode(' AND ', $sql_where);
                }
                $sql .= " ORDER BY s.stock_amount ASC";
                $result = $xoopsDB->query($sql);
                if ($xoopsDB->getRowsNum($result) > 0) {
                    // Création du fichier d'export
                    $csv = fopen($path_csv, 'w+');
                    //add BOM to fix UTF-8 in Excel
                    fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                    // En-tête du CSV
                    fputcsv($csv, $header, $separator);
                    // Écriture des données dans le CSV
                    while ($row = $xoopsDB->fetchArray($result)) {
                        $line = [
                            $row['article_reference'],
                            $row['article_name'],
                            $row['category_name'],
                            $row['area_name'],
                            $row['stock_mini'],
                            $row['stock_amount']
                        ];
                        fputcsv($csv, $line, $separator);
                    }
                    fclose($csv);
                    header("Location: $url_csv");
                } else {
                    $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
                }
            }
            break;

    case 'order':
        if ($perm_order == false){
            redirect_header('export.php', 5, _NOPERM);
        }
        if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
            $helper_xmarticle = Helper::getHelper('xmarticle');
            $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

            $helper_xmstock = Helper::getHelper('xmstock');
            $helper_xmstock->loadLanguage('main');
            $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

            $helper_xmprod = Helper::getHelper('xmprod');

            include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            $form = new XoopsThemeForm(_MA_XMSTATS_EXPORT_FILTER_ORDER_TITLE, 'form', $_SERVER['REQUEST_URI'], 'post', true);

            // area
            $area = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FILTER_AREA, 'filter_area', 0, 4, true);
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('area_status', 1));
            if (!empty($viewPermissionCat)) {
                $criteria->add(new Criteria('area_id', '(' . implode(',', $managePermissionArea) . ')', 'IN'));
            } else {
                redirect_header('index.php', 3, _NOPERM);
            }
            $criteria->setSort('area_weight ASC, area_name');
            $criteria->setOrder('ASC');
            $area_arr = $areaHandler->getall($criteria);
            $area->addOption(0, _MA_XMSTATS_EXPORT_FILTER_ALLM);
            foreach (array_keys($area_arr) as $i) {
                $area->addOption($area_arr[$i]->getVar('area_id'), $area_arr[$i]->getVar('area_name'));
            }
            $area->setDescription(_MA_XMSTATS_EXPORT_FILTER_AREA_DESC);
            $form->addElement($area, true);

            // Date
            $currentYear = date('Y');
            if (xoops_isActiveModule('xmprod')){
                $helper_xmprod = Helper::getHelper('xmprod');
                $month = $helper_xmprod->getConfig('general_month', 0);
                $years = date('m') < $month ? $currentYear - 1 : $currentYear;
            } else {
                $years = $currentYear;
                $month = 1;
            }

            $dateTray = new XoopsFormElementTray(_MA_XMSTATS_EXPORT_ORDER_FILTER_DATE_RANGE);
            $dateRadio = new XoopsFormRadio("<div class='form-inline'>", 'filter_date_range', 0);
            $dateRadio->addOption(0, _NO);
            $dateRadio->addOption(1, _YES);
            $dateTray->addElement($dateRadio);
            $dateFrom = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_FROM, 'filter_date_from', 15, mktime(0, 0, 0, $month, 1, $years));
            $dateTo = new XoopsFormTextDateSelect(_MA_XMSTATS_EXPORT_FILTER_DATE_TO, 'filter_date_to', 15, time());
            $dateTray->addElement($dateFrom);
            $dateTray->addElement($dateTo);
            $dateTray->addElement(new XoopsFormLabel("</div>"));
            $form->addElement($dateTray);

            // status
            $status = new XoopsFormCheckBox(_MA_XMSTATS_EXPORT_FILTER_ORDER_STATUS, 'filter_status', [1, 2, 3, 4]);
            $status->addOption(0, _MA_XMSTOCK_ORDER_STATUS_TITLE_0);
            $status->addOption(1, _MA_XMSTOCK_ORDER_STATUS_TITLE_1);
            $status->addOption(2, _MA_XMSTOCK_ORDER_STATUS_TITLE_2);
            $status->addOption(3, _MA_XMSTOCK_ORDER_STATUS_TITLE_3);
            $status->addOption(4, _MA_XMSTOCK_ORDER_STATUS_TITLE_4);
            $form->addElement($status, false);

            // export
            $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

            $form->addElement(new XoopsFormHidden('op', 'export_order'));

            $xoopsTpl->assign('form', $form->render());
        }
        break;

        case 'export_order':
            if ($perm_order == false){
                redirect_header('export.php', 5, _NOPERM);
            }
            if (xoops_isActiveModule('xmarticle') && xoops_isActiveModule('xmstock')){
                $helper_xmarticle = Helper::getHelper('xmarticle');
                $helper_xmarticle->loadLanguage('main');
                $categorieHandler  = $helper_xmarticle->getHandler('xmarticle_category');

                $helper_xmstock = Helper::getHelper('xmstock');
                $helper_xmstock->loadLanguage('main');
                $areaHandler  = $helper_xmstock->getHandler('xmstock_area');

                // récupération des valeurs du formulaire
                $areas = Request::getArray('filter_area', 0, 'POST');
                $date_range = Request::getInt('filter_date_range', 0, 'POST');
                $date_from = strtotime(Request::getString('filter_date_from', '', 'POST'));
                $date_to = strtotime(Request::getString('filter_date_to', '', 'POST'));
                $status = Request::getArray('filter_status', array(), 'POST');

                // options d'export
                $name_csv 	= 'Export_order_' . time() . '.csv';
                $path_csv 	= XOOPS_UPLOAD_PATH . '/xmstats/exports/order/' . $name_csv;
                $url_csv 	= XOOPS_UPLOAD_URL . '/xmstats/exports/order/' . $name_csv;
                //supression des anciens fichiers
                XmstatsUtility::delOldFiles(XOOPS_UPLOAD_PATH . '/xmstats/exports/order/', 'csv');
                // En-tête fixe du CSV
                $header = [_MA_XMSTATS_EXPORT_ORDER_N0, _MA_XMSTOCK_ORDER_DESCRIPTION, _MA_XMSTOCK_MANAGEMENT_CUSTOMER, _MA_XMSTOCK_MANAGEMENT_AREA, _MA_XMSTOCK_ORDER_DATEORDER,
                           _MA_XMSTOCK_ORDER_DATEDESIRED, _MA_XMSTOCK_ORDER_DATEVALIDATION, _MA_XMSTOCK_ORDER_DATEDELIVERYWITHDRAWAL,_MA_XMSTOCK_ORDER_DATEREADY,
                           _MA_XMSTOCK_ORDER_DATEDELIVERYWITHDRAWAL_R, _MA_XMSTOCK_ORDER_DATECANCELLATION, _MA_XMSTOCK_ORDER_DELIVERY, _MA_XMSTOCK_STATUS];
                // Récupération des prêts avec les informations
                $sql  = "SELECT o.*, u.uname AS user_name, s.area_name";
                $sql .= " FROM " . $xoopsDB->prefix('xmstock_order') . " AS o";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('users') . " AS u ON o.order_userid = u.uid";
                $sql .= " LEFT JOIN " . $xoopsDB->prefix('xmstock_area') . " AS s ON o.order_areaid = s.area_id";
                if (!in_array(0, $areas)){
                    $areas_ids = implode(',', $areas);
                    $sql_where[] = "o.order_areaid IN (" . $areas_ids . ")";
                } else {
                    $sql_where[] = "o.order_areaid IN (" . implode(',', $managePermissionArea) . ")";
                }
                if ($date_range == 1){
                    $sql_where[] = "(o.order_dorder >= " . $date_from . " AND o.order_dorder <= " . $date_to . ")";
                }
                if (!empty($status)) {
                    $sql_where[] = "o.order_status IN (" . implode(',', $status) . ")";
                }
                if (!empty($sql_where)) {
                    $sql .= " WHERE " . implode(' AND ', $sql_where);
                }
                $sql .= " ORDER BY o.order_dorder ASC";
                $result = $xoopsDB->query($sql);
                if ($xoopsDB->getRowsNum($result) > 0 && !empty($status)) {
                    // Création du fichier d'export
                    $csv = fopen($path_csv, 'w+');
                    //add BOM to fix UTF-8 in Excel
                    fputs($csv, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                    // En-tête du CSV
                    fputcsv($csv, $header, $separator);
                    // Écriture des données dans le CSV
                    while ($row = $xoopsDB->fetchArray($result)) {
                        switch ($row['order_status']) {
                            case 1:
                                $order_status = _MA_XMSTOCK_ORDER_STATUS_1;
                                break;
                            case 2:
                                $order_status = _MA_XMSTOCK_ORDER_STATUS_2;
                                break;
                            case 3:
                                $order_status = _MA_XMSTOCK_ORDER_STATUS_3;
                                break;
                            case 4:
                                $order_status = _MA_XMSTOCK_ORDER_STATUS_4;
                                break;
                            case 0:
                                $order_status = _MA_XMSTOCK_ORDER_STATUS_0;
                                break;
                        }
                        $line = [
                            $row['order_id'],
                            $row['order_description'],
                            $row['user_name'],
                            $row['area_name'],
                            formatTimestamp($row['order_dorder'], 'm'),
                            formatTimestamp($row['order_ddesired'], 's'),
                            $row['order_dvalidation'] == 0 ? '' : formatTimestamp($row['order_dvalidation'], 'm'),
                            $row['order_ddelivery'] == 0 ? '' : formatTimestamp($row['order_ddelivery'], 's'),
                            $row['order_dready'] == 0 ? '' : formatTimestamp($row['order_dready'], 'm'),
                            $row['order_ddelivery_r'] == 0 ? '' : formatTimestamp($row['order_ddelivery_r'], 'm'),
                            $row['order_dcancellation'] == 0 ? '' : formatTimestamp($row['order_dcancellation'], 'm'),
                            $row['order_delivery'] == 0 ? _MA_XMSTOCK_ORDER_DELIVERY_WITHDRAWAL : _MA_XMSTOCK_ORDER_DELIVERY_DELIVERY,
                            $order_status
                        ];
                        fputcsv($csv, $line, $separator);
                    }
                    fclose($csv);
                    header("Location: $url_csv");
                } else {
                    $xoopsTpl->assign('error', _MA_XMSTATS_EXPORT_NO_DATA);
                }
            }
            break;
}

$keywords = '';
//SEO
// pagetitle
$xoopsTpl->assign('xoops_pagetitle', _MI_XMSTATS_SUB_EXPORT . ' - ' . $xoopsModule->name());
//keywords
$xoTheme->addMeta('meta', 'keywords', $keywords);
include XOOPS_ROOT_PATH . '/footer.php';

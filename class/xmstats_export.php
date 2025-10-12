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
use Xmf\Request;
use Xmf\Module\Helper;

defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

/**
 * Class xmstats_export
 */
class xmstats_export extends XoopsObject
{
    // constructor
    /**
     * xmstats_export constructor.
     */
    public function __construct()
    {
        $this->initVar('export_id', XOBJ_DTYPE_INT, null, false, 11);
        $this->initVar('export_type', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('export_fid', XOBJ_DTYPE_INT, null);
        $this->initVar('export_status', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_name', XOBJ_DTYPE_TXTBOX, null);
    }

    /**
     * @return mixed
     */
    public function get_new_enreg()
    {
        global $xoopsDB;
        $new_enreg = $xoopsDB->getInsertId();
        return $new_enreg;
    }

    /**
     * @return mixed
     */
    public function saveExport($exportHandler, $action = false)
    {
        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        include __DIR__ . '/../include/common.php';

        $error_message = '';
        $this->setVar('export_type', Request::getString('export_type', ''));
        $this->setVar('export_fid', Request::getInt('export_fid', 1));
        $this->setVar('export_status', Request::getInt('export_status', 1));
        if ($error_message == '') {
            if ($exportHandler->insert($this)) {
                redirect_header($action, 2, _MA_XMSTATS_REDIRECT_SAVE);
            } else {
                $error_message =  $this->getHtmlErrors();
            }
        }
        return $error_message;
    }

    /**
     * @param bool $action
     * @return XoopsThemeForm
     */
    public function getForm($action = false)
    {
        if (!xoops_isActiveModule('xmarticle')) {
            redirect_header('index.php', 5, _MA_XMSTATS_ERROR_XMARTICLE_NOTACTIVE);
        }

        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
        include __DIR__ . '/../include/common.php';

        //form title
        $title = $this->isNew() ? sprintf(_MA_XMSTATS_ADD) : sprintf(_MA_XMSTATS_EDIT);

        $form = new XoopsThemeForm($title, 'form', $action, 'post', true);
        $form->setExtra('enctype="multipart/form-data"');

        if (!$this->isNew()) {
            $form->addElement(new XoopsFormHidden('export_id', $this->getVar('export_id')));
            $status = $this->getVar('export_status');
        } else {
            $status = 1;
        }
        // type
        $type = new XoopsFormSelect(_MA_XMSTATS_EXPORT_TYPE, 'export_type', $this->getVar('export_type'));
        $options    = array(
            'CPS'  => _MA_XMSTATS_EXPORT_TYPE_0,
            'STO'  => _MA_XMSTATS_EXPORT_TYPE_1,
            'TRA'  => _MA_XMSTATS_EXPORT_TYPE_2,
            'PRE'  => _MA_XMSTATS_EXPORT_TYPE_3,
            'DEC'  => _MA_XMSTATS_EXPORT_TYPE_4,
            'CMD'  => _MA_XMSTATS_EXPORT_TYPE_5,

        );
        $type->addOptionArray($options);
        $form->addElement($type, true);

        // field
        $field = new XoopsFormSelect(_MA_XMSTATS_EXPORT_FIELD, 'export_fid', $this->getVar('export_fid'));
        $helper_xmarticle = Helper::getHelper('xmarticle');
        $fieldHandler  = $helper_xmarticle->getHandler('xmarticle_field');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('field_status', 1));
        $criteria->setSort('field_weight ASC, field_name');
        $criteria->setOrder('ASC');
        $field_arr = $fieldHandler->getall($criteria);
        foreach (array_keys($field_arr) as $i) {
            $field->addOption($field_arr[$i]->getVar('field_id'), $field_arr[$i]->getVar('field_name') . ' (' . $field_arr[$i]->getVar('field_weight') . ')');
        }
        $field->setDescription(_MA_XMSTATS_EXPORT_FIELD_DSC);
        $form->addElement($field, true);


		// status
        $form_status = new XoopsFormRadio(_MA_XMSTATS_STATUS, 'export_status', $status);
        $options = array(1 => _MA_XMSTATS_STATUS_A, 0 =>_MA_XMSTATS_STATUS_NA,);
        $form_status->addOptionArray($options);
        $form->addElement($form_status);

        $form->addElement(new XoopsFormHidden('op', 'save'));
        // submit
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        return $form;
    }
}

/**
 * Classxmstatsxmstats_exportHandler
 */
class xmstatsxmstats_exportHandler extends XoopsPersistableObjectHandler
{
    /**
     * xmstatsxmstats_exportHandler constructor.
     * @param null|XoopsDatabase $db
     */
    public function __construct($db)
    {
        parent::__construct($db, 'xmstats_export', 'xmstats_export', 'export_id', 'export_stock');
    }
}

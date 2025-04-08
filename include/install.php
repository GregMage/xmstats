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

function xoops_module_install_xmstats()
{
    $namemodule = 'xmstats';
    // Liste des dossiers à créer
    $directories = [
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule,
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule . '/exports',
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule . '/exports/article',
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule . '/exports/kardex',
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule . '/exports/stock',
        XOOPS_ROOT_PATH . '/uploads/' . $namemodule . '/exports/transfer',
    ];
    // Création des dossiers
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true); // true pour créer les sous-dossiers si nécessaire
        }
        chmod($dir, 0777);
    }
    // Copie de index.php
    $indexFile = XOOPS_ROOT_PATH . '/modules/' . $namemodule . '/include/index.php';
    foreach ($directories as $destination) {
        copy($indexFile, $destination . '/index.php');
    }
    return true;
}

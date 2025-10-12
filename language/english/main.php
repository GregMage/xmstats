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

// Button
define('_MA_XMSTATS_', '');


// Admin


// Error message


// Info message


// Shared
define('_MA_XMSTATS_ADD', 'Ajouter');
define('_MA_XMSTATS_EDIT', 'Editer');
define('_MA_XMSTATS_DEL', 'Supprimer');
define('_MA_XMSTATS_STATUS', 'Statut');
define('_MA_XMSTATS_STATUS_A', 'Actif');
define('_MA_XMSTATS_STATUS_NA', 'Désactivé');
define('_MA_XMSTATS_REDIRECT_SAVE', 'Enregistrement effectué avec succès');

// index.php
define('_MA_XMSTATS_INDEX_ALL', 'Sur toute la période');
define('_MA_XMSTATS_INDEX_ORDER', 'Commandes');
define('_MA_XMSTATS_INDEX_YEAR', 'Sur l\'année en cours (scolaire)');
define('_MA_XMSTATS_INDEX_XMSTOCK', 'Gestion de stock');

// export.php
define('_MA_XMSTATS_EXPORT_ARTICLE', 'Composants');
define('_MA_XMSTATS_EXPORT_CATARTICLE', 'Catégorie du composant');
define('_MA_XMSTATS_EXPORT_FILTER_AREA', 'Lieu de stockage');
define('_MA_XMSTATS_EXPORT_FILTER_AREA_DESC', 'Il est possible de choisir plusieurs lieux de stockages en utilisant la touche CTRL.');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_USER', 'Créateur du composant');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_USER_DESC', 'Il est possible de choisir plusieurs créateurs de composants en utilisant la touche CTRL.');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_TITLE', 'Filtre pour l\'export des composants');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_CATEGORIE', 'Catégorie de composant');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_CATEGORIE_DESC', 'Il est possible de choisir plusieurs catégories de composants en utilisant la touche CTRL.');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_STATUS', 'Statut du composant');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME', 'Nom du composant');
define('_MA_XMSTATS_EXPORT_FILTER_ARTICLE_NAME_DESC', 'Le nom du composant peut être partiel. Laissez vide pour tout afficher.');
define('_MA_XMSTATS_EXPORT_FILTER_ALLF', 'Toutes');
define('_MA_XMSTATS_EXPORT_FILTER_ALLM', 'Tous');
define('_MA_XMSTATS_EXPORT_FILTER_DATE_RANGE', 'Filtrer sur une plage de dates');
define('_MA_XMSTATS_EXPORT_FILTER_DATE_FROM', 'De');
define('_MA_XMSTATS_EXPORT_FILTER_DATE_TO', 'à');
define('_MA_XMSTATS_EXPORT_FILTER_LOAN_TITLE', 'Filtre pour l\'export des prêts');
define('_MA_XMSTATS_EXPORT_FILTER_ORDER_TITLE', 'Filtre pour l\'export des commandes');
define('_MA_XMSTATS_EXPORT_FILTER_ORDER_STATUS', 'Statut de la commande');
define('_MA_XMSTATS_EXPORT_FILTER_OVERDRAFT_TITLE', 'Filtre pour l\'export des découverts');
define('_MA_XMSTATS_EXPORT_FILTER_STOCK_TITLE', 'Filtre pour l\'export des stocks');
define('_MA_XMSTATS_EXPORT_FILTER_TRANSFER_TITLE', 'Filtre pour l\'export des transferts');
define('_MA_XMSTATS_EXPORT_KARDEX', 'Kardex');
define('_MA_XMSTATS_EXPORT_LOAN', 'Prêts');
define('_MA_XMSTATS_EXPORT_LOAN_N0', 'N° du prêt');
define('_MA_XMSTATS_EXPORT_LOAN_REFARTICLE', 'Référence du composant prêté');
define('_MA_XMSTATS_EXPORT_LOAN_CAT', 'catégorie du composant prêté');
define('_MA_XMSTATS_EXPORT_NO_DATA', 'Aucune données à exporter');
define('_MA_XMSTATS_EXPORT_OVERDRAFT', 'Découverts');
define('_MA_XMSTATS_EXPORT_OVERDRAFT_STOCKMINI', 'Stock minimum');
define('_MA_XMSTATS_EXPORT_ORDER', 'Commandes');
define('_MA_XMSTATS_EXPORT_ORDER_FILTER_DATE_RANGE', 'Filtrer sur une plage de dates de commande');
define('_MA_XMSTATS_EXPORT_ORDER_N0', 'N° de commande');
define('_MA_XMSTATS_EXPORT_REFARTICLE', 'Référence du composant');
define('_MA_XMSTATS_EXPORT_STATUS_A', 'Activé');
define('_MA_XMSTATS_EXPORT_STATUS_NA', 'Désactivé');
define('_MA_XMSTATS_EXPORT_STOCK', 'Stocks');
define('_MA_XMSTATS_EXPORT_STOCK_CANORDER', 'Commande autorisée');
define('_MA_XMSTATS_EXPORT_STOCK_LOAN', 'Emprunt');
define('_MA_XMSTATS_EXPORT_TITLE', 'Système d\'exportation des données');
define('_MA_XMSTATS_EXPORT_TRANSFER', 'Transferts');
define('_MA_XMSTATS_EXPORT_TRANSFER_CAT', 'Catégorie du composant à transférer');
define('_MA_XMSTATS_EXPORT_TRANSFER_N0', 'N° de transfert');
define('_MA_XMSTATS_EXPORT_TRANSFER_REFARTICLE', 'Référence du composant à transférer');
define('_MA_XMSTATS_EXPORT_FILTER_TRANSFER_STATUS', 'Statut du transfert');

// Export admin
define('_MA_XMSTATS_EXPORT_FID_ADD', 'Ajouter un champ à exporter');
define('_MA_XMSTATS_EXPORT_FID_LIST', 'Liste des champs à exporter');
define('_MA_XMSTATS_EXPORT_TYPE', 'Type d\'export');
define('_MA_XMSTATS_EXPORT_TYPE_0', 'Composants');
define('_MA_XMSTATS_EXPORT_TYPE_1', 'Stocks');
define('_MA_XMSTATS_EXPORT_TYPE_2', 'Transferts');
define('_MA_XMSTATS_EXPORT_TYPE_3', 'Prêts');
define('_MA_XMSTATS_EXPORT_TYPE_4', 'Découverts');
define('_MA_XMSTATS_EXPORT_TYPE_5', 'Commandes');
define('_MA_XMSTATS_EXPORT_ID', 'ID');
define('_MA_XMSTATS_EXPORT_FIELD', 'Champs à ajouter à l\'export');
define('_MA_XMSTATS_EXPORT_FIELD_DSC', 'Choisissez le champ à ajouter à l\'export. Seuls les champs du module xmarticle sont disponibles.');
define('_MA_XMSTATS_EXPORT_SUREDEL', 'Voulez-vous vraiment supprimer ce champs? %s');


define('_MA_XMSTATS_ERROR_NOFIELDEXPORT', 'Aucun champ à exporter n\'a été créé.');
define('_MA_XMSTATS_ERROR_XMARTICLE_NOTACTIVE', 'Le module xmarticle doit être activé pour utiliser ce module.');

// Permission
define('_MA_XMSTATS_PERMISSION', 'Autorisations');
define('_MA_XMSTATS_PERMISSION_DSC', 'Choisissez les groupes qui peuvent effectuer les actions suivantes');
define('_MA_XMSTATS_PERMISSION_OTHER_KARDEX', 'Exportation Kardex');
define('_MA_XMSTATS_PERMISSION_OTHER_ARTICLE', 'Exportation composants');
define('_MA_XMSTATS_PERMISSION_OTHER_STOCK', 'Exportation stocks');
define('_MA_XMSTATS_PERMISSION_OTHER_TRANSFER', 'Exportation transferts');
define('_MA_XMSTATS_PERMISSION_OTHER_LOAN', 'Exportation prêts');
define('_MA_XMSTATS_PERMISSION_OTHER_OVERDRAFT', 'Exportation découverts');
define('_MA_XMSTATS_PERMISSION_OTHER_ORDER', 'Exportation commandes');
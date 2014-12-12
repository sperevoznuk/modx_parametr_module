<?php

if (!class_exists("DocumentParser")) {
    // Для безопасности:
    require_once('../../../manager/includes/protect.inc.php');
// Подгрузка конфигурации:
    include_once('../../../manager/includes/config.inc.php');
// Подключаем класс парсера документов:
    include_once('../../../manager/includes/document.parser.class.inc.php');
// Создаем новый экземпляр класса DocumentParser:
    $modx = new DocumentParser;
}
//if (IN_MANAGER_MODE != 'true' && !$modx->hasPermission('exec_module')) die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.');

require_once __DIR__ . '/config.php'; // include config 
//$modx->db->query('DROP TABLE IF EXISTS ' . $table . ';');
$sqlTable = '
CREATE TABLE IF NOT EXISTS ' . $table . '(
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `richtext` int(50) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;';

$modx->db->query($sqlTable);

// remove plugin 
$res = $modx->db->makeArray($modx->db->query("SELECT * FROM " . $modx->getFullTableName('site_plugins') . " WHERE `name`='$plugin_name'"));
if (count($res) > 0) {
    $id = $res[0]['id'];
    $modx->db->query("DELETE FROM" . $modx->getFullTableName('site_plugins') . " WHERE `id`=$id");
    $modx->db->query("DELETE FROM" . $modx->getFullTableName('site_plugin_events') . " WHERE `pluginid`=$id");
}
// insert plugin 
$sqlAddPlugin .= "INSERT INTO " . $modx->getFullTableName('site_plugins') . " VALUES ";
$sqlAddPlugin .= "(12,'$plugin_name','',0,0,0,'//AUTHORS: Sergey Perevoznuk\r\ninclude_once (MODX_BASE_PATH.\"assets/modules/parametr/parametr_plugin.php\");\r\n	',0,'',0,' ')";
$modx->db->query($sqlAddPlugin);

// insert plugins actions
$id = mysql_insert_id();
$sqlAction = 'INSERT INTO ' . $modx->getFullTableName('site_plugin_events') . " VALUES ($id,3,1)";
$modx->db->query($sqlAction);


// insert module 
$modx->db->query("DELETE FROM " . $modx->getFullTableName('site_modules') . " WHERE `name`='$midule_name'");
$sqlModule = "INSERT INTO " . $modx->getFullTableName('site_modules') . " VALUES ('','$midule_name','',0,0,0,0,0,'',0,'',0,0,'f2eb857272209024c895bf97d3d356fd',0,'','//AUTHORS: Sergey Perevoznuk\r\ninclude_once(\'../assets/modules/parametr/parametr_modul.php\');')";
$modx->db->query($sqlModule);


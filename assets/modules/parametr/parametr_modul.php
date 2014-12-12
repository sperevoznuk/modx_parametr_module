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
// получаем языки
if (file_exists(MODX_BASE_PATH . 'assets/modules/yams/class/yams.class.inc.php')) {
    require_once(MODX_BASE_PATH . 'assets/modules/yams/class/yams.class.inc.php');
    require_once(MODX_BASE_PATH . 'assets/modules/yams/yams.module.funcs.inc.php');
}

if (class_exists(YAMS)) {
    $yams = YAMS::GetInstance();
    $langkey = array_merge(
            $yams->GetActiveLangIds()
            , $yams->GetInactiveLangIds()
    );
} else {
    $langkey[] = "ru";
}

//ообработкa ajax
if (isset($_POST['action']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    switch ($_POST['action']) {
        case"addnew":
            // добавление новой строки
            $res['operation'] = $modx->db->query("INSERT INTO  $table (`value`) VALUES ('')");
            $res['list_params'] = list_params($table);
            break;
        case "save":
//            foreach ($_POST as $key => $val) {
//                $text[$key] = ($val); // собираем все языки в массив
//            }
//            $richtext = $_POST['richtext'] == "true" ? 1 : 0;
//            $res['operation'] = $modx->db->query("UPDATE $table  SET `value`='" . mysql_real_escape_string(serialize($text)) . "', `richtext`=$richtext WHERE `id`='" . $_POST['id'] . "'");
//            $res['button'] = $_POST['id'] . ' &#8744';
            $res = save($table);
            break;
        case "getdata": // читаем данные параметра
            $ind = 1;
            $param = $modx->db->makeArray($modx->db->query("SELECT * FROM $table WHERE `id`=" . $_POST['id']));
            $res['richtext'] = $param[0]['richtext'];
            $param = unserialize($param[0]['value']);
            $res['descr'] = '<input type="hidden" name="id" value="' . $_POST['id'] . '">';


            foreach ($langkey as $key => $val) {
                if ($val) {
                    $res['descr'] .= '<div id="tab_c' . $ind . '"><textarea area="tab_' . $ind . '" name="' . $val . '">' . $param[$val] . '</textarea></div>' . "\n";
                    $ind++;
                }
            }
            $res['operation'] = 1;

            break;
        case"rem": // удаление
            $res['operation'] = $modx->db->query("DELETE FROM $table WHERE `id`=" . $_POST['id']);
            $res['list_params'] = list_params($table);


            break;
        case "getdatapage":
            //формируем форму быстрого редактирования
            $param = $modx->db->makeArray($modx->db->query("SELECT * FROM $table WHERE `id`=" . $_POST['id']));
            $res['richtext'] = $param[0]['richtext'];
            $param = unserialize($param[0]['value']);
            $res['form'] = '
                                        <div class="modal-header">
                                            <button type="button" id="close"  data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h3>Изменить параметр № ' . $_POST['id'] . '</h3>
                                        </div>';
            $res['form'] .= '<div class="modal-body"><ul class="nav nav-tabs">';
            $class = 'active';
            foreach ($langkey as $key => $val) {
                $res['form'] .="\n ".'<li class="' . $class . '"><a href="#lang_' . $val . '" data-toggle="tab">' . $val . '</a></li>';
                $class = '';
            }

            $res['form'] .= '</ul><div class="tab-content">';
            $res['form'] .= '<input type="hidden" name="id" value="' . $_POST['id'] . '">';

            $class = 'active';
            foreach ($langkey as $key => $val) {

                $res['form'] .= '
                                        <div class="tab-pane ' . $class . '" id="lang_' . $val . '">
                                            <textarea id="' . $val . '" name="' . $val . '" class="edit-area">' . $param[$val] . '</textarea>
                                        </div>' . "\n";
                $class = '';
            }

            $res['form'] .= '  </div>
                                </div>

                                <div class="modal-footer">
                                <input type="hidden" name="richtext" value="' . $res['richtext'] . '"/>
                                 <!-- <div>
                                      <input type="checkbox" id="richtext" name="richtext" >
                                      <label for="richtext">Визуальный редактор</label>
                                  </div> -->
                                    <button type="button" data-dismiss="modal" class="btn">Close</button>
                                    <button type="button" class="btn btn-primary">Ok</button>
                                </div></div>';


            $res['operation'] = 1;
            //die(json_encode($res));
            break;

        case "savepage":

            $res = save($table);
            break;
    }
    print json_encode($res);
    die();
}


$res = file_get_contents(MODX_BASE_PATH . 'assets/modules/parametr/index.tpl.html');
$placeholder['list-params'] = list_params($table);

$ind = 1;
foreach ($langkey as $key => $val) {
    $placeholder['tablang'] .= '<input id="tab_' . $ind . '" type="radio" name="tab"/>' . "\n";
    $placeholder['tablang'] .= ' <label for="tab_' . $ind . '" id="tab_l' . $ind . '">' . $val . '</label>' . "\n";
    $ind++;
}

$placeholder['base_href'] = $modx->config['site_url'];
$placeholder['title'] = "доп параметры";

foreach ($placeholder as $key => $value) {
    $search[] = '[+' . $key . '+]';
    $replace[] = $value;
}
$res = str_replace($search, $replace, $res);


print $res;

function list_params($table) {
    // возвращает список параметров
    global $modx;
    $params = $modx->db->makeArray($modx->db->query("SELECT * FROM $table ORDER BY `id`"));
    $list = "";
    foreach ($params as $key => $val) {
        $empty = $val['value'] == '' ? ' <span>(empty)</span> ' : '';
        $list .= '<li value="' . $val['id'] . '" class="">' . $val['id'] . $empty . '</li>' . "\n";
    }
    return $list;
}

function save($table) {
    global $modx;
    foreach ($_POST as $key => $val) {

        $text[$key] = str_replace('sanitize_seed_dth4ct6v2zkk0sokks4kowg4s', '', $val); // собираем все языки в массив
    }
    //  print_r(json_decode($_REQUEST['']);
//   die();
    $richtext = $_POST['richtext'] == "true" || $_POST['richtext'] == 1 ? 1 : 0;
	//print serialize($text); die();
	$modx->db->connect();
    $res['operation'] = $modx->db->query("UPDATE $table  SET `value`='" . mysql_real_escape_string(serialize($text)) . "', `richtext`=$richtext WHERE `id`='" . $_POST['id'] . "'");
    $res['button'] = $_POST['id'] . ' &#8744';
    return $res;
}

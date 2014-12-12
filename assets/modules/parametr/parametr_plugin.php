<?php
require_once 'config.php'; // include config 

$e = & $modx->Event;
if (class_exists(YAMS)) {
    $yams = YAMS::GetInstance();
    $langkey = array_merge(
        $yams->GetActiveLangIds()
        , $yams->GetInactiveLangIds()
    );
} else {
    $langkey[] = "ru";
}



switch ($e->name) {
    case "OnWebPagePrerender":
        $o = $modx->documentOutput;
        //$table = "modx_site_params";
        $params = $modx->db->makeArray($modx->db->query("SELECT * FROM $table"));
        if (class_exists(YAMS)) {
            $yams = YAMS::GetInstance();
            $lang = $yams->DetermineCurrentLangId(); // получаем код текущего языка

        } else {
            // берём первый язык из массива
            $lang = $langkey[0];
        }

        if ($_SESSION['usertype'] == "manager") {
            // если  залогинился manager подключаем ajax плагин
            $head = '
                        <link href="assets/modules/parametr/css/prettify.css" rel="stylesheet"/>
                        <link href="assets/modules/parametr/css/bootstrap-modal-bs3patch.css" rel="stylesheet"/>
                        <link href="assets/modules/parametr/css/bootstrap-modal.css" rel="stylesheet"/>
                        <script src="assets/modules/parametr/lib/bootstrap.js"></script>
                        <script src="assets/modules/parametr/lib/bootstrap-modalmanager.js"></script>
                        <script src="assets/modules/parametr/lib/bootstrap-modal.js"></script>
                        <script type="text/javascript" src="assets/plugins/tinymce/tiny_mce/tiny_mce.js?231077"></script>
                        <script type="text/javascript" src="assets/plugins/tinymce/js/xconfig.js"></script>
                        <script type="text/javascript" src="assets/plugins/tinymce/js/tinymce.linklist.php"></script>
                        <script src="assets/modules/parametr/popup.js"></script>';

            $form = '<form id="ajax-modal" class="modal fade" tabindex="-1" style="display: none;"></form>';

            $o = str_replace("</head>", " $head  \n </head>", $o);
            $o = str_replace("</body>", "$form \n</body>", $o);

            foreach ($params as $key => $val) {
                // формируем теги
                $st = '<t data-id="' . $val['id'] . '" data-toggle="modal">';
                $et = '</t>';
                $item = unserialize($val['value']);
                $items["[-" . $val['id'] . "-]"] = $st . $item[$lang] . $et;
            }

        } else {
            foreach ($params as $key => $val) {
                $item = unserialize($val['value']);
                $items["[-" . $val['id'] . "-]"] = $st . $item[$lang] . $et;
            }

        }
        $search = array_keys($items);
        $replace = array_values($items);
        $o = str_replace($search, $replace, $o);
        $o = parse ($o);



        $modx->documentOutput = $o;
        break;
}

function parse($o){
    global $modx;
    if ($o) {
        $o = $modx->mergeSettingsContent($o);
        $o = $modx->mergeChunkContent($o);
        $o = $modx->evalSnippets($o);
        if (strpos($o, "[~") !== false) {
            $o = $modx->rewriteUrls($o);
        }
    }
    return $o;
}
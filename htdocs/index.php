<?php

xhprof_enable(XHPROF_FLAGS_MEMORY);

require_once('../seezoo/bark.php');
$SZ = Seezoo::init(SZ_MODE_MVC);

$xhprof_data = xhprof_disable();
  
$XHPROF_ROOT = '/Users/sugimoto/works'; //xhprofをインストールしたディレクトリ
$XHPROF_SOURCE_NAME = 'SZFW';
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
$xhprof_runs = new XHProfRuns_Default();
$run_id = $xhprof_runs->save_run($xhprof_data, $XHPROF_SOURCE_NAME);

echo "<a href=\"http://localhost/xhprof_html/index.php?run=$run_id&source=$XHPROF_SOURCE_NAME\" target=\"_blank\">xhprof Result</a>\n";

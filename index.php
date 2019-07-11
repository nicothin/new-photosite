<?php
$start = microtime(true);

$folder = 'ph/';
$dir = opendir($folder);
$list = array();
while($file = readdir($dir)){
  if ($file != '.' && $file != '..' && $file != 'imagecache' && $file[strlen($file)-1] != '~' ){
    $ctime = filectime( $folder . $file ) . ',' . $file;
    $list[$ctime] = $file;
  }
}
closedir($dir);
ksort($list);
echo json_encode($list);

echo '<hr>Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';
?>

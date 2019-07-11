<?php
$start = microtime(true);

$folder = 'ph/';
$dir = opendir($folder);
$list = array();
while($file = readdir($dir)){
  if ($file != '.' && $file != '..' && $file != 'imagecache' && $file[strlen($file)-1] != '~' ){
    $ctime = filectime( $folder . $file ) . ',' . $file;
    $list[$ctime]['file'] = $folder . $file;
    $size = getimagesize($folder . $file, $info);
    $list[$ctime]['w'] = $size[0];
    $list[$ctime]['h'] = $size[1];
    if(isset($info['APP13'])){
      $iptc = iptcparse($info['APP13']);
      $list[$ctime]['name'] = $iptc["2#005"][0];
      $list[$ctime]['descr'] = $iptc["2#120"][0];
      $list[$ctime]['date'] = preg_replace('/^(\d{4})(\d{2})(\d{2})/', '$3.$2.$1', $iptc["2#055"][0]);
    }
    else {
      $list[$ctime]['name'] = "";
      $list[$ctime]['descr'] = "";
      $list[$ctime]['date'] = "";
    }
  }
}
closedir($dir);
krsort($list);

foreach ($list as $key => $photo) {
  echo $key . '<br>';
  echo $photo['file'] . '<br>';
  echo $photo['w'] . '<br>';
  echo $photo['h'] . '<br>';
  echo $photo['name'] . '<br>';
  echo $photo['descr'] . '<br>';
  echo $photo['date'] . '<br>';
}

/*
foreach ($list as $photoKey => $photo) {
  $photoPath = $folder . $photo;
  echo "{$photoKey} => {$photoPath} <br>";

  $size = getimagesize($photoPath, $info);
  echo $size[0] . '<br>';
  echo $size[1] . '<br>';
  if(isset($info['APP13'])){
    $iptc = iptcparse($info['APP13']);
    var_dump($iptc);
  }
}
*/

echo '<hr>Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';
?>

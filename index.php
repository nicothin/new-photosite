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
$list = array_values($list);

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

if (!$list)
{
  header('HTTP/1.1 400 Bad Request');
  echo 'Ошибка Будды: нихера нет. Ни одного, ять, фото.';
  exit();
}

echo '
<!DOCTYPE html>
<html class="page  no-js" lang="ru">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#000">
<meta name="msapplication-navbutton-color" content="#000">
<meta name="apple-mobile-web-app-status-bar-style" content="#000">
<script>function cth(c){document.documentElement.classList.add(c)}\'ontouchstart\' in window?cth(\'touch\'):cth(\'no-touch\');document.documentElement.className = document.documentElement.className.replace(\'no-js\', \'js\');</script>
<title>Николай Громов. Фотоблог</title>
<meta name="description" content="Фотографии: стритфото, жанр, портреты, абстракция, минимализм, ню.">
<link rel="stylesheet" href="css/style.css">
</head>

<body>
<noscript>У вас отключен JavaScript. Это пугает.</noscript>
<div class="page__inner">
<main class="photogrid" itemscope itemtype="http://schema.org/ImageGallery">

<header class="photogrid__main welcome">
  <a class="welcome__header" href="#about">
    <h1 class="welcome__name"><span class="welcome__name-inner">Николай Громов</span></h1>
    <p class="welcome__descr"><span class="welcome__descr-inner">Фотохудожник</span></p>
  </a>
  <figure class="welcome__photo-wrap" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
  <a href="'.$list[0]['file'].'" itemprop="contentUrl" data-size="'.$list[0]['w'].'x'.$list[0]['h'].'">
    <img class="welcome__last-photo" src="'.$list[0]['file'].'" itemprop="thumbnail" alt="'.$list[0]['name'].'">
  </a>
  <figcaption class="welcome__photo-descr" itemprop="caption description">'.$list[0]['name'];
if($list[0]['descr']) echo '<br>'.$list[0]['descr'];
  echo '</figcaption>
  </figure>
</header>';


foreach ($list as $key => $photo) {
  if($key == 0) continue;
  if($key < 16) {
    $sources = 'src="/image.php?image=/'.$photo['file'].'&amp;width=20&amp;height=20&amp;cropratio=1:1" data-src="/image.php?image=/'.$photo['file'].'&amp;width=380&amp;height=380&amp;cropratio=1:1"';
  }
  else {
    $sources = 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="/image.php?image=/'.$photo['file'].'&amp;width=380&amp;height=380&amp;cropratio=1:1"';
  }
  echo '<figure class="photogrid__item photo" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject"><a class="photo__img-wrap" href="'.$photo['file'].'" itemprop="contentUrl" data-size="'.$photo['w'].'x'.$photo['h'].'"><img class="photo__img" '.$sources.' itemprop="thumbnail" alt="'.$photo['name'].'"></a>';
  echo '<figcaption class="photo__descr" itemprop="caption description">'.$photo['name'];
  if($photo['descr']) echo '<br>'.$photo['descr'];
  echo '</figcaption></figure>';
  // echo $photo['date'] . '<br>';
}


echo '</main>
    <aside class="about" id="about">
      <h2 class="about__title">Кто такой Николай Громов</h2>
      <div class="about__text">
        <p>Я веб-разработчик. С 2000 по 2016 год я активно занимался фотографией (даже была своя студия). Сейчас снимаю редко и исключительно по собственному желанию. Этот свой сайт с фото переделал в середине 2019, удалив почти все кадры.</p>
        <p><a href="https://vk.com/n.gromov">ВКонтакте</a>, <a href="tel:+79112603759">+7 911 260-37-59</a>, <a href="mailto:nicothin@gmail.com">nicothin@gmail.com</a>.</p>
      </div>
    </aside>
    <section class="photohelp" id="photohelp">
      <h2>Памятка начинающему фотографу</h2>
      <p>Это PDF — две стороны листа A4, разбитые на 4 блока. Распечатать на одном листе, сложить — получится памятка, легко влезающая в карман или небольшую фотосумку.</p>
      <p><a href="http://ngromov.ru/uploads/pamyatka_fotografu_1.3_ngromov_ru_.pdf">Скачать</a></p>
    </section>
  </div>
  <script src="js/bundle.js"></script>

  <!-- Время выполнения скрипта: '.round(microtime(true) - $start, 4).' -->

</body>

</html>';
?>

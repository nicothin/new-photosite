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

$siteName = 'Николай Громов. Фотоблог';
$siteDescr = 'Фотографии: стритфото, жанр, портреты, абстракция, минимализм, ню.';

echo '
<!DOCTYPE html>
<html class="page  no-js" lang="ru">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#000">
<meta name="msapplication-navbutton-color" content="#000">
<meta name="apple-mobile-web-app-status-bar-style" content="#000">
<meta property="og:locale" content="ru_RU">
<meta property="og:type" content="website">
<meta property="og:title" content="'.$siteName.'">
<meta property="og:description" content="'.$siteDescr.'">
<meta property="og:image" content="//ngromov.ru/'.$list[0]['file'].'">
<meta property="og:site_name" content="'.$siteName.'">
<title>'.$siteName.'</title>
<meta name="description" content="'.$siteDescr.'">
<link rel="stylesheet" href="css/style.css">
<script>function cth(c){document.documentElement.classList.add(c)}\'ontouchstart\' in window?cth(\'touch\'):cth(\'no-touch\');document.documentElement.className = document.documentElement.className.replace(\'no-js\', \'js\');</script>
</head>

<body>
<noscript>У вас отключен JavaScript. Это пугает.</noscript>
<div class="page__inner">
<main>

<header class="welcome">
  <a class="welcome__header" href="#about">
    <h1 class="welcome__name"><span class="welcome__name-inner">Николай Громов</span></h1>
    <p class="welcome__descr"><span class="welcome__descr-inner">Фотохудожник</span></p>
  </a></header>
<div class="photogrid" id="photogallery" itemscope itemtype="http://schema.org/ImageGallery">';

echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

foreach ($list as $key => $photo) {
  if($key == 0) {
    $figureClass = 'photogrid__main photo';
    $sources = 'src="'.$photo['file'].'"';
  }
  else {
    $figureClass = 'photogrid__item photo';
    $sources = 'src="" data-src="/image.php?image=/'.$photo['file'].'&amp;width=380&amp;height=380&amp;cropratio=1:1"';
  }
  echo '<figure class="'.$figureClass.'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject"><a class="photo__img-wrap js-photo" href="'.$photo['file'].'" itemprop="contentUrl" data-size="'.$photo['w'].'x'.$photo['h'].'"><img class="photo__img" '.$sources.' itemprop="thumbnail" alt="'.$photo['name'].'"></a>';
  echo '<figcaption class="photo__descr" itemprop="caption description">'.$photo['name'];
  if($photo['descr']) echo '<br>'.$photo['descr'];
  echo '</figcaption></figure>';
  // echo $photo['date'] . '<br>';
}

echo '
</div>
</main>
  <aside class="about" id="about" tabindex="-1">
    <h2>Кто такой Николай Громов</h2>
    <div class="about__text">
      <p>Я веб-разработчик. С 2000 по 2016 год я активно занимался фотографией (даже была своя студия). Сейчас снимаю редко и исключительно по собственному желанию. Этот свой сайт с фото переделал в середине 2019, удалив почти все кадры.</p>
      <p><a href="https://vk.com/n.gromov">ВКонтакте</a>, <a href="tel:+79112603759">+7 911 260-37-59</a>, <a href="mailto:nicothin@gmail.com">nicothin@gmail.com</a>.</p>
    </div>
  </aside>
  <aside class="photohelp" id="photohelp" tabindex="-1">
    <h2>Памятка начинающему фотографу</h2>
    <p>Это PDF — две стороны листа A4, разбитые на 4 блока. Распечатать на одном листе, сложить — получится памятка, легко влезающая в карман или небольшую фотосумку.</p>
    <p><a href="http://ngromov.ru/uploads/pamyatka_fotografu_1.3_ngromov_ru_.pdf">Скачать</a></p>
  </aside>
  <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
      <div class="pswp__container">
        <div class="pswp__item"></div>
        <div class="pswp__item"></div>
        <div class="pswp__item"></div>
      </div>
      <div class="pswp__ui pswp__ui--hidden">
        <div class="pswp__top-bar">
          <div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="Close (Esc)"></button><button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button><button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
          <div class="pswp__preloader">
            <div class="pswp__preloader__icn">
              <div class="pswp__preloader__cut">
                <div class="pswp__preloader__donut"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
          <div class="pswp__share-tooltip"></div>
        </div><button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button><button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
        <div class="pswp__caption">
          <div class="pswp__caption__center"></div>
        </div>
      </div>
    </div>
  </div>
  <script src="js/bundle.js"></script>

  <!-- Время выполнения скрипта: '.round(microtime(true) - $start, 4).' -->

</body>

</html>';
?>

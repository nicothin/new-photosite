<?php
$start = microtime(true);

// сформируем список фото с сортировкой от самых новых
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
    if (isset($info['APP13'])) {
      $iptc = iptcparse($info['APP13']);
      $list[$ctime]['name'] = $iptc["2#005"][0];
      $list[$ctime]['descr'] = $iptc["2#120"][0];
      // дату не показываем нигде
      // $list[$ctime]['date'] = preg_replace('/^(\d{4})(\d{2})(\d{2})/', '$3.$2.$1', $iptc["2#055"][0]);
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

// если не сформировался список фото, все тлен
if (!$list) {
  header('HTTP/1.1 400 Bad Request');
  echo 'Ошибка Будды: ничего нет. Ни одного фото.';
  exit();
}

// данные сайта, которые не хочется дублировать
$site = array(
  'title'     => 'Николай Громов',
  'subtitle'  => 'Фотохудожник',
  'descr'     => 'Cтритфото, жанр, портреты, абстракция, минимализм, ню.',
  'about'     => array(
    'title'   => 'Кто такой Николай Громов',
    'html'    => '<p>Я веб-разработчик. С 2000 по 2016 год активно занимался фотографией (была своя студия). Сейчас снимаю редко, исключительно по собственному желанию. Этот свой фотосайт я переделал в середине 2019, удалив почти все кадры.</p>
                  <p><a href="https://vk.com/n.gromov">ВКонтакте</a>, <a href="tel:+79112603759">+7 911 260-37-59</a>, <a href="mailto:nicothin@gmail.com">nicothin@gmail.com</a>.</p>',
  ),
  'photohelp'     => array(
    'title'   => 'Памятка начинающему фотографу',
    'html'    => '<p>Это PDF — две стороны листа A4, разбитые на 4 блока. Распечатать на одном листе и сложить — получится памятка, легко влезающая в карман или небольшую фотосумку.</p>
                  <p><a href="http://ngromov.ru/uploads/pamyatka_fotografu_1.3_ngromov_ru_.pdf">Скачать</a></p>',
  ),
);

// метаданные по умолчанию
$meta = array();
$meta['type'] = 'website';
$meta['title'] = $site['title'].'. '.$site['subtitle'];
$meta['descr'] = $site['descr'];
$meta['image'] = $list[0]['file'];

// пришли какие-то данные для фотосвайпера, вероятно нужно изменить метаданные
if (isset($_GET) and isset($_GET['gid']) and isset($_GET['pid']) ) {
  // это один из текстовых блоков (gid = 1980 указан в JS для текстовых слайдов)
  if ($_GET['gid'] == 1980) {
    // пока только 2 таких текстовых блока
    if ($_GET['pid'] == 'about' or $_GET['pid'] == 'photohelp') {
      $meta['type'] = 'article';
      $meta['title'] = $site[$_GET['pid']]['title'];
      $meta['descr'] = iconv_substr(strip_tags($site[$_GET['pid']]['html']), 0, 100, 'UTF-8') . '...';
    }
  }
  // это фотография
  else {
    foreach ($list as $key => $photo) {
      if ($_GET['pid'] == $photo['file']) {
        $meta['type'] = 'article';
        $meta['title'] = $photo['name'] ? $photo['name'] : 'Фото без названия';
        $meta['descr'] = $photo['descr'] ? $photo['descr'] : '';
        $meta['image'] = $photo['file'];
        break;
      }
    }
  }
}

// выводим страницу
echo '<!DOCTYPE html>
<html class="page  no-js" lang="ru">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#000">
<meta name="msapplication-navbutton-color" content="#000">
<meta name="apple-mobile-web-app-status-bar-style" content="#000">
<meta property="og:locale" content="ru_RU">
<meta property="og:type" content="'.$meta['type'].'">
<meta property="og:title" content="'.$meta['title'].'">
<meta property="og:description" content="'.$meta['descr'].'">
<meta property="og:image" content="'.$meta['image'].'">
<meta property="og:site_name" content="'.$site["title"].'. '.$site["subtitle"].'">
<title>'.$meta['title'].'</title>
<meta name="description" content="'.$meta['descr'].'">
<link rel="stylesheet" href="css/style.css">
<script>function cth(c){document.documentElement.classList.add(c)}\'ontouchstart\' in window?cth(\'touch\'):cth(\'no-touch\');document.documentElement.className = document.documentElement.className.replace(\'no-js\', \'js\');</script>
<script>
// исходные метаданные
var mainMeta = {
  type: \'website\',
  title: \''.$site['title'].'. '.$site['subtitle'].'.\',
  descr: \''.$site['descr'].'\',
  image: \''.$list[0]['file'].'\',
}</script>
</head>

<body>
<noscript>У вас отключен JavaScript. Это пугает.</noscript>
<div class="page__inner">
<main>

<header class="welcome">
  <a class="welcome__header" href="#about">
    <h1 class="welcome__name"><span class="welcome__name-inner">'.$site["title"].'</span></h1>
    <p class="welcome__descr"><span class="welcome__descr-inner">'.$site["subtitle"].'</span></p>
  </a></header>
<div class="photogrid" id="photogallery" itemscope itemtype="http://schema.org/ImageGallery">';

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
  <aside class="about" id="about" tabindex="-1" aria-hidden="true">
    <h2>'.$site['about']['title'].'</h2>
    '.$site['about']['html'].'
  </aside>
  <aside class="photohelp" id="photohelp" tabindex="-1" aria-hidden="true">
    <h2>'.$site['photohelp']['title'].'</h2>
    '.$site['photohelp']['html'].'
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



// если захочется смотреть весь EXIF
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
?>

/* global Element window document */

// const ready = require('./utils/documentReady.js');

// ready(function(){
//   console.log('DOM героически построен!');
// });



// полифил closest
(function() {
  if (!Element.prototype.closest) {
    // реализуем
    Element.prototype.closest = function(css) {
      var node = this;
      while (node) {
        if (node.matches(css)) return node;
        else node = node.parentElement;
      }
      return null;
    };
  }
})();

// еще один свой closest, сученька!
var closest = function closest(el, fn) {
  return el && ( fn(el) ? el : closest(el.parentNode, fn) );
};



// ленивая подгрузка
const LazyLoad = require('vanilla-lazyload');
new LazyLoad({
  elements_selector: ".photo__img",
  callback_loaded: function(img){
    img.closest('.photo').classList.add('photo--loaded');
  },
});



// галерея фото
const PhotoSwipe = require('./photoswipe.js');
const PhotoSwipeUI_Default = require('./photoswipe-ui-default.js');

// вернёт галерею и слайд из URL (#&gid=2&pid=1)
function photoswipeParseHash() {
  var hash = window.location.hash.substring(1),
  params = {};
  if(hash.length < 5) {
    return params;
  }
  var vars = hash.split('&');
  for (var i = 0; i < vars.length; i++) {
    if(!vars[i]) {
      continue;
    }
    var pair = vars[i].split('=');
    if(pair.length < 2) {
      continue;
    }
    params[pair[0]] = pair[1];
  }
  if(params.gid) {
    params.gid = parseInt(params.gid, 10);
  }
  return params;
}

// вернёт объект со слайдами для галереи
function parseThumbnailElements(el) {
  var thumbElements = el.querySelectorAll('figure'),
      numNodes = thumbElements.length,
      items = [],
      figureEl,
      linkEl,
      size,
      item;
  for(var i = 0; i < numNodes; i++) {
    figureEl = thumbElements[i]; // потомок, внутри которого нужно искать фото
    if(figureEl.nodeType !== 1) continue; // только узловые потомки
    linkEl = figureEl.querySelector('.js-photo'); // все ссылки
    size = linkEl.getAttribute('data-size').split('x');
    // будущий слайд
    item = {
      el: figureEl,
      src: linkEl.getAttribute('href'),
      // msrc: linkEl.children[0].getAttribute('src'), // пропорции миниатюры не совпадают с пропорциями фото
      w: parseInt(size[0], 10),
      h: parseInt(size[1], 10),
      title: figureEl.querySelector('figcaption').innerHTML,
      pid: linkEl.getAttribute('href'),
    };
    items.push(item);
  }
  return items;
}

// обработает клик на миниатюре фотогалереи
function onThumbnailsClick(e) {
  e = e || window.event;
  e.preventDefault ? e.preventDefault() : e.returnValue = false;
  var eTarget = e.target || e.srcElement;
  var clickedListItem = closest(eTarget, function(el) {
    return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
  });
  if(!clickedListItem) {
    return;
  }
  var clickedGallery = clickedListItem.parentNode,
      childNodes = clickedListItem.parentNode.childNodes,
      numChildNodes = childNodes.length,
      nodeIndex = 0,
      index;
  for (var i = 0; i < numChildNodes; i++) {
    if(childNodes[i].nodeType !== 1) {
      continue;
    }
    if(childNodes[i] === clickedListItem) {
      index = nodeIndex;
      break;
    }
    nodeIndex++;
  }
  if(index >= 0) {
    openPhotoSwipe( index, clickedGallery );
  }
  return false;
}

// откроет PhotoSwipe
function openPhotoSwipe(index, galleryElement, fromURL) {
  var pswpElement = document.querySelectorAll('.pswp')[0], photoSwipe, options, items;
  // собственно, фотки
  items = parseThumbnailElements(galleryElement);
  // настройки PhotoSwipe
  options = {
    shareEl: false,
    galleryUID: galleryElement.getAttribute('data-pswp-uid'),
    showHideOpacity: true, // пропорции миниатюры не совпадают с пропорциями фото
    getThumbBoundsFn: false, // пропорции миниатюры не совпадают с пропорциями фото
    timeToIdle: false,
    timeToIdleOutside: false,
    // pinchToClose: false,
    // preloaderEl: true,
    // tapToClose: false,
    // clickToCloseNonZoomable: false,
    // closeElClasses: ['caption', 'ui', 'top-bar'],
  };
  if(fromURL) {
    for(var j = 0; j < items.length; j++) {
      if(items[j].pid == index) {
        options.index = j;
        break;
      }
    }
  } else { options.index = parseInt(index, 10); }
  if( isNaN(options.index) ) { return; }
  photoSwipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
  photoSwipe.init();
}

// повесим слежение за кликом на галерею (пока — одну)
var galleryElements = document.querySelectorAll('#photogallery');
for(var i = 0, l = galleryElements.length; i < l; i++) {
  galleryElements[i].setAttribute('data-pswp-uid', i+1);
  galleryElements[i].onclick = onThumbnailsClick;
}

var hashData = photoswipeParseHash();
// если это фотогалерея
if(hashData.pid && hashData.gid && hashData.gid == 1980) {
  textPhotoSwipe(hashData.pid);
}
else if(hashData.pid && hashData.gid) {
  openPhotoSwipe( hashData.pid ,  galleryElements[ hashData.gid - 1 ], true );
}



function textPhotoSwipe(index) {

  var pswpElement = document.querySelectorAll('.pswp')[0];
  var items = [
    {
      html: document.querySelector('#about').innerHTML,
      pid: 'about',
    },
    {
      html: document.querySelector('#photohelp').innerHTML,
      pid: 'memo',
    },
  ];
  var options = {
    mainClass: 'pswp--text',
    timeToIdle: false,
    timeToIdleOutside: false,
    showHideOpacity: true,
    pinchToClose: false,
    closeOnScroll: false,
    closeOnVerticalDrag: false,
    galleryUID: '1980',
    captionEl: false,
    fullscreenEl: false,
    zoomEl: false,
    shareEl: false,
    counterEl: false,
    arrowEl: false,
    preloaderEl: true,
    tapToClose: false,
    clickToCloseNonZoomable: false,
    closeElClasses: ['caption', 'ui', 'top-bar'],
  };
  for(var j = 0; j < items.length; j++) {
    if(items[j].pid == index) {
      options.index = j;
      break;
    }
  }
  var modal = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
  modal.init();
}

document.querySelector('.welcome__header').addEventListener('click', function(e){
  e.preventDefault();
  textPhotoSwipe();
});

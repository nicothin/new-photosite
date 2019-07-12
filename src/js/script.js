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

// ленивая подгрузка
const LazyLoad = require('vanilla-lazyload');
new LazyLoad({
  elements_selector: ".photo__img",
  callback_loaded: function(img){
    img.closest('.photo').classList.add('photo--loaded');
  },
});

// галерея фото
const PhotoSwipe = require('photoswipe/dist/photoswipe.js');
const PhotoSwipeUI_Default = require('photoswipe/dist/photoswipe-ui-default.js');
var initPhotoSwipeFromDOM = function(gallerySelector) {

  // парсим данные о фотках
  var parseThumbnailElements = function(el) {
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
  };


  // еще один свой closest, сученька!
  var closest = function closest(el, fn) {
    return el && ( fn(el) ? el : closest(el.parentNode, fn) );
  };

  // отрабатываем клик на превьюхе
  var onThumbnailsClick = function(e) {
    e = e || window.event;
    e.preventDefault ? e.preventDefault() : e.returnValue = false;

    var eTarget = e.target || e.srcElement;

    // find root element of slide
    var clickedListItem = closest(eTarget, function(el) {
        return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
    });

    if(!clickedListItem) {
        return;
    }

    // find index of clicked item by looping through all child nodes
    // alternatively, you may define index via data- attribute
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
        // open PhotoSwipe if valid index found
        openPhotoSwipe( index, clickedGallery );
    }
    return false;
  };

  // парсим картинку и галерею из URL (#&pid=1&gid=2)
  var photoswipeParseHash = function() {
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
  };


  // Ну, погнали, что ли...
  var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
    var pswpElement = document.querySelectorAll('.pswp')[0], gallery, options, items;
    // собственно, фотки
    items = parseThumbnailElements(galleryElement);
    // настройки PhotoSwipe
    options = {
      shareEl: false,
      galleryUID: galleryElement.getAttribute('data-pswp-uid'),
      showHideOpacity:true, // пропорции миниатюры не совпадают с пропорциями фото
      getThumbBoundsFn:false, // пропорции миниатюры не совпадают с пропорциями фото
    };
    // PhotoSwipe открыт по URL
    if(fromURL) {
      // получим номер фото, которое надо показать
      for(var j = 0; j < items.length; j++) {
        if(items[j].pid == index) {
          options.index = j;
          break;
        }
      }
    } else {
      options.index = parseInt(index, 10);
    }
    if( isNaN(options.index) ) {
      return;
    }

    // и вперед!
    gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.init();
  };

    // loop through all gallery elements and bind events
    var galleryElements = document.querySelectorAll( gallerySelector );

    for(var i = 0, l = galleryElements.length; i < l; i++) {
        galleryElements[i].setAttribute('data-pswp-uid', i+1);
        galleryElements[i].onclick = onThumbnailsClick;
    }

    // Parse URL and open gallery if it contains #&pid=3&gid=1
    var hashData = photoswipeParseHash();
    if(hashData.pid && hashData.gid) {
        openPhotoSwipe( hashData.pid ,  galleryElements[ hashData.gid - 1 ], true, true );
    }

};

// execute above function
initPhotoSwipeFromDOM('#photogallery');

/* global Element */

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

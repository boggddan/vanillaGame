// for IE 11
if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

if (!('remove' in Element.prototype)) {
  Element.prototype.remove = function() {
    if (this.parentNode) this.parentNode.removeChild(this);
  };
}

if (!Object.getOwnPropertyDescriptor(Element.prototype, 'classList') && HTMLElement) {
  const classList = Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'classList');
  classList && Object.defineProperty(Element.prototype, 'classList', classList);
}


(function () {
  "use strict";

  // -- Fix SVG filter=(#) fragment
// ref: http://blog.rodneyrehm.de/archives/25-Revisioning-Assets-using-base.html
// document's URL without the fragment
var baseUrl = location.href.replace(/#.*$/, '');
  //console.log(baseUrl)
// find the <rect>s to fix
var elements = document.querySelectorAll('rect');
  //console.log(elements)
for (var i=0, length = elements.length; i < length; i++) {
  var node = elements[i];
  // inject the document's URL into url(#something) values
  var value = node.getAttribute('filter');

  var _value = value.replace(/url\((['"]?)#/i, 'url($1' + baseUrl + '#');
  console.log(_value,value)
  if (value !== _value) {
    node.setAttribute('filter', _value);
  }
}
// -- Fix SVG Fragment


  var fallback = document.querySelector('feImage[result="slide2"]'),
      blended_bg = document.querySelector('.blended-background'),
      quotes = /"/g,
      bg,
      will_it_blend = function () {
        // var arca = document.createElement("div");
        // if ("backgroundBlendMode" in window.getComputedStyle(arca)) {
          document.body.className += " cssBackgroundBlendMode";
        // } else {
        //  document.body.className += " noCssBackgroundBlendMode";
        // }
      };

  will_it_blend();

  if (fallback !== null && blended_bg !== null) {
    bg = getComputedStyle(blended_bg).backgroundImage;
    if (bg !== "none") {
      fallback.removeAttributeNS('http://www.w3.org/199/xlink', 'xlink:href');
      fallback.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', bg.replace(quotes, '').slice(4, -1));
    }
  }
});




window.addEventListener('load', function(event) {
  console.log('All resources finished loading!');

  const cards = document.querySelectorAll('.memory-card');

  let firstCard = null,
    secondCard = null;

  const cardClick = event => {
    const elem = event.target;

    if (elem === firstCard || secondCard) return;

    elem.classList.add('flip');

    if (!firstCard) {
      firstCard = elem;
    } else {
      secondCard = elem;

      if (firstCard.dataset.value === secondCard.dataset.value) {
        firstCard.setAttribute('disabled', true);
        secondCard.setAttribute('disabled', true);
        firstCard = secondCard = null;
      } else {
        setTimeout(() => {
          firstCard.classList.remove('flip');
          secondCard.classList.remove('flip');
          firstCard = secondCard = null;
        }, 1500);
      }
    }
  };

  cards.forEach(card => {
    card.style.order = Math.floor(Math.random() * 12);
    card.dataset.value = card.querySelector('.front-face').alt;
    card.addEventListener('click', cardClick);
  });
});

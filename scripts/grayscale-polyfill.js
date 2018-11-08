'use strict';

// Запускается в браузерах который не поддерживают свойство filter.
// Полифил применяетс к тегам img[data-filter='grayscale(50%)']
// по значению grayscale(50%) создается фильтр #filter-grayscale-50, который
// нужно использовать с CSS `filter: url('#filter-grayscale-50')`
// Можно создать несколько фильтров задав разное значение в элементах.

// Удаляет элемент и создает обертку c контейнера (div) и 3-ех SVG:
// 1) фон с приминением фильтра - необходимо создавать, что бы его расстянуть
// на весь родительский блок, если в нем заданы отступы padding для изображения
// 2) изображения для фильтра
// 3) изображение без фильтра - нужно что бы создать анимацию для фильтра. В IE 11 не
// filter: url() не работает анимация, поэтому используем прозрачность (opacity)
//
// В IE 11 и EDGE - CSS нужно прописывать стили, не на сам SVG, а на его элементы
//
//    .svg-filter-background *,
//    .svg-filter * {
//       filter: url('#filter-grayscale-100');
//    }
//
//
//

// Нужно перекомилировать через Babel
// Неообходимые полифилы для IE-11
// Element.classList
// NodeList.forEach
// Element.remove

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

if (!('remove' in Element.prototype)) {
  Element.prototype.remove = function () {
    if (this.parentNode) this.parentNode.removeChild(this);
  };
}

if (!Object.getOwnPropertyDescriptor(Element.prototype, 'classList') && HTMLElement) {
  var classList = Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'classList');
  classList && Object.defineProperty(Element.prototype, 'classList', classList);
}

(function () {
  // В IE 11 fiter значение не применяется, но само свойство присуствует
  var isSupport = function () {
    var testImg = new Image();
    testImg.style.filter = 'grayscale(100%)';
    return !testImg.style.filter;
  }();

  // Создаем фильтр
  var createSVGFilterGrayScale = function createSVGFilterGrayScale(_ref) {
    var filterId = _ref.filterId,
        filterValue = _ref.filterValue;

    var namespaceURI = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(namespaceURI, 'svg');

    svg.style.position = 'absolute';
    svg.style.top = '0';
    svg.style.left = '0';
    svg.style.height = '0';
    svg.style.width = '0';

    var defs = svg.appendChild(document.createElementNS(namespaceURI, 'defs'));

    var filter = defs.appendChild(document.createElementNS(namespaceURI, 'filter'));
    filter.setAttribute('id', filterId);
    var feColorMatrix = filter.appendChild(document.createElementNS(namespaceURI, 'feColorMatrix'));
    feColorMatrix.setAttribute('id', 'filter-grayscale-elem');
    feColorMatrix.setAttribute('type', 'saturate');
    feColorMatrix.setAttribute('values', '' + (1 - filterValue / 100));

    document.body.appendChild(svg);
  };

  var createSVGElem = function createSVGElem(_ref2) {
    var id = _ref2.id,
        cssText = _ref2.cssText,
        classList = _ref2.classList,
        src = _ref2.src,
        backgroundColor = _ref2.backgroundColor;

    var container = document.createElement('div');
    id && container.setAttribute('id', id);
    classList && container.classList.add(classList);
    cssText && (container.style.cssText = cssText);
    container.style.position === 'absolute' && (container.style.position = 'relative');

    var namespaceURI = 'http://www.w3.org/2000/svg';

    // Создаем SVG для фона c наложеным фильтром
    // потому что у IE11 и EDGE не
    // работает filter если его применить в SVG
    var svgBackground = container.appendChild(document.createElementNS(namespaceURI, 'svg'));
    svgBackground.classList.add('svg-filter-background');
    svgBackground.setAttribute('width', '100%');
    svgBackground.setAttribute('height', '100%');
    svgBackground.style.position = 'absolute';
    svgBackground.style.top = '0';
    svgBackground.style.left = '0';

    var shapeBackground = svgBackground.appendChild(document.createElementNS(namespaceURI, 'rect'));
    shapeBackground.style.position = 'absolute';
    shapeBackground.setAttribute('x', '0');
    shapeBackground.setAttribute('y', '0');
    shapeBackground.setAttribute('width', '100%');
    shapeBackground.setAttribute('height', '100%');
    shapeBackground.setAttribute('fill', backgroundColor);

    // Создаем 2 картинки (обычная и с наложенным фильтром) для анимации фильтра
    // через opacity делаем отображение изображения
    ['filter', 'real'].forEach(function (el) {
      var svg = container.appendChild(document.createElementNS(namespaceURI, 'svg'));
      svg.classList.add('svg-' + el);
      svg.setAttribute('width', '100%');
      svg.setAttribute('height', '100%');
      svg.style.position = 'absolute';
      svg.style.top = '0';
      svg.style.left = '0';
      svg.style.padding = 'inherit';
      svg.style.zIndex = '9999';

      var image = svg.appendChild(document.createElementNS(namespaceURI, 'image'));
      src && image.setAttributeNS('http://www.w3.org/1999/xlink', 'href', src);
      image.setAttribute('width', '100%');
      image.setAttribute('height', '100%');
    });

    return container;
  };

  var checkFilterExists = function checkFilterExists(_ref3) {
    var filterId = _ref3.filterId,
        filterValue = _ref3.filterValue;

    if (!document.getElementById(filterId)) {
      createSVGFilterGrayScale({ filterId: filterId, filterValue: filterValue });
    }
  };

  var replaceElem = function replaceElem(el) {
    var id = el.getAttribute('id');
    var classList = el.classList;
    var cssText = el.style.cssText;
    var src = el.getAttribute('src');
    var backgroundColor = getComputedStyle(el).backgroundColor;

    var filterValue = (el.dataset.filter.match(/grayscale\(\s*(\d+)%\s*\)/) || [])[1] || 0;
    var filterId = 'filter-grayscale-' + filterValue;

    checkFilterExists({ filterId: filterId, filterValue: filterValue });

    var container = createSVGElem({ id: id, cssText: cssText, classList: classList, src: src, backgroundColor: backgroundColor });
    el.parentNode.insertBefore(container, el);
    el.remove();
  };

  var applyFilter = function applyFilter() {
    var elements = document.querySelectorAll('img[data-filter*=\'grayscale\']');
    if (elements) {
      elements.forEach(replaceElem);
    }
  };

  if (isSupport) window.addEventListener('load', applyFilter);
})();
//# sourceMappingURL=grayscale-polyfill.js.map

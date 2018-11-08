'use strict';

// Запускается в браузерах который не поддерживают свойство background-blend-mode.
// Полифил применяетс к тегам [data-background-blend-mode="multiply"]
// Изображение и цвет берет с CSS свойств (background-image, background-color)
// IE 11 работает огранниченное количество фильтров: normal, myltiply, lighten, screen, darken
//   https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/samples/jj206437(v=vs.85)

// Нужно перекомилировать через Babel
// Неообходимые полифилы для IE-11
// Element.prepend
// NodeList.forEach

// prepend polyfill
[Element.prototype, Document.prototype, DocumentFragment.prototype].forEach(function (item) {
  item.prepend = item.prepend || function () {
    var docFrag = document.createDocumentFragment();

    Array.prototype.slice.call(arguments).forEach(function (argItem) {
      docFrag.appendChild(argItem instanceof Node ? argItem : document.createTextNode(String(argItem)));
    });

    this.insertBefore(docFrag, this.firstChild);
  };
});

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

(function () {
  var isNotSupport = getComputedStyle(document.body).backgroundBlendMode === undefined;

  // Создаем фильтр
  var createSVGFilter = function createSVGFilter(_ref) {
    var backgroundImage = _ref.backgroundImage,
        backgroundColor = _ref.backgroundColor,
        backgroundBlendMode = _ref.backgroundBlendMode;

    var uniqueId = new Date().getUTCMilliseconds();
    var filterId = 'background-filter-' + uniqueId;

    var namespaceURI = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(namespaceURI, 'svg');
    svg.setAttribute('height', '100%');
    svg.setAttribute('width', '100%');
    svg.style.position = 'absolute';
    svg.style.top = '0';
    svg.style.left = '0';
    svg.style.bottom = '0';
    svg.style.right = '0';
    svg.style.overflow = 'hidden';

    var defs = svg.appendChild(document.createElementNS(namespaceURI, 'defs'));
    var filter = defs.appendChild(document.createElementNS(namespaceURI, 'filter'));
    filter.setAttribute('id', filterId);

    var feImage = filter.appendChild(document.createElementNS(namespaceURI, 'feImage'));
    feImage.setAttribute('preserveAspectRatio', 'xMinYMin slice');
    feImage.setAttribute('x', '0');
    feImage.setAttribute('y', '0');
    feImage.setAttribute('height', '100%');
    feImage.setAttribute('width', '100%');
    feImage.setAttribute('result', 'slide2');
    feImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', backgroundImage);

    var feBlend = filter.appendChild(document.createElementNS(namespaceURI, 'feBlend'));
    feBlend.setAttribute('preserveAspectRatio', 'xMinYMin slice');
    feBlend.setAttribute('in', 'slide2');
    feBlend.setAttribute('in2', 'SourceGraphic');
    feBlend.setAttribute('mode', backgroundBlendMode);

    var shapeBackground = svg.appendChild(document.createElementNS(namespaceURI, 'rect'));
    shapeBackground.style.position = 'absolute';
    shapeBackground.setAttribute('x', '0');
    shapeBackground.setAttribute('y', '0');
    shapeBackground.setAttribute('width', '100%');
    shapeBackground.setAttribute('height', '100%');
    shapeBackground.setAttribute('filter', 'url(#' + filterId + ')');
    shapeBackground.setAttribute('fill', backgroundColor);

    return svg;
  };

  var applyFilter = function applyFilter() {
    var elements = document.querySelectorAll('[data-background-blend-mode]');

    if (elements) {
      elements.forEach(function (el) {
        var _getComputedStyle = getComputedStyle(el),
            borderTopWidth = _getComputedStyle.borderTopWidth,
            position = _getComputedStyle.position,
            borderWidth = _getComputedStyle.borderWidth,
            backgroundImageUrl = _getComputedStyle.backgroundImage,
            backgroundColor = _getComputedStyle.backgroundColor;

        var backgroundImage = backgroundImageUrl && backgroundImageUrl.replace(/"/g, '').slice(4, -1);
        var backgroundBlendMode = el.dataset.backgroundBlendMode;

        if (backgroundImage && backgroundColor && backgroundBlendMode) {
          if (position === 'static') el.style.position = 'relative';

          if (borderTopWidth) {
            el.style.borderTopWidth = '0.01px';
            el.style.borderTopColor = 'transparent';
            el.style.borderTopStyle = 'solid';
          }
          var svg = createSVGFilter({ backgroundImage: backgroundImage, backgroundColor: backgroundColor, backgroundBlendMode: backgroundBlendMode });
          el.prepend(svg);

          // Если фон для Body, нужно по вешать обработчик события на измнения размеров окна
          // потому что размер body может быть меньша размера документа, когда часть контента
          // выпадает
          if (el.tagName.toLowerCase() === 'body') {
            var running = false;
            var windowHeight = 0;

            var update = function update() {
              if (windowHeight !== window.innerHeight) {
                windowHeight = window.innerHeight;
                svg.style.height = 'auto';
                svg.style.height = document.documentElement.scrollHeight + 'px';
              }
              running = false;
            };

            var requestTick = function requestTick() {
              if (!running) requestAnimationFrame(update);
              running = true;
            };

            window.addEventListener('resize', requestTick);
          }
        }
      });
    }
  };

  // if (isNotSupport)
  window.addEventListener('load', applyFilter);
})();
//# sourceMappingURL=multiply-background-blend-mode-polyfill.js.map

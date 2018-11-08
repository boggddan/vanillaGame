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

(() => {
  const isNotSupport = getComputedStyle(document.body).backgroundBlendMode === undefined;

  // Создаем фильтр
  const createSVGFilter = ({ backgroundImage, backgroundColor, backgroundBlendMode }) => {
    const uniqueId = new Date().getUTCMilliseconds();
    const filterId = `background-filter-${uniqueId}`;

    const namespaceURI = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(namespaceURI, 'svg');
    svg.setAttribute('height', '100%');
    svg.setAttribute('width', '100%');
    svg.style.position = 'absolute';
    svg.style.top = '0';
    svg.style.left = '0';
    svg.style.bottom = '0';
    svg.style.right = '0';
    svg.style.overflow = 'hidden';

    const defs = svg.appendChild(document.createElementNS(namespaceURI, 'defs'));
    const filter = defs.appendChild(document.createElementNS(namespaceURI, 'filter'));
    filter.setAttribute('id', filterId);

    const feImage = filter.appendChild(document.createElementNS(namespaceURI, 'feImage'));
    feImage.setAttribute('preserveAspectRatio', 'xMinYMin slice');
    feImage.setAttribute('x', '0');
    feImage.setAttribute('y', '0');
    feImage.setAttribute('height', '100%');
    feImage.setAttribute('width', '100%');
    feImage.setAttribute('result', 'slide2');
    feImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', backgroundImage);

    const feBlend = filter.appendChild(document.createElementNS(namespaceURI, 'feBlend'));
    feBlend.setAttribute('preserveAspectRatio', 'xMinYMin slice');
    feBlend.setAttribute('in', 'slide2');
    feBlend.setAttribute('in2', 'SourceGraphic');
    feBlend.setAttribute('mode', backgroundBlendMode);

    const shapeBackground = svg.appendChild(document.createElementNS(namespaceURI, 'rect'));
    shapeBackground.style.position = 'absolute';
    shapeBackground.setAttribute('x', '0');
    shapeBackground.setAttribute('y', '0');
    shapeBackground.setAttribute('width', '100%');
    shapeBackground.setAttribute('height', '100%');
    shapeBackground.setAttribute('filter', `url(#${filterId})`);
    shapeBackground.setAttribute('fill', backgroundColor);

    return svg;
  };

  const applyFilter = () => {
    const elements = document.querySelectorAll(`[data-background-blend-mode]`);

    if (elements) {
      elements.forEach(el => {
        const { borderTopWidth, position, borderWidth, backgroundImage: backgroundImageUrl, backgroundColor } = getComputedStyle(el);
        const backgroundImage = backgroundImageUrl && backgroundImageUrl.replace(/"/g,'').slice(4, -1);
        const backgroundBlendMode = el.dataset.backgroundBlendMode;

        if (backgroundImage && backgroundColor && backgroundBlendMode) {
          if (position === 'static') el.style.position = 'relative';

          if (borderTopWidth) {
            el.style.borderTopWidth = '0.01px';
            el.style.borderTopColor = 'transparent';
            el.style.borderTopStyle = 'solid';
          }
          const svg = createSVGFilter({ backgroundImage, backgroundColor, backgroundBlendMode });
          el.prepend(svg);

          // Если фон для Body, нужно по вешать обработчик события на измнения размеров окна
          // потому что размер body может быть меньша размера документа, когда часть контента
          // выпадает
          if (el.tagName.toLowerCase() === 'body') {
	          let running = false;
            let windowHeight = 0;

            const update = () => {
              if (windowHeight !== window.innerHeight) {
                windowHeight = window.innerHeight;
                svg.style.height = 'auto';
                svg.style.height = `${document.documentElement.scrollHeight}px`;
              }
              running = false;
            }

            const requestTick = () => {
              if(!running) requestAnimationFrame(update);
              running = true;
            }

            window.addEventListener('resize', requestTick);
          }
        }
      });
    }
  };

  // if (isNotSupport)
  window.addEventListener('load', applyFilter);
})();

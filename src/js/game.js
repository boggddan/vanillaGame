// for IE 11
if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

if (!('remove' in Element.prototype)) {
  Element.prototype.remove = function () {
    if (this.parentNode) this.parentNode.removeChild(this);
  };
}

if (!Object.getOwnPropertyDescriptor(Element.prototype,'classList') && HTMLElement) {
  const classList = Object.getOwnPropertyDescriptor(HTMLElement.prototype,'classList');
  classList && Object.defineProperty(Element.prototype,'classList', classList);
}

window.addEventListener('load', function(event) {
  console.log("All resources finished loading!");var container = document.createElement("div");

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
  }

  cards.forEach(card => {
    card.style.order = Math.floor(Math.random() * 12);
    card.dataset.value = card.querySelector('.front-face').alt;
    card.addEventListener('click', cardClick);
  });
});


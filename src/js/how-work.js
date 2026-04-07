document.addEventListener("DOMContentLoaded", function () {
  const howWorkCards = document.querySelectorAll(".howWork__card");

  if (howWorkCards.length === 0) {
    return;
  }

  // Function to activate a card by index
  function activateCard(index) {
    // Remove active class from all cards
    howWorkCards.forEach((card) => {
      card.classList.remove("howWork__card--active");
    });

    // Add active class to the selected card (toggle if already active)
    if (howWorkCards[index]) {
      if (howWorkCards[index].classList.contains("howWork__card--active")) {
        howWorkCards[index].classList.remove("howWork__card--active");
      } else {
        howWorkCards[index].classList.add("howWork__card--active");
      }
    }
  }

  // Add click handlers to cards
  howWorkCards.forEach((card, index) => {
    card.addEventListener("click", function () {
      activateCard(index);
    });
  });
});

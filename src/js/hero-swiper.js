document.addEventListener("DOMContentLoaded", function () {
  if (typeof Swiper === "undefined") return;

  const heroSwiper = document.querySelector(".hero__swiper");
  if (heroSwiper) {
    new Swiper(".hero__swiper", {
      // Basic settings
      slidesPerView: 1,
      spaceBetween: 0,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      speed: 800,

      // Navigation arrows
      navigation: {
        nextEl: ".hero__swiper__next",
        prevEl: ".hero__swiper__prev",
      },

      // Pagination
      pagination: {
        el: ".hero__swiper__pagination",
        clickable: true,
      },

      // Effect
      effect: "fade",
      fadeEffect: {
        crossFade: true,
      },

      // Keyboard control
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      // Accessibility
      a11y: {
        prevSlideMessage: "Previous slide",
        nextSlideMessage: "Next slide",
      },
    });
  }
});

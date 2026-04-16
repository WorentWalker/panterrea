jQuery(document).ready(function ($) {
  const $catalogPostSlider = $("#catalogPostSlider");
  const $catalogPostSliderCounter = $("#catalogPostSliderCounter");
  const $catalogPostSliderPrev = $("#catalogPostSliderPrev");
  const $catalogPostSliderNext = $("#catalogPostSliderNext");

  if ($catalogPostSlider.length) {
    $catalogPostSlider.slick({
      accessibility: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: false,
    });

    if (
      $catalogPostSliderCounter &&
      $catalogPostSliderPrev &&
      $catalogPostSliderNext
    ) {
      $catalogPostSlider.on(
        "afterChange",
        function (event, slick, currentSlide) {
          const totalSlides = slick.slideCount;
          $catalogPostSliderCounter.text(`${currentSlide + 1}/${totalSlides}`);
        }
      );

      $catalogPostSliderPrev.on("click", function () {
        $catalogPostSlider.slick("slickPrev");
      });

      $catalogPostSliderNext.on("click", function () {
        $catalogPostSlider.slick("slickNext");
      });
    }
  }

  const $categorySlider = $("#categorySlider");
  const $categorySliderPrev = $("#categorySliderPrev");
  const $categorySliderNext = $("#categorySliderNext");

  if ($categorySlider.length) {
    $categorySlider.slick({
      accessibility: true,
      slidesToShow: 7,
      arrows: false,
      dots: false,
      infinite: true,
      swipeToSlide: true,
      variableWidth: true,
      responsive: [
        {
          breakpoint: 659,
          settings: {
            centerMode: true,
          },
        },
      ],
    });

    if (window.innerWidth >= 659) {
      setTimeout(() => {
        let $track = $categorySlider.find(".slick-track");
        let currentTransform = $track.css("transform");

        if (currentTransform !== "none") {
          let matrix = currentTransform.replace(/[^0-9\-.,]/g, "").split(",");
          let currentX = parseFloat(matrix[4]) || 0;
          $track.css("transform", `translateX(${currentX + 35}px)`);
        } else {
          $track.css("transform", "translateX(20px)");
        }
      }, 200);
    }

    if ($categorySliderPrev && $categorySliderNext) {
      $categorySliderPrev.on("click", function () {
        $categorySlider.slick("slickPrev");
      });

      $categorySliderNext.on("click", function () {
        $categorySlider.slick("slickNext");
      });
    }
  }

  const $categorySelectSlider = $("#categorySelectSlider");
  const $categorySelectSliderPrev = $("#categorySelectSliderPrev");
  const $categorySelectSliderNext = $("#categorySelectSliderNext");

  if ($categorySelectSlider.length) {
    $categorySelectSlider.slick({
      accessibility: true,
      slidesToShow: 6,
      slidesToScroll: 1,
      arrows: false,
      dots: false,
      swipeToSlide: true,
      infinite: false,
      responsive: [
        {
          breakpoint: 1260,
          settings: {
            slidesToShow: 4,
          },
        },
        {
          breakpoint: 1023,
          settings: {
            slidesToShow: 3,
          },
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 2,
          },
        },
        {
          breakpoint: 560,
          settings: {
            slidesToShow: 1,
          },
        },
      ],
    });

    function updateNavButtons() {
      const currentSlide = $categorySelectSlider.slick("slickCurrentSlide");
      const totalSlides = $categorySelectSlider.slick("getSlick").slideCount;
      const slidesToShow =
        $categorySelectSlider.slick("getSlick").options.slidesToShow;

      if (currentSlide === 0) {
        $categorySelectSliderPrev.addClass("disabled");
      } else {
        $categorySelectSliderPrev.removeClass("disabled");
      }

      if (currentSlide + slidesToShow >= totalSlides) {
        $categorySelectSliderNext.addClass("disabled");
      } else {
        $categorySelectSliderNext.removeClass("disabled");
      }
    }

    $categorySelectSlider.on("init", updateNavButtons);

    if ($categorySelectSliderPrev && $categorySelectSliderNext) {
      $categorySelectSliderPrev.on("click", function () {
        $categorySelectSlider.slick("slickPrev");
        updateNavButtons();
      });

      $categorySelectSliderNext.on("click", function () {
        $categorySelectSlider.slick("slickNext");
        updateNavButtons();
      });
    }

    $categorySelectSlider.on("afterChange", updateNavButtons);
  }

  $(document).on("click", ".js-forumSlider", function () {
    const $clicked = $(this);
    const $mediaContainer = $clicked.closest(".forum__itemPost__media");
    const mediaData = $mediaContainer.data("media");
    const $popup = $("#mediaPopup");
    const $slider = $popup.find(".mediaSlider");

    if (!mediaData || !mediaData.length) return;

    // Determine start index from the clicked item's data-index attribute
    const startIndex = parseInt($clicked.data("index") || 0, 10);

    if ($slider.hasClass("slick-initialized")) {
      $slider.slick("unslick");
    }
    $slider.html("");

    mediaData.forEach((item) => {
      const url = item.url || "";
      const type = item.type || "";
      if (type.startsWith("image")) {
        $slider.append(`<div><img src="${url}" alt=""></div>`);
      } else if (type.startsWith("video")) {
        $slider.append(`
          <div>
            <video controls playsinline>
              <source src="${url}" type="${type}">
              Ваш браузер не підтримує відео.
            </video>
          </div>`);
      }
    });

    $slider.slick({
      arrows: true,
      infinite: true,
      initialSlide: startIndex,
      prevArrow: $(".forum-slick-prev"),
      nextArrow: $(".forum-slick-next"),
    });

    // Pause all videos when changing slide
    $slider.on("beforeChange", function () {
      $slider.find("video").each(function () {
        this.pause();
      });
    });

    $popup.fadeIn();
    document.body.classList.add("noScroll");
  });

  function closeMediaPopup() {
    const $popup = $("#mediaPopup");
    const $slider = $popup.find(".mediaSlider");
    // Stop any playing video before closing
    $slider.find("video").each(function () { this.pause(); });
    $popup.fadeOut(() => {
      if ($slider.hasClass("slick-initialized")) {
        $slider.slick("unslick");
      }
      $slider.html("");
      document.body.classList.remove("noScroll");
    });
  }

  $(".mediaPopup__close").on("click", closeMediaPopup);

  // Close on backdrop click
  $(document).on("click", "#mediaPopup", function (e) {
    if ($(e.target).is("#mediaPopup")) {
      closeMediaPopup();
    }
  });
});

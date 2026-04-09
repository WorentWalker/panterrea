const SHOWPASS_SELECTOR = ".js-togglePass";

const TOGGLE_SWITCH_SELECTOR = ".js-toggleSwitch";

const SHOW_PHONE_SELECTOR = ".js-showPhone";

const SHARE_BUTTON_SELECTOR = ".js-share";

const CATALOG_CONTAINER =
  "#catalogItems, #catalogDeactivatedItems, #catalogPublishItems, #catalogPostButtons, .catalogPost__subContent__posts";
const BUTTON_FAVORITES = ".js-favorites";

const FAKE_SELECT_WRAPPER = ".js-fakeSelectWrapper";
const FAKE_SELECT_INPUT = ".js-fakeSelectInput";
const FAKE_SELECT_OPTION = ".js-fakeSelectOptions";

const OPEN_POPUP_BUTTON = "js-openPopUp";
const CLOSE_POPUP_BUTTON = "js-closePopUp";

const POPUP = "js-popUp";

const TOGGLE_STATUS_SWITCHER = "#statusAd";

function markNotificationsAsRead() {
  let unreadNotifications = document.querySelectorAll(
    ".notificationPanel__item__text.subtitle2:not(.read)",
  );
  let notificationIcon = document.getElementById("toggleNotificationPanel");

  if (unreadNotifications.length > 0) {
    unreadNotifications.forEach((notification) => {
      notification.classList.add("read");
    });

    if (notificationIcon) {
      notificationIcon.classList.remove("new");
    }

    fetch(mainObject.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "mark_notifications_read",
        security: mainObject.notification_nonce,
      }),
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("walker");

  class Panterrea {
    static checkNotificationFromCookies() {
      const type = getCookie("message_type");
      const message = getCookie("message_text");

      if (type && message) {
        const decodedMessage = decodeURIComponent(message);
        MessageSystem.showMessage(type, decodedMessage);

        document.cookie = "message_type=; Max-Age=0; path=/";
        document.cookie = "message_text=; Max-Age=0; path=/";
      }
    }

    static togglePass() {
      const togglePassElements = document.querySelectorAll(SHOWPASS_SELECTOR);
      togglePassElements.forEach((togglePass) => {
        togglePass.addEventListener("click", (e) => {
          const passwordInput = e.target
            .closest(".input__form")
            .querySelector("input");
          if (passwordInput) {
            const isPasswordVisible = passwordInput.type === "password";
            passwordInput.type =
              passwordInput.type === "password" ? "text" : "password";
            togglePass.classList.toggle("showPass", isPasswordVisible);
          }
        });
      });
    }

    static toggleStatusAd() {
      const toggleStatusSwitcher = document.querySelector(
        TOGGLE_STATUS_SWITCHER,
      );
      if (toggleStatusSwitcher) {
        toggleStatusSwitcher.addEventListener("click", function () {
          const switcher = this;
          const postId = mainObject.postID;

          toggleLoadingCursor(true);

          fetch(mainObject.ajax_url, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "toggle_catalog_post_status",
              security: mainObject.statusAd_nonce,
              post_id: postId,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const isActive = data.data.is_active === "1";
                const message = data.data.message;

                if (!isActive) {
                  switcher.classList.add("active");
                } else {
                  switcher.classList.remove("active");
                }

                setMessageCookies("success", getTranslatedText(message));
                window.location.reload();
              } else {
                const errorMessage =
                  data.data.message ?? getTranslatedText("error_generic");
                /*const errorMessage = data.data.message ?? getTranslation('error_generic', 'Щось пішло не так. Спробуйте ще раз.');*/
                MessageSystem.showMessage("error", errorMessage);
              }
            })
            .catch((error) => {
              //console.error('Error:', error);
            })
            .finally(() => {
              toggleLoadingCursor(false);
            });
        });
      }
    }

    static showUserPhone() {
      const showPhone = document.querySelector(SHOW_PHONE_SELECTOR);
      if (showPhone) {
        showPhone.addEventListener("click", function () {
          const authorId = this.getAttribute("data-user");
          const loggedIn = mainObject.loggedIn === "true";
          const emailConfirmed = mainObject.emailConfirmed === "true";
          const button = this;

          if (button.classList.contains("shown")) {
            return;
          }

          if (!loggedIn) {
            const message = getTranslatedText("login_required", {
              url: mainObject.loginURL,
            });

            /*const message = getTranslation('login_required', {
                            url: mainObject.loginURL
                        }) || '<a href="' + mainObject.loginURL + '">Авторизуйтесь</a> для доступу.';*/
            MessageSystem.showMessage("warning", message);
            return;
          }

          if (!emailConfirmed) {
            const message = getTranslatedText("email_confirmation_required");
            /*const message = getTranslation('email_confirmation_required') || 'Підтвердіть свою електронну пошту для доступу.';*/
            MessageSystem.showMessage("warning", message);
            return;
          }

          toggleLoadingCursor(true);

          fetch(mainObject.ajax_url, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "get_author_phone",
              security: mainObject.phone_nonce,
              author_id: authorId,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                button.textContent = `${getTranslatedText("mobile")} ${data.data.phone}`;
                /*button.textContent = `${getTranslation('mobile')} ${data.data.phone}`;*/
                button.classList.add("shown");
              } else {
                if (data.data.errors) {
                  MessageSystem.showMessage("warning", data.data.errors);
                }
              }
            })
            .catch((error) => {
              //console.error('Error:', error);
            })
            .finally(() => {
              toggleLoadingCursor(false);
            });
        });
      }
    }

    static shareUrl() {
      const shareBtn = document.querySelectorAll(SHARE_BUTTON_SELECTOR);
      if (shareBtn) {
        shareBtn.forEach((shareBtn) => {
          shareBtn.addEventListener("click", function () {
            const currentUrl = window.location.href;
            const textSpan = this.querySelector("span");
            const originalText = textSpan ? textSpan.textContent : "";

            navigator.clipboard
              .writeText(currentUrl)
              .then(function () {
                // Add copied state
                shareBtn.classList.add("copied");
                if (textSpan) {
                  textSpan.textContent = "Скопійовано";
                }

                // Reset after 2 seconds
                setTimeout(() => {
                  shareBtn.classList.remove("copied");
                  if (textSpan) {
                    textSpan.textContent = originalText;
                  }
                }, 2000);
              })
              .catch(function (err) {
                MessageSystem.showMessage(
                  "warning",
                  getTranslatedText("link_copy_error"),
                );
                /*MessageSystem.showMessage('warning', getTranslation('link_copy_error'));*/
              });
          });
        });
      }
    }

    static toggleFavorites() {
      const catalogContainer = document.querySelectorAll(CATALOG_CONTAINER);

      const handleFavoriteClick = (event) => {
        const button = event.target.closest(BUTTON_FAVORITES);

        if (button) {
          const postId = button.getAttribute("data-post-id");
          const isFavorite = button.classList.contains("active");
          const loggedIn = mainObject.loggedIn === "true";
          const emailConfirmed = mainObject.emailConfirmed === "true";

          if (!loggedIn) {
            const message = getTranslatedText("login_required", {
              url: mainObject.loginURL,
            });
            /*const message = getTranslation('login_required', {
                            url: mainObject.loginURL
                        }) || '<a href="' + mainObject.loginURL + '">Авторизуйтесь</a> для доступу.';*/
            MessageSystem.showMessage("warning", message);
            return;
          }

          if (!emailConfirmed) {
            const message = getTranslatedText("email_confirmation_required");
            /*const message = getTranslation('email_confirmation_required') || 'Підтвердіть свою електронну пошту для доступу.';*/
            MessageSystem.showMessage("warning", message);
            return;
          }

          toggleLoadingCursor(true);

          fetch(mainObject.ajax_url, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "manage_favorites",
              action_type: isFavorite ? "remove" : "add",
              security: mainObject.favorites_nonce,
              post_id: postId,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const allButtons = document.querySelectorAll(
                  `[data-post-id="${postId}"]`,
                );

                if (isFavorite) {
                  allButtons.forEach((btn) => btn.classList.remove("active"));
                  MessageSystem.showMessage(
                    "success",
                    getTranslatedText("removed_from_favorites"),
                  );
                  /*MessageSystem.showMessage('success', getTranslation('removed_from_favorites'));*/
                } else {
                  allButtons.forEach((btn) => btn.classList.add("active"));
                  MessageSystem.showMessage(
                    "success",
                    getTranslatedText("added_to_favorites"),
                  );
                  /*MessageSystem.showMessage('success', getTranslation('added_to_favorites'));*/
                }
              } else {
                MessageSystem.showMessage(
                  "error",
                  data.data.errors || getTranslatedText("request_error"),
                );
                /*MessageSystem.showMessage('error', data.data.errors || getTranslation('request_error'));*/
              }
            })
            .catch((error) => {
              console.error("Error:", error);
            })
            .finally(() => {
              toggleLoadingCursor(false);
            });
        }
      };

      if (catalogContainer) {
        catalogContainer.forEach((post) => {
          post.addEventListener("click", handleFavoriteClick);
        });
      }
    }

    static fakeSelect() {
      const selects = document.querySelectorAll(FAKE_SELECT_WRAPPER);

      if (selects.length > 0) {
        selects.forEach((select) => {
          const input = select.querySelector(FAKE_SELECT_INPUT);
          const options = select.querySelector(FAKE_SELECT_OPTION);

          input.addEventListener("click", () => {
            const isVisible = options.style.display === "flex";

            document.querySelectorAll(FAKE_SELECT_OPTION).forEach((opt) => {
              opt.style.display = "none";
            });

            options.style.display = isVisible ? "none" : "flex";
          });

          options.addEventListener("click", (event) => {
            if (event.target.tagName === "LI") {
              input.value = event.target.textContent.trim();
              options.style.display = "none";

              input.dispatchEvent(new Event("input"));
            }
          });

          document.addEventListener("click", (event) => {
            if (!select.contains(event.target)) {
              options.style.display = "none";
            }
          });
        });

        function updateFakeSelectInputs() {
          document.querySelectorAll(".js-fakeSelectInput").forEach((input) => {
            const originalValue = input.value.trim();

            const selectWrapper =
              input.closest(".input__formAd, .input__formTextarea") ||
              input.parentElement;
            if (!selectWrapper) return;

            const options = selectWrapper.querySelectorAll(
              ".js-fakeSelectOptions li",
            );
            if (!options.length) return;

            const matchedOption = Array.from(options).find(
              (li) => li.getAttribute("data-value") === originalValue,
            );

            if (matchedOption) {
              input.value = matchedOption.textContent.trim();
            }
          });
        }

        updateFakeSelectInputs();
      }
    }

    static popUp() {
      document.addEventListener("click", (event) => {
        if (event.target.classList.contains(OPEN_POPUP_BUTTON)) {
          const popUpId = event.target.getAttribute("data-popUp");
          const popUp = document.getElementById(popUpId);
          if (popUp) {
            popUp.classList.remove("hidden");
            document.body.classList.add("noScroll");

            setTimeout(() => {
              const slider = popUp.querySelector(".slick-initialized");
              if (window.jQuery && slider) {
                jQuery(slider).slick("setPosition");
                jQuery(slider).slick("refresh");
              }
            }, 100);
          }
        }
      });

      document.addEventListener("click", (event) => {
        if (event.target.classList.contains(CLOSE_POPUP_BUTTON)) {
          const popUpId = event.target.getAttribute("data-popUp");
          const popUp = document.getElementById(popUpId);
          if (popUp) {
            popUp.classList.add("hidden");
            checkIfPopupsOpen();
          }
        }
      });

      document.addEventListener("click", (event) => {
        const popUp = event.target.closest(POPUP);
        if (!popUp && event.target.classList.contains(POPUP)) {
          event.target.classList.add("hidden");
          checkIfPopupsOpen();
        }
      });

      function checkIfPopupsOpen() {
        const openPopups = document.querySelectorAll(`.${POPUP}:not(.hidden)`);
        if (openPopups.length === 0) {
          document.body.classList.remove("noScroll");
        }
      }
    }

    static symbolCount() {
      const inputsWithCount = document.querySelectorAll("[data-symbolCount]");

      inputsWithCount.forEach((input) => {
        const maxSymbols = parseInt(input.getAttribute("data-symbolCount"), 10);
        if (isNaN(maxSymbols)) return;

        const parent = input.closest(".input__formAd, .input__formTextarea");
        const helper = parent ? parent.querySelector(".symbolCount") : null;
        const valueSpan = helper
          ? helper.querySelector(".symbolCount__value")
          : null;
        if (!helper || !valueSpan) return;

        const updateSymbolCount = () => {
          const currentLength = input.value.length || 0;
          /*let translation = getTranslation('characters_used', {
                        current: currentLength,
                        max: maxSymbols
                    });

                    translation = translation.replace('{current}', currentLength);*/

          valueSpan.textContent = `${currentLength}/${maxSymbols}`;

          if (currentLength > maxSymbols) {
            helper.classList.add("tooMuch");
          } else {
            helper.classList.remove("tooMuch");
          }
        };

        updateSymbolCount();

        input.addEventListener("input", updateSymbolCount);
      });
    }

    static deleteAd() {
      const deleteAdButton = document.getElementById("deleteAdButton");

      if (deleteAdButton) {
        deleteAdButton.addEventListener("click", () => {
          const postId = mainObject.postID;

          if (!postId) {
            return;
          }

          toggleLoadingCursor(true);

          fetch(mainObject.ajax_url, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "delete_catalog_post",
              security: mainObject.deleteAd_nonce,
              post_id: postId,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const successMessage = getTranslatedText("ad_deleted_success");
                /*const successMessage = getTranslation('ad_deleted_success');*/
                setMessageCookies("success", successMessage);
                window.location.href = mainObject.userAdURL;
              } else {
                const errorMessage =
                  data.data.message ?? getTranslatedText("error_generic");
                /*const errorMessage = data.data.message ?? getTranslation('error_generic');*/
                MessageSystem.showMessage("error", errorMessage);
              }
            })
            .catch((error) => {})
            .finally(() => {
              toggleLoadingCursor(false);
            });
        });
      }
    }

    static toggleNotificationPanel() {
      const panel = document.querySelector(".notificationPanel");
      const toggleButton = document.querySelector("#toggleNotificationPanel");
      const closeButton = document.querySelector("#closeNotificationPanel");

      if (panel && toggleButton) {
        toggleButton.addEventListener("click", () => {
          let isOpening = !panel.classList.contains("open");

          panel.classList.toggle("open");

          if (!isOpening) {
            markNotificationsAsRead();
          }
        });
      }

      if (panel && closeButton) {
        closeButton.addEventListener("click", () => {
          panel.classList.remove("open");
          markNotificationsAsRead();
        });
      }
    }

    static deleteAllNotification() {
      const deleteButton = document.getElementById("deleteAllNotification");

      if (!deleteButton) return;

      deleteButton.addEventListener("click", () => {
        toggleLoadingCursor(true);
        fetch(mainObject.ajax_url, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "delete_all_notifications",
            security: mainObject.notification_nonce,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              const notificationContent = document.getElementById(
                "notificationContent",
              );
              const notificationCount =
                document.getElementById("notificationTitle");

              if (notificationContent) {
                notificationContent.innerHTML = "";
              }

              if (notificationCount) {
                notificationCount.innerHTML = getTranslatedText(
                  "notifications_count",
                );
                /*notificationCount.innerHTML = getTranslation('notifications_count', 'Сповіщення (0)');*/
              }

              const successMessage = getTranslatedText(
                "notifications_deleted_success",
              );
              /*const successMessage = getTranslation('notifications_deleted_success');*/
              MessageSystem.showMessage("success", successMessage);
            }
          })
          .catch()
          .finally(() => {
            toggleLoadingCursor(false);
          });
      });
    }

    static toggleEmailNotification() {
      const toggleSwitchers = document.querySelectorAll(
        ".js-toggleSwitch[data-type]",
      );

      toggleSwitchers.forEach((switcher) => {
        switcher.addEventListener("click", function () {
          const type = this.dataset.type;
          const switcherEl = this;

          toggleLoadingCursor(true);

          fetch(mainObject.ajax_url, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "toggle_email_notification_status",
              type: type,
              security: mainObject.notification_nonce,
            }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                const isActive = data.data.is_active === "1";

                switcherEl.classList.toggle("active", isActive);

                MessageSystem.showMessage(
                  "success",
                  getTranslatedText("settings_saved"),
                );
                /*MessageSystem.showMessage('success', getTranslation('settings_saved', 'Налаштування збережено.'));*/
              } else {
                const errorMessage =
                  data.data.message ?? getTranslatedText("error_generic");
                /*const errorMessage = data.data.message ?? getTranslation('error_generic', 'Щось пішло не так. Спробуйте ще раз.');*/
                MessageSystem.showMessage("error", errorMessage);
              }
            })
            .catch((error) => {
              // console.error('Error:', error);
            })
            .finally(() => {
              toggleLoadingCursor(false);
            });
        });
      });
    }

    static convertNotificationDates() {
      const dateElements = document.querySelectorAll(
        ".notificationPanel__item__date",
      );

      if (!dateElements.length) {
        return;
      }

      dateElements.forEach((element) => {
        const serverDate = element.getAttribute("data-server-date");
        if (serverDate) {
          const date = new Date(serverDate + " UTC");
          element.textContent = date.toLocaleString([], {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });
        }
      });
    }

    static preventScrollOnFocus() {
      if (!this.isMobile()) return;

      document.querySelectorAll("input, textarea").forEach((el) => {
        el.addEventListener("focus", () => {
          document.body.classList.add("noScroll");
        });
        el.addEventListener("blur", () => {
          document.body.classList.remove("noScroll");
        });
      });
    }

    static isMobile() {
      return /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    }

    static initImagePopup() {
      const popupImage = document.getElementById("popupImage");
      if (!popupImage) return;

      const imageWrapper = popupImage.closest(".popUp__imageWrapper");

      let isDragging = false;
      let didDrag = false;
      let startX = 0;
      let startY = 0;
      let scrollLeft = 0;
      let scrollTop = 0;

      document.addEventListener("click", function (e) {
        const target = e.target;

        if (target.classList.contains("js-openPopUp-img")) {
          popupImage.src = target.dataset.full;
          popupImage.classList.remove("zoomed");

          popupImage.style.width = "";
          popupImage.style.height = "";
          popupImage.style.transformOrigin = "";

          if (imageWrapper) {
            imageWrapper.scrollLeft = 0;
            imageWrapper.scrollTop = 0;
          }
        }
      });

      popupImage.addEventListener("click", function (e) {
        e.stopPropagation();

        if (didDrag) {
          didDrag = false;
          return;
        }

        const isZoomed = popupImage.classList.toggle("zoomed");

        if (isZoomed && imageWrapper) {
          setTimeout(() => {
            const naturalWidth = popupImage.naturalWidth;
            const naturalHeight = popupImage.naturalHeight;

            const scale = 1.5;
            popupImage.style.width = `${naturalWidth * scale}px`;
            popupImage.style.height = "auto";

            const rect = popupImage.getBoundingClientRect();
            const wrapperRect = imageWrapper.getBoundingClientRect();

            const clickX = e.clientX - wrapperRect.left;
            const clickY = e.clientY - wrapperRect.top;

            const scrollToX =
              (clickX / wrapperRect.width) * popupImage.clientWidth -
              imageWrapper.clientWidth / 2;
            const scrollToY =
              (clickY / wrapperRect.height) * popupImage.clientHeight -
              imageWrapper.clientHeight / 2;

            imageWrapper.scrollTo({
              left: scrollToX,
              top: scrollToY,
              behavior: "smooth",
            });
          }, 30);
        } else {
          popupImage.style.width = "";
          popupImage.style.height = "";
        }
      });

      // Drag-to-scroll
      imageWrapper.addEventListener("mousedown", function (e) {
        if (!popupImage.classList.contains("zoomed")) return;

        isDragging = true;
        didDrag = false;
        startX = e.pageX - imageWrapper.offsetLeft;
        startY = e.pageY - imageWrapper.offsetTop;
        scrollLeft = imageWrapper.scrollLeft;
        scrollTop = imageWrapper.scrollTop;
        popupImage.classList.add("dragging");
        e.preventDefault();
      });

      imageWrapper.addEventListener("mouseup", () => {
        isDragging = false;
        setTimeout(() => (didDrag = false), 0);
        popupImage.classList.remove("dragging");
      });

      imageWrapper.addEventListener("mousemove", function (e) {
        if (!isDragging) return;

        const x = e.pageX - imageWrapper.offsetLeft;
        const y = e.pageY - imageWrapper.offsetTop;
        const walkX = x - startX;
        const walkY = y - startY;

        if (Math.abs(walkX) > 5 || Math.abs(walkY) > 5) {
          didDrag = true;
        }

        imageWrapper.scrollLeft = scrollLeft - walkX;
        imageWrapper.scrollTop = scrollTop - walkY;
      });
    }

    static fakeFlagSelect() {
      const selects = document.querySelectorAll(".js-fakeSelectFlagWrapper");

      if (selects.length > 0) {
        selects.forEach((select) => {
          const input = select.querySelector(".js-fakeSelectFlagInput");
          const options = select.querySelector(".js-fakeSelectFlagOptions");

          const inputWrapper = input.closest(".js-fakeSelectFlagWrapper");
          let flagImgWrapper = inputWrapper.querySelector(
            ".js-fakeSelectFlagImg",
          );
          let flagImg = flagImgWrapper.querySelector("img");

          input.style.paddingLeft = "40px";
          inputWrapper.style.position = "relative";

          input.addEventListener("click", () => {
            const isVisible = options.style.display === "flex";

            document
              .querySelectorAll(".js-fakeSelectFlagOptions")
              .forEach((opt) => {
                opt.style.display = "none";
              });

            options.style.display = isVisible ? "none" : "flex";
          });

          options.addEventListener("click", (event) => {
            const li = event.target.closest("li");
            if (li) {
              const selectedFlag = li.dataset.flag || "";
              const img = li.querySelector("img");

              input.value = selectedFlag;

              if (img && flagImg) {
                flagImg.src = img.src;
                flagImg.alt = img.alt;
              }

              options.style.display = "none";
              input.dispatchEvent(new Event("input"));
            }
          });

          document.addEventListener("click", (event) => {
            if (!select.contains(event.target)) {
              options.style.display = "none";
            }
          });
        });
      }
    }

    static closeForumBanner() {
      const banner = document.querySelector(".js-forumBanner");
      const closeBtn = document.querySelector(".js-closeBanner");

      if (!banner) return;

      if (closeBtn) {
        closeBtn.addEventListener("click", () => {
          banner.style.display = "none";
          setCookie("forumBannerClosed", "1", 30 * 24 * 60 * 60);
        });
      }
    }

    static toggleMobileMenu() {
      const burger = document.getElementById("headerBurger");
      const mobileMenu = document.getElementById("headerMobileMenu");
      const closeBtn = document.getElementById("headerMobileMenuClose");
      const overlay = document.getElementById("headerMobileMenuOverlay");

      if (!burger || !mobileMenu) return;

      const openMenu = () => {
        mobileMenu.classList.add("active");
        burger.classList.add("active");
        document.body.style.overflow = "hidden";
      };

      const closeMenu = () => {
        mobileMenu.classList.remove("active");
        burger.classList.remove("active");
        document.body.style.overflow = "";
      };

      burger.addEventListener("click", openMenu);
      if (closeBtn) closeBtn.addEventListener("click", closeMenu);
      if (overlay) overlay.addEventListener("click", closeMenu);

      const menuLinks = mobileMenu.querySelectorAll(
        ".header__mobileMenu__list a",
      );
      menuLinks.forEach((link) => {
        link.addEventListener("click", () => {
          setTimeout(closeMenu, 300);
        });
      });
    }

    static captureUtmMetrics() {
      const urlParams = new URLSearchParams(window.location.search);
      const utms = [
        "utm_source",
        "utm_medium",
        "utm_campaign",
        "utm_content",
        "utm_term",
      ];
      const duration = 30 * 24 * 60 * 60;

      utms.forEach((utm) => {
        if (urlParams.has(utm)) {
          const value = urlParams.get(utm);
          setCookie(`panterrea_${utm}`, value, duration);
        }
      });
    }

    static init() {
      this.checkNotificationFromCookies();
      this.togglePass();
      this.toggleStatusAd();
      this.showUserPhone();
      this.shareUrl();
      this.toggleFavorites();
      this.fakeSelect();
      this.popUp();
      this.symbolCount();
      this.deleteAd();
      this.toggleNotificationPanel();
      this.deleteAllNotification();
      this.toggleEmailNotification();
      this.convertNotificationDates();
      /*this.preventScrollOnFocus();*/
      this.isMobile();
      this.initImagePopup();
      this.fakeFlagSelect();
      this.closeForumBanner();
      this.toggleMobileMenu();
      this.captureUtmMetrics();
    }
  }

  Panterrea.init();
});

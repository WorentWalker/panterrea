document.addEventListener("DOMContentLoaded", function () {

  /* ── Composer helpers ── */
  const composerWrap = document.getElementById("forumComposer");

  function focusComposer() {
    composerWrap?.classList.add("is-open");
    if (quill) { quill.focus(); }
  }

  /* ── Login popup ── */
  const loginPopup = document.getElementById("forumLoginPopup");

  function openLoginPopup() {
    if (!loginPopup) return;
    loginPopup.hidden = false;
    document.body.style.overflow = "hidden";
    loginPopup.querySelector("input")?.focus();
  }

  function closeLoginPopup() {
    if (!loginPopup) return;
    loginPopup.hidden = true;
    document.body.style.overflow = "";
  }

  document.querySelectorAll(".js-forumLoginPopupClose").forEach((el) => {
    el.addEventListener("click", closeLoginPopup);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && loginPopup && !loginPopup.hidden) {
      closeLoginPopup();
    }
  });

  /* ── Multi-select category chips ── */
  function syncCatHiddenInputs() {
    const container = document.getElementById("composerCatInputs");
    if (!container) return;
    container.innerHTML = "";
    document.querySelectorAll(".js-forumCatChip.is-active").forEach((chip) => {
      const inp = document.createElement("input");
      inp.type = "hidden";
      inp.name = "post_category_ids[]";
      inp.value = chip.dataset.catId || "";
      container.appendChild(inp);
    });
  }

  function getSelectedCatIds() {
    return Array.from(document.querySelectorAll(".js-forumCatChip.is-active"))
      .map((c) => c.dataset.catId);
  }

  const closeIconSVG = `<span class="forum__composer__catChip__x" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M16.6667 8.33333C16.6667 12.9358 12.9358 16.6667 8.33333 16.6667C3.73083 16.6667 0 12.9358 0 8.33333C0 3.73083 3.73083 0 8.33333 0C12.9358 0 16.6667 3.73083 16.6667 8.33333ZM5.80833 5.80833C5.92552 5.69129 6.08437 5.62555 6.25 5.62555C6.41563 5.62555 6.57448 5.69129 6.69167 5.80833L8.33333 7.45L9.975 5.80833C10.0935 5.69793 10.2502 5.63783 10.4121 5.64069C10.574 5.64354 10.7285 5.70914 10.843 5.82365C10.9575 5.93816 11.0231 6.09265 11.026 6.25456C11.0288 6.41648 10.9687 6.57319 10.8583 6.69167L9.21667 8.33333L10.8583 9.975C10.9687 10.0935 11.0288 10.2502 11.026 10.4121C11.0231 10.574 10.9575 10.7285 10.843 10.843C10.7285 10.9575 10.574 11.0231 10.4121 11.026C10.2502 11.0288 10.0935 10.9687 9.975 10.8583L8.33333 9.21667L6.69167 10.8583C6.57319 10.9687 6.41648 11.0288 6.25456 11.026C6.09265 11.0231 5.93816 10.9575 5.82365 10.843C5.70914 10.7285 5.64354 10.574 5.64069 10.4121C5.63783 10.2502 5.69793 10.0935 5.80833 9.975L7.45 8.33333L5.80833 6.69167C5.69129 6.57448 5.62555 6.41563 5.62555 6.25C5.62555 6.08437 5.69129 5.92552 5.80833 5.80833Z" fill="#116262"/></svg></span>`;

  function updateChipIcon(chip) {
    const existing = chip.querySelector(".forum__composer__catChip__x");
    if (chip.classList.contains("is-active")) {
      if (!existing) chip.insertAdjacentHTML("beforeend", closeIconSVG);
    } else {
      if (existing) existing.remove();
    }
  }

  document.querySelectorAll(".js-forumCatChip").forEach((chip) => {
    chip.addEventListener("click", function () {
      chip.classList.toggle("is-active");
      chip.setAttribute("aria-pressed", chip.classList.contains("is-active") ? "true" : "false");
      updateChipIcon(chip);
      syncCatHiddenInputs();
    });
  });

  /* ── Guest draft — localStorage ── */
  const DRAFT_KEY = "panterrea_forum_draft";
  const COMMENT_DRAFT_PREFIX = "panterrea_comment_draft_";

  function saveDraft(content, cats) {
    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify({ content, cats }));
    } catch (_) {}
  }

  function loadDraft() {
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (_) { return null; }
  }

  function clearDraft() {
    try { localStorage.removeItem(DRAFT_KEY); } catch (_) {}
  }

  /* ── Comment drafts (per post) ── */
  function saveCommentDraft(postId, text) {
    try {
      localStorage.setItem(COMMENT_DRAFT_PREFIX + postId, text);
    } catch (_) {}
  }

  function loadCommentDraft(postId) {
    try {
      return localStorage.getItem(COMMENT_DRAFT_PREFIX + postId) || null;
    } catch (_) { return null; }
  }

  function clearCommentDraft(postId) {
    try { localStorage.removeItem(COMMENT_DRAFT_PREFIX + postId); } catch (_) {}
  }

  function restoreDraft(draft) {
    if (!draft) return;
    if (quill && draft.content) {
      quill.clipboard.dangerouslyPasteHTML(draft.content);
    }
    if (Array.isArray(draft.cats)) {
      document.querySelectorAll(".js-forumCatChip").forEach((chip) => {
        if (draft.cats.includes(chip.dataset.catId)) {
          chip.classList.add("is-active");
          chip.setAttribute("aria-pressed", "true");
          updateChipIcon(chip);
        }
      });
      syncCatHiddenInputs();
    }
    clearDraft();
  }

  /* ── Quill init ── */
  const quillContainer = document.querySelector("#quillEditor");
  let quill;

  if (quillContainer) {
    quill = new Quill("#quillEditor", {
      theme: "snow",
      placeholder: getTranslatedText("forum_placeholder"),
      modules: {
        toolbar: [
          ["bold", "italic", "underline"],
          ["link"],
          [{ list: "ordered" }, { list: "bullet" }],
        ],
      },
    });
  }

  /*const quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: getTranslation('forum_placeholder'),
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['link'],
                [{ list: 'ordered' }, { list: 'bullet' }]
            ]
        }
    });*/

  const form = document.querySelector("#formForum");
  const formListPost = document.querySelector("#infiniteScrollForum");
  if (!formListPost) return;

  const uploadButton = document.querySelector(".btn__forumUpload");
  const fileInput = document.getElementById("mediaUploadInput");
  const previewContainer = document.getElementById("previewContainer");

  const maxFiles = 10;
  let filesArray = [];

  if (uploadButton && fileInput) {
  uploadButton.addEventListener("click", () => {
    fileInput.click();
  });

  fileInput.addEventListener("change", (event) => {
    const selectedFiles = Array.from(event.target.files);

    selectedFiles.forEach((file) => {
      if (filesArray.length >= maxFiles) return;

      const isImage = file.type.startsWith("image/");
      const isVideo = file.type.startsWith("video/");

      const imageMaxSize = 5 * 1024 * 1024;
      const videoMaxSize = 120 * 1024 * 1024;

      if (isImage && file.size > imageMaxSize) {
        MessageSystem.showMessage(
          "warning",
          getTranslatedText("file_size_limit")
        );
        /*MessageSystem.showMessage('warning', getTranslation('file_size_limit'));*/
        return;
      }

      if (isVideo && file.size > videoMaxSize) {
        MessageSystem.showMessage(
          "warning",
          getTranslatedText("video_size_limit")
        );
        /*MessageSystem.showMessage('warning', getTranslation('video_size_limit'));*/
        return;
      }

      const supported = ["image/jpeg", "image/png", "image/webp", "video/mp4"];
      if (!supported.includes(file.type)) {
        MessageSystem.showMessage(
          "warning",
          getTranslatedText("unsupported_file_format")
        );
        /*MessageSystem.showMessage('warning', getTranslation('unsupported_file_format'));*/
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        filesArray.push({
          file,
          preview: e.target.result,
          name: file.name,
          type: file.type,
        });
        renderPreview();
      };
      reader.readAsDataURL(file);
    });

    event.target.value = "";
  });
  }

  function renderPreview() {
    if (!previewContainer) return;
    previewContainer.innerHTML = "";

    filesArray.forEach((item, index) => {
      const wrapper = document.createElement("div");
      wrapper.classList.add("preview-item", "filled");

      let content = "";
      if (
        item.type === "image" ||
        (item.type && item.type.startsWith("image/"))
      ) {
        content = `<img src="${item.preview}" alt="${item.name}" />`;
      } else if (
        item.type === "video" ||
        (item.type && item.type.startsWith("video/"))
      ) {
        content = `<video src="${item.preview}" controls muted></video>`;
      } else {
        content = `<div class="unknown-file">Файл</div>`;
      }

      wrapper.innerHTML = `
            ${content}
            <div class="remove-btn" data-index="${index}"></div>
        `;

      wrapper.querySelector(".remove-btn").addEventListener("click", () => {
        filesArray.splice(index, 1);
        renderPreview();
      });

      previewContainer.appendChild(wrapper);
    });
  }

  if (form) {
  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const quillHtml = document.querySelector(".ql-editor")?.innerHTML || "";
    const textContent =
      document.querySelector(".ql-editor")?.innerText.trim() || "";

    const textareaWrapper = document.querySelector(".input__formTextarea");
    const errorElement = textareaWrapper.querySelector(".error.caption");

    textareaWrapper.classList.remove("notValid");
    errorElement.textContent = "";

    if (!textContent) {
      textareaWrapper.classList.add("notValid");
      errorElement.textContent = "Обов'язкове поле.";
      return;
    }

    /* ── Guest: save draft & open login popup ── */
    const isLoggedIn = form.dataset.loggedIn === "1";
    if (!isLoggedIn) {
      saveDraft(quillHtml, getSelectedCatIds());
      openLoginPopup();
      return;
    }

    syncCatHiddenInputs();
    document.getElementById("postContent").value = quillHtml;

    const formData = new FormData(form);
    formData.append("security", forumObject.forum_nonce);

    filesArray.forEach((item, index) => {
      if (item.file) {
        formData.append(`files[${index}]`, item.file);
      } else {
        formData.append(`existing[${index}]`, item.preview);
      }
    });

    toggleLoadingCursor(true);

    if (isEditing) {
      formData.append("action", "forum_edit_post");
      formData.append("post_id", editingPostId);

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            setMessageCookies("success", getTranslatedText("edit_success"), 60);
            /*setMessageCookies('success', getTranslation('edit_success'), 60);*/
            location.reload();
          } else {
            MessageSystem.showMessage(
              "error",
              data.data?.message || getTranslatedText("error_generic")
            );
            /*MessageSystem.showMessage('error', data.data?.message || getTranslation('error_generic'));*/
          }
        })
        .catch(() => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage('error', getTranslation('server_error'));*/
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    } else {
      formData.append("action", "forum_submit_post");

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            form.reset();
            filesArray = [];
            renderPreview();
            setMessageCookies("success", getTranslatedText("post_success"), 60);
            /*setMessageCookies('success', getTranslation('post_success'), 60);*/
            location.reload();
          } else {
            MessageSystem.showMessage(
              "error",
              data.data?.message || getTranslatedText("error_generic")
            );
            /*MessageSystem.showMessage('error', data.data?.message || getTranslation('error_generic'));*/
          }
        })
        .catch(() => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage('error', getTranslation('server_error'));*/
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    }
  });
  }

  if (previewContainer) {
    renderPreview();
  }

  let isSearching = false;
  let currentPage = 1;
  let hasMorePosts = true;
  let forumSort = "all";
  let isLoading = false;

  const postsContainerInit = document.querySelector("#infiniteScrollForum");
  if (postsContainerInit) {
    if (postsContainerInit.dataset.forumSort) {
      forumSort = postsContainerInit.dataset.forumSort;
    } else if (postsContainerInit.dataset.onlyMy === "1") {
      forumSort = "mine";
    }
  }

  function getSelectedForumCategoryIds() {
    const allBox = document.getElementById("forum-cat-all");
    if (allBox && allBox.checked) {
      return [];
    }
    return Array.from(
      document.querySelectorAll(".js-forumCategoryFilter:checked")
    ).map((el) => String(el.value));
  }

  function syncForumCategoryAllState() {
    const allBox = document.getElementById("forum-cat-all");
    if (!allBox) return;
    const anySpec = document.querySelector(
      ".forum__sidebar .js-forumCategoryFilter:checked"
    );
    if (!anySpec) {
      allBox.checked = true;
    }
  }

  function syncForumSortDataAttr() {
    const el = document.querySelector("#infiniteScrollForum");
    if (!el) return;
    el.dataset.forumSort = forumSort;
    el.dataset.onlyMy = forumSort === "mine" ? "1" : "0";
  }

  function forumUiStrings() {
    const o = typeof forumObject !== "undefined" ? forumObject : {};
    return {
      showComments: o.str_show_all_comments || getTranslatedText("show_comment"),
      hideComments: o.str_hide_comments || getTranslatedText("hide_comment"),
      showAll: o.str_show_all || "Показати всі",
      hide: o.str_hide || "Сховати",
    };
  }

  function initForumCommentClamps(root) {
    const isEl =
      root &&
      (typeof Node !== "undefined"
        ? root.nodeType === Node.ELEMENT_NODE
        : root.nodeType === 1);
    const el = isEl ? root : document;
    const nodes = el.querySelectorAll(
      ".forum__itemPost__comment__body.is-forumCommentClampable"
    );

    nodes.forEach((body) => {
      body.classList.remove("is-forumCommentExpanded");
      const block = body.closest(".forum__itemPost__comment__textBlock");
      const btn = block?.querySelector(".js-forumCommentToggle");
      if (!btn || !block) return;

      if (body.scrollHeight <= body.clientHeight + 2) {
        btn.hidden = true;
      } else {
        btn.hidden = false;
        btn.textContent = btn.dataset.show || forumUiStrings().showAll;
      }
    });
  }

  function scheduleInitForumUi(root) {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => initForumCommentClamps(root));
    });
  }

  function forumRefreshTopLevelCommentsUi(postEl) {
    const commentsRoot = postEl.querySelector(".forum__itemPost__comments");
    const block = postEl.querySelector(".forum__itemPost__commentsBlock");
    if (!commentsRoot || !block) return;
    const tops = commentsRoot.querySelectorAll(
      ":scope > .forum__itemPost__comment"
    );
    let btn = block.querySelector(".js-toggleComments");
    tops.forEach((el, i) => {
      el.classList.remove("shown-comment");
      if (i >= 2) {
        el.classList.add("hidden-comment");
      } else {
        el.classList.remove("hidden-comment");
      }
    });
    if (tops.length > 2) {
      if (!btn) {
        btn = document.createElement("button");
        btn.type = "button";
        btn.className =
          "btn btn__transparent forum__itemPost__showAllComments js-toggleComments subtitle2";
        btn.textContent = forumUiStrings().showComments;
        block.appendChild(btn);
      }
    } else if (btn) {
      btn.remove();
    }
    scheduleInitForumUi(postEl);
  }

  function appendForumCats(formData) {
    getSelectedForumCategoryIds().forEach((id) => {
      formData.append("forum_cats[]", id);
    });
  }

  function syncForumCatsDataAttr() {
    const el = document.querySelector("#infiniteScrollForum");
    if (!el) return;
    el.dataset.forumCats = getSelectedForumCategoryIds().join(",");
  }

  function runForumSearch(query) {
    isSearching = true;
    syncLoadMoreBtn();
    toggleLoadingCursor(true);
    const formData = new FormData();
    formData.append("action", "search_forum_posts");
    formData.append("security", forumObject.forum_nonce);
    formData.append("query", query);
    formData.append("forum_sort", forumSort);
    formData.append("only_my", forumSort === "mine" ? "1" : "0");
    appendForumCats(formData);

    fetch(mainObject.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const container = document.querySelector("#infiniteScrollForum");
          if (container) container.innerHTML = data.data.html;
          scheduleInitForumUi(container);
        } else {
          MessageSystem.showMessage(
            "error",
            getTranslatedText("error_generic")
          );
        }
      })
      .catch(() => {
        MessageSystem.showMessage("error", getTranslatedText("server_error"));
      })
      .finally(() => {
        toggleLoadingCursor(false);
      });
  }

  function refreshForumFeed() {
    const si = document.getElementById("searchInputForum");
    const query = si ? si.value.trim() : "";
    syncForumCatsDataAttr();
    syncForumSortDataAttr();
    if (query !== "") {
      runForumSearch(query);
      return;
    }
    isSearching = false;
    currentPage = 1;
    hasMorePosts = true;
    syncLoadMoreBtn();
    loadForumPosts({ reset: true });
  }

  // If the server pre-rendered page 1 (SEO/no-JS fallback), continue from the next page
  (function initFromServerRenderedState() {
    const postsContainer = document.querySelector("#infiniteScrollForum");
    if (!postsContainer) return;
    const serverPage = parseInt(postsContainer.dataset.currentPage || "0", 10);
    const maxPages = parseInt(postsContainer.dataset.maxPages || "0", 10);
    if (maxPages === 0) {
      hasMorePosts = false;
      currentPage = serverPage > 0 ? serverPage + 1 : 1;
      return;
    }
    if (serverPage > 0) {
      currentPage = serverPage + 1;
      if (serverPage >= maxPages) {
        hasMorePosts = false;
      }
    }
  })();

  function loadForumPosts({ reset = false } = {}) {
    const postsContainer = document.querySelector("#infiniteScrollForum");
    if (!postsContainer || isLoading || (!hasMorePosts && !reset)) return;

    isLoading = true;

    const formData = new FormData();
    formData.append("action", "load_forum_posts");
    formData.append("security", forumObject.forum_nonce);
    formData.append("page", currentPage);
    formData.append("forum_sort", forumSort);
    formData.append("only_my", forumSort === "mine" ? "1" : "0");
    appendForumCats(formData);

    fetch(mainObject.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((html) => {
        if (reset) {
          postsContainer.innerHTML = "";
          currentPage = 1;
          hasMorePosts = true;
        }

        if (!html.trim()) {
          hasMorePosts = false;
          syncLoadMoreBtn();
          return;
        }

        currentPage++;

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;
        Array.from(tempDiv.children).forEach((post) => {
          postsContainer.appendChild(post);
          scheduleInitForumUi(post);
        });

        syncLoadMoreBtn();
      })
      .catch((err) => {
        console.error("Помилка завантаження постів:", err);
      })
      .finally(() => {
        isLoading = false;
        if (loadMoreBtn) {
          loadMoreBtn.classList.remove("is-loading");
          loadMoreBtn.disabled = false;
        }
      });
  }

  const loadMoreBtn = document.getElementById("forumLoadMoreBtn");

  function syncLoadMoreBtn() {
    if (!loadMoreBtn) return;
    if (hasMorePosts && !isSearching) {
      loadMoreBtn.style.display = "";
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", () => {
      if (isLoading || !hasMorePosts) return;
      loadMoreBtn.classList.add("is-loading");
      loadMoreBtn.disabled = true;
      loadForumPosts();
    });
  }

  const postsContainerCheck = document.querySelector("#infiniteScrollForum");
  if (postsContainerCheck && postsContainerCheck.children.length === 0) {
    loadForumPosts();
  }

  syncLoadMoreBtn();

  document.querySelectorAll('input[name="forum_feed_sort"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      if (!radio.checked) return;
      forumSort = radio.value;
      syncForumSortDataAttr();
      refreshForumFeed();
    });
  });

  const forumCatAll = document.getElementById("forum-cat-all");
  if (forumCatAll) {
    forumCatAll.addEventListener("change", () => {
      if (forumCatAll.checked) {
        document
          .querySelectorAll(".forum__sidebar .js-forumCategoryFilter")
          .forEach((cb) => {
            cb.checked = false;
          });
        refreshForumFeed();
      } else {
        syncForumCategoryAllState();
        refreshForumFeed();
      }
    });
  }

  document.querySelectorAll(".js-forumCategoryFilter").forEach((cb) => {
    cb.addEventListener("change", () => {
      const allBox = document.getElementById("forum-cat-all");
      if (cb.checked && allBox) {
        allBox.checked = false;
      }
      if (!cb.checked) {
        syncForumCategoryAllState();
      }
      refreshForumFeed();
    });
  });

  document.querySelectorAll(".js-forumCatsToggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const extras = btn.previousElementSibling;
      if (!extras || !extras.classList.contains("forum__sidebar__categoryExtras")) {
        return;
      }
      const collapsed = extras.classList.toggle("is-collapsed");
      const showLab = btn.dataset.labelShow || forumUiStrings().showAll;
      const hideLab = btn.dataset.labelHide || forumUiStrings().hide;
      btn.textContent = collapsed ? showLab : hideLab;
      btn.setAttribute("aria-expanded", collapsed ? "false" : "true");
    });
  });

  scheduleInitForumUi(document);

  document.querySelectorAll(".js-forumScrollToComposer").forEach((btn) => {
    btn.addEventListener("click", () => {
      const el = document.getElementById("forumComposer");
      if (el) {
        el.scrollIntoView({ behavior: "smooth", block: "start" });
      }
      focusComposer();
    });
  });

  document
    .querySelectorAll(".forum__sidebar__searchSubmit")
    .forEach((btn) => {
      btn.addEventListener("click", () => {
        const si = document.getElementById("searchInputForum");
        if (!si) return;
        const query = si.value.trim();
        if (query === "") return;
        runForumSearch(query);
      });
    });

  const searchInput = document.getElementById("searchInputForum");

  if (searchInput) {
    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const query = this.value.trim();
        if (query === "") return;
        runForumSearch(query);
      }
    });

    searchInput.addEventListener("input", function () {
      const query = this.value.trim();
      if (query === "") {
        refreshForumFeed();
      }
    });
  }

  document.addEventListener("click", function (event) {
    const allOptionsLists = document.querySelectorAll(
      ".forum__itemPost__optionsList"
    );
    const forumOptions = event.target.closest(".js-forumOptions");

    if (forumOptions) {
      const optionsList = forumOptions.querySelector(
        ".forum__itemPost__optionsList"
      );

      if (optionsList) {
        allOptionsLists.forEach((list) => {
          if (list !== optionsList) {
            list.classList.add("hidden");
          }
        });

        optionsList.classList.toggle("hidden");
      }
    } else {
      allOptionsLists.forEach((list) => list.classList.add("hidden"));
    }
  });

  let deletePostId = null;

  document
    .getElementById("infiniteScrollForum")
    ?.addEventListener("click", function (event) {
      const button = event.target.closest(
        ".js-openPopUp[data-popUp='deleteForumItem']"
      );
      if (!button) return;

      const postWrapper = button.closest(".forum__itemPost");
      if (!postWrapper) return;

      deletePostId = postWrapper.getAttribute("data-post-id");
    });

  const deleteForumItemButton = document.getElementById(
    "deleteForumItemButton"
  );
  if (deleteForumItemButton) {
    deleteForumItemButton.addEventListener("click", function () {
      if (!deletePostId) return;

      const formData = new FormData();
      formData.append("action", "delete_forum_post");
      formData.append("security", forumObject.forum_nonce);
      formData.append("post_id", deletePostId);

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const postElement = document.querySelector(
              `.forum__itemPost[data-post-id="${deletePostId}"]`
            );
            if (postElement) postElement.remove();

            document.querySelector("#deleteForumItem .js-closePopUp").click();

            MessageSystem.showMessage("success", data.data.message);
          } else {
            MessageSystem.showMessage(
              "error",
              getTranslatedText("delete_error")
            );
            /*MessageSystem.showMessage("error", getTranslation('delete_error', 'Помилка видалення.'));*/
          }
        })
        .catch(() => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage("error", getTranslation('server_error', 'Помилка сервера. Спробуйте пізніше.'));*/
        });
    });
  }

  let isEditing = false;
  let editingPostId = null;

  const editButtonsWrapper = document.querySelector(".form__editBtn");
  const editSubmitButton = document.querySelector(".js-forumEdit");
  const cancelEditButton = document.querySelector(".js-forumEditCancel");
  const publishButton = form ? form.querySelector(".js-forumPublish") : null;

  function resetForm() {
    if (!form) return;
    form.reset();
    if (quill) { quill.setContents([]); }
    filesArray = [];
    editingPostId = null;
    isEditing = false;
    renderPreview();
    if (editButtonsWrapper) editButtonsWrapper.classList.add("hidden");
    if (publishButton) publishButton.classList.remove("hidden");
    document.querySelectorAll(".js-forumCatChip").forEach((c) => {
      c.classList.remove("is-active");
      c.setAttribute("aria-pressed", "false");
      updateChipIcon(c);
    });
    syncCatHiddenInputs();
  }

  /* ── Restore draft on page load (logged-in users) ── */
  (function checkAndRestoreDraft() {
    const isLoggedIn = form && form.dataset.loggedIn === "1";
    if (!isLoggedIn) return;
    const draft = loadDraft();
    if (!draft) return;
    // Defer until Quill is ready
    const tryRestore = (attempts) => {
      if (quill) {
        restoreDraft(draft);
        focusComposer();
      } else if (attempts > 0) {
        setTimeout(() => tryRestore(attempts - 1), 200);
      }
    };
    tryRestore(10);
  })();

  if (cancelEditButton) {
    cancelEditButton.addEventListener("click", (e) => {
      e.preventDefault();
      resetForm();
    });
  }

  document.addEventListener("click", function (e) {
    const editBtn = e.target.closest(".js-forumEditTrigger");
    if (!editBtn) return;

    e.preventDefault();
    const post = editBtn.closest(".forum__itemPost");
    if (!post) return;

    const postId = post.dataset.postId;
    const contentHTML =
      post.querySelector(".forum__itemPost__content")?.innerHTML || "";
    const mediaJson = post.querySelector(".forum__itemPost__media")?.dataset
      .media;

    isEditing = true;
    editingPostId = postId;
    if (quill) {
      quill.setContents(quill.clipboard.convert(contentHTML));
    }

    filesArray = [];

    if (mediaJson) {
      try {
        const media = JSON.parse(mediaJson);
        media.forEach((item) => {
          filesArray.push({
            file: null,
            preview: item.url,
            name: item.url.split("/").pop(),
            type: item.type,
          });
        });
      } catch (err) {
        console.error("Помилка JSON:", err);
      }
    }

    renderPreview();

    // Restore categories from post
    document.querySelectorAll(".js-forumCatChip").forEach((c) => {
      c.classList.remove("is-active");
      c.setAttribute("aria-pressed", "false");
      updateChipIcon(c);
    });
    const postCats = post.querySelectorAll(".forum__itemPost__cat");
    postCats.forEach((catEl) => {
      const catName = catEl.textContent.trim();
      document.querySelectorAll(".js-forumCatChip").forEach((chip) => {
        if (chip.dataset.catName === catName) {
          chip.classList.add("is-active");
          chip.setAttribute("aria-pressed", "true");
        }
      });
    });
    syncCatHiddenInputs();

    if (publishButton) publishButton.classList.add("hidden");
    if (editButtonsWrapper) editButtonsWrapper.classList.remove("hidden");
    focusComposer();
    if (form) {
      setTimeout(() => {
        form.scrollIntoView({ behavior: "smooth", block: "start" });
      }, 50);
    }
  });

  if (editSubmitButton && form) {
    editSubmitButton.addEventListener("click", () => {
      form.requestSubmit();
    });
  }

  /* ── Helper: submit a comment via AJAX ── */
  function submitComment(commentForm, commentText) {
    const postId = commentForm.dataset.postId;
    const inputWrapper = commentForm.querySelector(".input__form");
    const errorElement = inputWrapper ? inputWrapper.querySelector(".error") : null;

    if (inputWrapper) inputWrapper.classList.remove("notValid");
    if (errorElement) errorElement.textContent = "";

    if (!commentText) {
      if (inputWrapper) inputWrapper.classList.add("notValid");
      if (errorElement) errorElement.textContent = "Обов'язкове поле.";
      return;
    }

    const formData = new FormData();
    formData.append("action", "add_comment");
    formData.append("security", forumObject.forum_nonce);
    formData.append("post_id", postId);
    formData.append("comment", commentText);

    toggleLoadingCursor(true);

    fetch(mainObject.ajax_url, { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.data.comment_html) {
          const commentsContainer = commentForm
            .closest(".forum__itemPost")
            .querySelector(".forum__itemPost__comments");
          if (commentsContainer) {
            commentsContainer.insertAdjacentHTML("beforeend", data.data.comment_html);
            forumRefreshTopLevelCommentsUi(commentForm.closest(".forum__itemPost"));
          }
          commentForm.reset();
          clearCommentDraft(postId);
          MessageSystem.showMessage("success", getTranslatedText("comment_added"));
        } else {
          MessageSystem.showMessage("error", getTranslatedText("error_generic"));
        }
      })
      .catch(() => {
        MessageSystem.showMessage("error", getTranslatedText("server_error"));
      })
      .finally(() => {
        toggleLoadingCursor(false);
      });
  }

  document.addEventListener("submit", function (e) {
    const commentForm = e.target.closest(".js-forumCommentForm");
    if (!commentForm) return;
    e.preventDefault();

    const commentInput = commentForm.querySelector('input[name="comment"]');
    const commentText = commentInput ? commentInput.value.trim() : "";
    const postId = commentForm.dataset.postId;
    const isLoggedIn = commentForm.dataset.loggedIn === "1";

    /* ── Guest: save comment draft & open login popup ── */
    if (!isLoggedIn) {
      if (commentText) saveCommentDraft(postId, commentText);
      openLoginPopup();
      return;
    }

    submitComment(commentForm, commentText);
  });

  /* ── Cancel button ── */
  document.addEventListener("click", function (e) {
    const cancelBtn = e.target.closest(".js-forumCommentCancel");
    if (!cancelBtn) return;
    const commentForm = cancelBtn.closest(".js-forumCommentForm");
    if (!commentForm) return;
    commentForm.reset();
    const inputWrapper = commentForm.querySelector(".input__form");
    if (inputWrapper) inputWrapper.classList.remove("notValid");
  });

  /* ── On page load: restore comment draft if logged in ── */
  (function restoreCommentDrafts() {
    document.querySelectorAll(".js-forumCommentForm[data-logged-in='1']").forEach((form) => {
      const postId = form.dataset.postId;
      const draft = loadCommentDraft(postId);
      if (!draft) return;
      const input = form.querySelector('input[name="comment"]');
      if (input) {
        input.value = draft;
        clearCommentDraft(postId);
        /* Auto-submit after short delay so UI is ready */
        setTimeout(() => submitComment(form, draft), 400);
      }
    });
  })();

  document.addEventListener("click", function (e) {
    const replyBtn = e.target.closest(".js-replyComment");
    if (!replyBtn) return;

    const commentElement = replyBtn.closest(".forum__itemPost__comment");
    if (!commentElement) return;

    document.querySelectorAll(".form__forumReply").forEach((replyForm) => {
      replyForm.classList.add("hidden");
    });

    const replyForm = commentElement.querySelector(".form__forumReply");
    if (replyForm) {
      replyForm.classList.remove("hidden");
      replyForm.querySelector('input[name="commentReply"]').focus();
    }
  });

  document.addEventListener("click", function (e) {
    const cancelBtn = e.target.closest(".js-commentCancel");
    if (!cancelBtn) return;

    const replyForm = cancelBtn.closest(".form__forumReply");
    if (replyForm) {
      replyForm.classList.add("hidden");
    }
  });

  document.addEventListener("click", function (e) {
    const submitReplyBtn = e.target.closest(".js-commentReply");
    if (!submitReplyBtn) return;

    const submitReplyForm = submitReplyBtn.closest(".form__forumReply");
    const isEditing = submitReplyForm.dataset.editing === "true";
    const commentElement = submitReplyBtn.closest(".forum__itemPost__comment");
    const postId = submitReplyForm.closest("[data-post-id]").dataset.postId;
    const input = submitReplyForm.querySelector("input[name='commentReply']");
    const commentText = input.value.trim();
    const errorField = submitReplyForm.querySelector(".error");

    submitReplyForm.classList.remove("notValid");
    errorField.textContent = "";

    if (!commentText) {
      submitReplyForm.classList.add("notValid");
      errorField.textContent = "Обов'язкове поле.";
      /*errorField.textContent = getTranslation("validation.required");*/
      return;
    }

    const formData = new FormData();
    formData.append("security", forumObject.forum_nonce);
    formData.append("comment", commentText);

    if (isEditing) {
      formData.append("action", "edit_comment");
      formData.append("comment_id", submitReplyForm.dataset.commentId);
    } else {
      formData.append("action", "add_comment");
      formData.append("post_id", postId);
      formData.append("parent_id", commentElement.dataset.commentId);
    }

    toggleLoadingCursor(true);

    fetch(mainObject.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          if (isEditing) {
            const commentContent = commentElement.querySelector(
              ".forum__itemPost__comment__body"
            );
            if (commentContent) {
              commentContent.innerHTML = "";
              commentText.split("\n").forEach((line, i, arr) => {
                commentContent.appendChild(document.createTextNode(line));
                if (i < arr.length - 1) {
                  commentContent.appendChild(document.createElement("br"));
                }
              });
              scheduleInitForumUi(commentElement);
            }
            MessageSystem.showMessage(
              "success",
              getTranslatedText("comment_edit")
            );
            /*MessageSystem.showMessage("success", getTranslation("comment_edit", "Коментар оновлено"));*/
          } else {
            let repliesWrapper = commentElement.querySelector(
              ".forum__itemPost__comment__replies"
            );
            if (!repliesWrapper) {
              repliesWrapper = document.createElement("div");
              repliesWrapper.classList.add("forum__itemPost__comment__replies");
              commentElement.appendChild(repliesWrapper);
            }
            repliesWrapper.insertAdjacentHTML(
              "beforeend",
              data.data.comment_html
            );
            const inserted = repliesWrapper.lastElementChild;
            if (inserted) {
              scheduleInitForumUi(inserted);
            }
            MessageSystem.showMessage(
              "success",
              getTranslatedText("comment_added")
            );
            /*MessageSystem.showMessage("success", getTranslation("comment_added", "Коментар додано"));*/
          }

          input.value = "";
          submitReplyForm.classList.add("hidden");
          submitReplyForm.dataset.editing = "false";
          submitReplyBtn.textContent = getTranslatedText("reply");
          /*submitReplyBtn.textContent = getTranslation("reply", "Відповісти");*/
        } else {
          MessageSystem.showMessage(
            "error",
            data.data?.message || getTranslatedText("error_generic")
          );
          /*MessageSystem.showMessage("error", data.data?.message || getTranslation("error_generic"));*/
        }
      })
      .catch(() => {
        MessageSystem.showMessage("error", getTranslatedText("server_error"));
        /*MessageSystem.showMessage("error", getTranslation("server_error"));*/
      })
      .finally(() => {
        toggleLoadingCursor(false);
      });
  });

  let deleteCommentId = null;

  document.addEventListener("click", function (event) {
    const deleteBtn = event.target.closest(
      ".js-openPopUp[data-popUp='deleteForumComment']"
    );

    if (deleteBtn) {
      const commentWrapper = deleteBtn.closest(".forum__itemPost__comment");
      if (commentWrapper) {
        deleteCommentId = commentWrapper.dataset.commentId;
      }
    }
  });

  const deleteCommentButton = document.getElementById(
    "deleteForumCommentButton"
  );

  if (deleteCommentButton) {
    deleteCommentButton.addEventListener("click", function () {
      if (!deleteCommentId) return;

      const formData = new FormData();
      formData.append("action", "delete_forum_comment");
      formData.append("security", forumObject.forum_nonce);
      formData.append("comment_id", deleteCommentId);

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const commentElement = document.querySelector(
              `.forum__itemPost__comment[data-comment-id="${deleteCommentId}"]`
            );
            if (commentElement) commentElement.remove();
            document
              .querySelector("#deleteForumComment .js-closePopUp")
              ?.click();

            MessageSystem.showMessage("success", data.data.message);
          } else {
            MessageSystem.showMessage(
              "error",
              getTranslatedText("delete_error")
            );
            /*MessageSystem.showMessage("error", getTranslation('delete_error', 'Помилка видалення.'));*/
          }
        })
        .catch(() => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage("error", getTranslation('server_error', 'Помилка сервера. Спробуйте пізніше.'));*/
        });
    });
  }

  document.addEventListener("click", function (e) {
    const editBtn = e.target.closest(".js-editComment");
    if (!editBtn) return;

    const commentEl = editBtn.closest(".forum__itemPost__comment");
    const bodyEl = commentEl.querySelector(".forum__itemPost__comment__body");
    const commentText = bodyEl ? bodyEl.textContent.trim() : "";
    const form = commentEl.querySelector(".form__forumReply");
    const input = form.querySelector("input[name='commentReply']");

    input.value = commentText;
    form.classList.remove("hidden");
    form.dataset.editing = "true";
    form.dataset.commentId = commentEl.dataset.commentId;

    const submitBtn = form.querySelector(".js-commentReply");
    submitBtn.textContent = getTranslatedText("save");
    /*submitBtn.textContent = getTranslation("save", "Зберегти");*/
  });

  document.addEventListener("click", function (e) {
    const likeBtn = e.target.closest(".js-likes");
    if (!likeBtn) return;

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

    const postId = likeBtn.dataset.postId;

    const formData = new FormData();
    formData.append("action", "toggle_post_like");
    formData.append("post_id", postId);
    formData.append("security", forumObject.forum_nonce);

    fetch(mainObject.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          likeBtn.classList.toggle("active", data.data.liked);
          const likesCountElem = likeBtn.querySelector(".js-likes-count");
          if (likesCountElem) {
            likesCountElem.textContent =
              data.data.count > 0 ? data.data.count : "";
          }
        } else {
          MessageSystem.showMessage(
            "error",
            getTranslatedText("error_generic")
          );
          /*MessageSystem.showMessage('error', getTranslation('error_generic'));*/
        }
      })
      .catch(() => {
        MessageSystem.showMessage("error", getTranslatedText("server_error"));
        /*MessageSystem.showMessage('error', getTranslation('server_error'));*/
      });
  });

  document.addEventListener("click", function (e) {
    if (e.target.closest(".js-shareForum")) {
      const btn = e.target.closest(".js-shareForum");
      const postId = btn.dataset.postId;
      if (!postId) return;

      const baseUrl = window.location.origin + window.location.pathname;
      const shareUrl = `${baseUrl}?highlight_post=${postId}`;

      const textSpan = btn.querySelector('span');
      const originalText = textSpan ? textSpan.textContent : '';

      navigator.clipboard
        .writeText(shareUrl)
        .then(() => {
          // Add copied state
          btn.classList.add('copied');
          if (textSpan) {
            textSpan.textContent = 'Скопировано';
          }

          // Reset after 2 seconds
          setTimeout(() => {
            btn.classList.remove('copied');
            if (textSpan) {
              textSpan.textContent = originalText;
            }
          }, 2000);
        })
        .catch(() => {
          MessageSystem.showMessage(
            "warning",
            getTranslatedText("link_copy_error")
          );
          /*MessageSystem.showMessage('error', getTranslation('link_copy_error'));*/
        });
    }
  });

  document.addEventListener("click", function (e) {
    const scrollBtn = e.target.closest(".js-scrollToComments");
    if (scrollBtn) {
      const postEl = scrollBtn.closest(".forum__itemPost");
      const commentInput = postEl?.querySelector(".js-forumCommentForm input[name='comment']");
      if (commentInput) {
        commentInput.scrollIntoView({ behavior: "smooth", block: "center" });
        commentInput.focus();
      }
    }
  });

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".js-toggleComments");
    if (!btn) return;

    const postContainer = btn.closest(".forum__itemPost");
    const hidden = postContainer.querySelectorAll(".hidden-comment");
    const shown = postContainer.querySelectorAll(".shown-comment");
    const { showComments, hideComments } = forumUiStrings();

    if (hidden.length > 0) {
      hidden.forEach((el) => {
        el.classList.remove("hidden-comment");
        el.classList.add("shown-comment");
      });
      btn.classList.add("expanded");
      btn.textContent = hideComments;
    } else {
      shown.forEach((el) => {
        el.classList.remove("shown-comment");
        el.classList.add("hidden-comment");
      });
      btn.classList.remove("expanded");
      btn.textContent = showComments;
    }
    scheduleInitForumUi(postContainer);
  });

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".js-forumCommentToggle");
    if (!btn || btn.hidden) return;
    const block = btn.closest(".forum__itemPost__comment__textBlock");
    const body = block?.querySelector(".forum__itemPost__comment__body");
    if (!body) return;
    const expanded = body.classList.toggle("is-forumCommentExpanded");
    btn.textContent = expanded
      ? btn.dataset.hide || forumUiStrings().hide
      : btn.dataset.show || forumUiStrings().showAll;
  });
});

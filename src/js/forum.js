document.addEventListener("DOMContentLoaded", function () {
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
  if (!form && !formListPost) return;

  const uploadButton = document.querySelector(".btn__forumUpload");
  const fileInput = document.getElementById("mediaUploadInput");
  const previewContainer = document.getElementById("previewContainer");

  const maxFiles = 10;
  let filesArray = [];

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

  function renderPreview() {
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
      /*errorElement.textContent = getTranslation('validation.required');*/
      return;
    }

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

  renderPreview();

  let isSearching = false;
  let currentPage = 1;
  let hasMorePosts = true;
  let showOnlyMine = false;
  let isLoading = false;

  // If the server pre-rendered page 1 (SEO/no-JS fallback), continue from the next page
  (function initFromServerRenderedState() {
    const postsContainer = document.querySelector("#infiniteScrollForum");
    if (!postsContainer) return;
    const serverPage = parseInt(postsContainer.dataset.currentPage || "0", 10);
    const maxPages = parseInt(postsContainer.dataset.maxPages || "0", 10);
    if (serverPage > 0) {
      currentPage = serverPage + 1;
      if (maxPages > 0 && serverPage >= maxPages) {
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
    formData.append("only_my", showOnlyMine ? "1" : "0");

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
          return;
        }

        currentPage++;

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;
        Array.from(tempDiv.children).forEach((post) => {
          postsContainer.appendChild(post);
        });
      })
      .catch((err) => {
        console.error("Помилка завантаження постів:", err);
      })
      .finally(() => {
        isLoading = false;
      });
  }

  function initForumInfiniteScroll() {
    window.addEventListener("scroll", () => {
      if (isSearching || isLoading || !hasMorePosts) return;

      const postsContainer = document.querySelector("#infiniteScrollForum");
      if (!postsContainer) return;

      const scrollPosition = window.innerHeight + window.scrollY;
      const triggerPosition =
        postsContainer.offsetTop + postsContainer.offsetHeight - 300;

      if (scrollPosition >= triggerPosition) {
        loadForumPosts();
      }
    });

    const postsContainer = document.querySelector("#infiniteScrollForum");
    if (postsContainer && postsContainer.children.length === 0) {
      loadForumPosts();
    }
  }

  initForumInfiniteScroll();

  const toggleBtn = document.querySelector(".js-toggleAll");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      showOnlyMine = !showOnlyMine;
      toggleBtn.innerText = showOnlyMine
        ? getTranslatedText("show_all")
        : getTranslatedText("show_my");
      /*toggleBtn.innerText = showOnlyMine ? getTranslation('show_all') : getTranslation('show_my');*/
      toggleBtn.dataset.show = showOnlyMine ? "my" : "all";

      const query = searchInput.value.trim();

      if (query !== "") {
        isSearching = true;
        toggleLoadingCursor(true);

        const formData = new FormData();
        formData.append("action", "search_forum_posts");
        formData.append("security", forumObject.forum_nonce);
        formData.append("query", query);
        formData.append("only_my", showOnlyMine ? "1" : "0");

        fetch(mainObject.ajax_url, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              const container = document.querySelector("#infiniteScrollForum");
              container.innerHTML = data.data.html;
            } else {
              MessageSystem.showMessage(
                "error",
                getTranslatedText("error_generic")
              );
              /*MessageSystem.showMessage("error", getTranslation("error_generic"));*/
            }
          })
          .catch(() => {
            MessageSystem.showMessage(
              "error",
              getTranslatedText("server_error")
            );
            /*MessageSystem.showMessage("error", getTranslation("server_error"));*/
          })
          .finally(() => {
            toggleLoadingCursor(false);
          });
      } else {
        currentPage = 1;
        hasMorePosts = true;
        isSearching = false;

        loadForumPosts({ reset: true });
      }
    });
  }

  const searchInput = document.getElementById("searchInputForum");

  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      const query = this.value.trim();

      if (query === "") return;

      isSearching = true;
      toggleLoadingCursor(true);

      const formData = new FormData();
      formData.append("action", "search_forum_posts");
      formData.append("security", forumObject.forum_nonce);
      formData.append("query", query);
      formData.append("only_my", showOnlyMine ? "1" : "0");

      fetch(mainObject.ajax_url, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const container = document.querySelector("#infiniteScrollForum");
            container.innerHTML = data.data.html;
          } else {
            MessageSystem.showMessage(
              "error",
              getTranslatedText("error_generic")
            );
            /*MessageSystem.showMessage("error", getTranslation("error_generic"));*/
          }
        })
        .catch(() => {
          MessageSystem.showMessage("error", getTranslatedText("server_error"));
          /*MessageSystem.showMessage("error", getTranslation("server_error"));*/
        })
        .finally(() => {
          toggleLoadingCursor(false);
        });
    }
  });

  searchInput.addEventListener("input", function () {
    const query = this.value.trim();

    if (query === "") {
      isSearching = false;
      currentPage = 1;
      hasMorePosts = true;

      loadForumPosts({ reset: true });
    }
  });

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
  const publishButton = form.querySelector(".js-forumPublish");

  function resetForm() {
    form.reset();
    if (quill) {
      quill.setContents([]);
    }
    filesArray = [];
    editingPostId = null;
    isEditing = false;
    renderPreview();
    editButtonsWrapper.classList.add("hidden");
    publishButton.classList.remove("hidden");
  }

  cancelEditButton.addEventListener("click", (e) => {
    e.preventDefault();
    resetForm();
  });

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

    publishButton.classList.add("hidden");
    editButtonsWrapper.classList.remove("hidden");
    window.scrollTo({ top: form.offsetTop - 180, behavior: "smooth" });
  });

  editSubmitButton.addEventListener("click", () => {
    form.requestSubmit();
  });

  document.addEventListener("submit", function (e) {
    const commentForm = e.target.closest(".js-forumCommentForm");
    if (!commentForm) return;

    e.preventDefault();

    const postId = commentForm.dataset.postId;
    const commentInput = commentForm.querySelector('input[name="comment"]');
    const commentText = commentInput.value.trim();

    const inputWrapper = commentForm.querySelector(".input__form");
    const errorElement = inputWrapper.querySelector(".error");

    inputWrapper.classList.remove("notValid");
    errorElement.textContent = "";

    if (!commentText) {
      inputWrapper.classList.add("notValid");
      errorElement.textContent = "Обов'язкове поле.";
      /*errorElement.textContent = getTranslation('validation.required');*/
      return;
    }

    const formData = new FormData();
    formData.append("action", "add_comment");
    formData.append("security", forumObject.forum_nonce);
    formData.append("post_id", postId);
    formData.append("comment", commentText);

    toggleLoadingCursor(true);

    fetch(mainObject.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.data.comment_html) {
          const commentsContainer = commentForm
            .closest(".forum__itemPost")
            .querySelector(".forum__itemPost__comments");

          if (commentsContainer) {
            commentsContainer.insertAdjacentHTML(
              "beforeend",
              data.data.comment_html
            );
          }

          commentForm.reset();

          MessageSystem.showMessage(
            "success",
            getTranslatedText("comment_added")
          );
          /*MessageSystem.showMessage('success', getTranslation('comment_added'));*/
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
      })
      .finally(() => {
        toggleLoadingCursor(false);
      });
  });

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
              ".forum__itemPost__comment__content"
            );
            commentContent.textContent = commentText;
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
    const commentText = commentEl
      .querySelector(".forum__itemPost__comment__content")
      .textContent.trim();
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
    const btn = e.target.closest(".js-toggleComments");
    if (!btn) return;

    const postContainer = btn.closest(".forum__itemPost");
    const hidden = postContainer.querySelectorAll(".hidden-comment");
    const shown = postContainer.querySelectorAll(".shown-comment");

    if (hidden.length > 0) {
      hidden.forEach((el) => {
        el.classList.remove("hidden-comment");
        el.classList.add("shown-comment");
      });
      btn.classList.add("expanded");
      btn.textContent = getTranslatedText("hide_comment");
      /*btn.textContent = getTranslation('hide_comment');*/
    } else {
      shown.forEach((el) => {
        el.classList.remove("shown-comment");
        el.classList.add("hidden-comment");
      });
      btn.classList.remove("expanded");
      btn.textContent = getTranslatedText("show_comment");
      /*btn.textContent = getTranslation('show_comment');*/
    }
  });
});

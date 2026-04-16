const CATEGORY_FILTER_SELECTOR = ".js-catalogFilter[data-category]";
const POST_CONTAINER_SELECTOR = "#catalogItems";
const CATALOG_TAGS_SELECTOR =
  '.dropdown__item input[type="checkbox"]:not([data-category])';
const CATALOG_SORTING_SELECTOR = '.dropdown__item input[type="radio"]';
const ACTIVE_TAGS_CONTAINER_SELECTOR = "#catalogTagActive";
const INFINITE_SCROLL_SELECTOR = "infiniteScroll";

const scrollElement = document.getElementById(INFINITE_SCROLL_SELECTOR);
const DataFilter = {
  category: "",
  tags: [],
  sort: "novelty",
  priceMin: 0,
  priceMax: 1000000,
  noPrice: false,
  currentPage: 1,
  hasMorePosts: true,
  scrollPage: scrollElement?.dataset.scrollPage || "catalog",
};

function formatPrice(val) {
  return new Intl.NumberFormat("uk-UA").format(val);
}

const URL_PARAM_CATEGORY = "category";
const URL_PARAM_TAGS = "tags";
const URL_PARAM_SORT = "sort";
const URL_PARAM_PRICE_MIN = "price_min";
const URL_PARAM_PRICE_MAX = "price_max";
const URL_PARAM_NO_PRICE = "no_price";

function getCategoryFromPath() {
  const pathname = window.location.pathname;
  const parts = pathname.split("/").filter(Boolean);
  const catalogIdx = parts.indexOf("catalog");
  if (catalogIdx === -1) return null;
  const afterCatalog = parts.slice(catalogIdx + 1);
  if (afterCatalog.length === 0) return "all";
  return afterCatalog[afterCatalog.length - 1];
}

function getFiltersFromUrl() {
  const params = new URLSearchParams(window.location.search);
  const tagsParam = params.get(URL_PARAM_TAGS);
  const tags = tagsParam
    ? tagsParam.split(",").filter(Boolean)
    : null;
  const priceSliderEl = document.querySelector(".js-priceSlider");
  const sliderMax = priceSliderEl
    ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
    : 1000000;
  const categoryFromPath = getCategoryFromPath();
  const category =
    categoryFromPath !== null
      ? categoryFromPath
      : params.get(URL_PARAM_CATEGORY) || null;
  return {
    category: category || null,
    tags,
    sort: params.get(URL_PARAM_SORT) || null,
    priceMin: params.has(URL_PARAM_PRICE_MIN)
      ? parseInt(params.get(URL_PARAM_PRICE_MIN), 10) || 0
      : null,
    priceMax: params.has(URL_PARAM_PRICE_MAX)
      ? parseInt(params.get(URL_PARAM_PRICE_MAX), 10) ?? sliderMax
      : null,
    noPrice: params.get(URL_PARAM_NO_PRICE) === "1",
  };
}

function hasFilterParamsInUrl() {
  const categoryFromPath = getCategoryFromPath();
  if (categoryFromPath !== null && categoryFromPath !== "all") return true;
  const params = new URLSearchParams(window.location.search);
  return (
    params.has(URL_PARAM_CATEGORY) ||
    params.has(URL_PARAM_TAGS) ||
    params.has(URL_PARAM_SORT) ||
    params.has(URL_PARAM_PRICE_MIN) ||
    params.has(URL_PARAM_PRICE_MAX) ||
    params.get(URL_PARAM_NO_PRICE) === "1"
  );
}

function updateUrlFromFilters(replace = false) {
  const filters = typeof filtersObject !== "undefined" ? filtersObject : {};
  const category =
    DataFilter.category && DataFilter.category !== "all"
      ? DataFilter.category
      : "all";
  const tags =
    DataFilter.tags?.length && !DataFilter.tags.includes("tags-all")
      ? DataFilter.tags.join(",")
      : null;
  const sort =
    DataFilter.sort && DataFilter.sort !== "novelty"
      ? DataFilter.sort
      : null;
  const priceSliderEl = document.querySelector(".js-priceSlider");
  const sliderMax = priceSliderEl
    ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
    : 1000000;
  const priceMin =
    DataFilter.priceMin > 0 ? DataFilter.priceMin : null;
  const priceMax =
    DataFilter.priceMax < sliderMax ? DataFilter.priceMax : null;
  const noPrice = DataFilter.noPrice ? "1" : null;

  // For multi-select always use the base catalog URL + query params
  let baseUrl = filters.catalogBaseUrl ||
    window.location.origin + "/catalog";

  const url = new URL(baseUrl);
  const setOrDelete = (key, val) => {
    if (val != null && val !== "" && val !== "all") {
      url.searchParams.set(key, String(val));
    } else {
      url.searchParams.delete(key);
    }
  };
  setOrDelete(URL_PARAM_CATEGORY, category !== "all" ? category : null);
  setOrDelete(URL_PARAM_TAGS, tags);
  setOrDelete(URL_PARAM_SORT, sort);
  setOrDelete(URL_PARAM_PRICE_MIN, priceMin);
  setOrDelete(URL_PARAM_PRICE_MAX, priceMax);
  setOrDelete(URL_PARAM_NO_PRICE, noPrice);

  const method = replace ? "replaceState" : "pushState";
  window.history[method]({}, "", url.toString());
}

function applyFiltersFromUrl(params, postsContainer) {
  applyingFiltersFromUrl = true;
  const priceSliderEl = document.querySelector(".js-priceSlider");
  const sliderMax = priceSliderEl
    ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
    : 1000000;
  const defaultSort = "novelty";
  const category = params.category ?? "all";

  DataFilter.category = category;
  const catEl = document.querySelector(
    `.js-catalogFilter[data-category="${category}"]`,
  );
  if (catEl) {
    if (catEl.type === "checkbox") {
      document.querySelectorAll(".js-catalogFilter[data-category]").forEach((f) => {
        f.checked = f === catEl;
      });
    } else {
      document.querySelectorAll(".js-catalogFilter[data-category]").forEach((f) => {
        f.classList.toggle("active", f === catEl);
      });
    }
  }

  if (params.tags != null && params.tags.length > 0) {
    DataFilter.tags = params.tags;
    document.querySelectorAll(CATALOG_TAGS_SELECTOR).forEach((cb) => {
      const checked = params.tags.includes(cb.id);
      cb.checked = checked;
      try {
        localStorage.setItem(cb.id, String(checked));
      } catch (_) {}
    });
  } else {
    DataFilter.tags = ["tags-all"];
    document.querySelectorAll(CATALOG_TAGS_SELECTOR).forEach((cb) => {
      cb.checked = cb.id === "tags-all";
      try {
        localStorage.setItem(cb.id, cb.id === "tags-all" ? "true" : "false");
      } catch (_) {}
    });
  }

  if (params.sort != null) {
    DataFilter.sort = params.sort;
    const sortRadio = document.querySelector(
      `input[name="sort"][value="${params.sort}"]`,
    );
    if (sortRadio) {
      sortRadio.checked = true;
      try {
        localStorage.setItem("sort", params.sort);
      } catch (_) {}
    }
  } else {
    DataFilter.sort = defaultSort;
  }

  if (params.priceMin != null || params.priceMax != null || params.noPrice) {
    const min = params.priceMin ?? 0;
    const max = params.priceMax ?? sliderMax;
    DataFilter.priceMin = min;
    DataFilter.priceMax = max;
    DataFilter.noPrice = !!params.noPrice;
    const minRange = document.querySelector(".js-priceRangeMin");
    const maxRange = document.querySelector(".js-priceRangeMax");
    const minInput = document.querySelector(".js-priceMinInput");
    const maxInput = document.querySelector(".js-priceMaxInput");
    const minVal = document.querySelector(".js-priceMinVal");
    const maxVal = document.querySelector(".js-priceMaxVal");
    const noPriceEl = document.querySelector(".js-noPrice");
    if (minRange) minRange.value = min;
    if (maxRange) maxRange.value = max;
    if (minInput) minInput.value = min;
    if (maxInput) maxInput.value = max;
    if (minVal) minVal.textContent = formatPrice(min);
    if (maxVal) maxVal.textContent = formatPrice(max);
    if (noPriceEl) noPriceEl.checked = !!params.noPrice;
    const track = document.querySelector(".catalog__sidebar__sliderTrack");
    if (track) {
      const absMin = 0;
      const range = sliderMax - absMin || 1;
      track.style.setProperty("--min-percent", `${((min - absMin) / range) * 100}%`);
      track.style.setProperty("--max-percent", `${((max - absMin) / range) * 100}%`);
    }
  }

  DataFilter.currentPage = 1;
  DataFilter.hasMorePosts = true;

  const container = postsContainer || document.querySelector(POST_CONTAINER_SELECTOR);
  fetchPosts(getFetchBody())
    .then((data) => {
      if (data && container) {
        updateCatalogPosts(data, container);
        document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
        updateUrlFromFilters(true);
      }
    })
    .catch((err) => console.error("Error:", err))
    .finally(() => {
      applyingFiltersFromUrl = false;
    });
}

function getFetchBody(extra = {}) {
  const filters = typeof filtersObject !== "undefined" ? filtersObject : {};
  const body = {
    action: "filter_posts",
    security: filters.filters_nonce || "",
    current_page_category: filters.currentCategory || "",
    category: DataFilter.category || "all",
    tags: JSON.stringify(DataFilter.tags),
    sort: DataFilter.sort,
    scrollPage: DataFilter.scrollPage,
    price_min: DataFilter.priceMin,
    price_max: DataFilter.priceMax,
    no_price: DataFilter.noPrice ? "1" : "0",
    ...extra,
  };
  return body;
}

let fetchAbortController = null;
let applyingFiltersFromUrl = false;

function fetchPosts(body) {
  const ajaxUrl =
    typeof mainObject !== "undefined"
      ? mainObject.ajax_url
      : "/wp-admin/admin-ajax.php";
  const opts = {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams(body),
  };
  if (typeof AbortController !== "undefined") {
    if (fetchAbortController) fetchAbortController.abort();
    fetchAbortController = new AbortController();
    opts.signal = fetchAbortController.signal;
  }

  return fetch(ajaxUrl, opts)
    .then((response) => response.text())
    .catch((err) => {
      if (err.name === "AbortError") return null;
      throw err;
    });
}

function updateCatalogPosts(data, postsContainer) {
  const container =
    postsContainer || document.querySelector(POST_CONTAINER_SELECTOR);
  if (!container) return;

  const temp = document.createElement("div");
  temp.innerHTML = data.trim();
  const wrapper = temp.querySelector("[data-catalog-count]");
  if (wrapper) {
    const count = wrapper.getAttribute("data-catalog-count");
    const countEl = document.querySelector(".js-catalogCountNum");
    if (countEl) countEl.textContent = count || "0";
    const filtersJson = wrapper.getAttribute("data-available-filters");
    if (filtersJson) {
      try {
        const filters = JSON.parse(filtersJson);
        document
          .querySelectorAll(
            ".catalog__sidebar__checkbox[data-filter-slug][data-filter-section]",
          )
          .forEach((el) => {
            const slug = el.getAttribute("data-filter-slug");
            const section = el.getAttribute("data-filter-section");
            if (section === "category") return;
            const alwaysShow = slug === "all" || slug === "tags-all";
            const available =
              alwaysShow ||
              (filters[section] && filters[section].includes(slug));
            el.setAttribute("data-available", available ? "1" : "0");
          });
        document
          .querySelectorAll(".js-catalogSidebarSection")
          .forEach((section) => {
            const sectionName = section.getAttribute("data-filter-section");
            if (sectionName === "category") return;
            const availableSlugs = filters[sectionName] || [];
            const hasOptions = availableSlugs.length > 0;
            section.classList.toggle(
              "catalog__sidebar__section--empty",
              !hasOptions,
            );
          });
      } catch (_) {}
    }
    container.innerHTML = wrapper.innerHTML;
  } else {
    container.innerHTML = data;
  }
  // Ensure no wrapper div stays inside container (fixes flex layout)
  const firstChild = container.firstElementChild;
  const strayWrapper =
    firstChild && firstChild.hasAttribute("data-catalog-count")
      ? firstChild
      : null;
  if (strayWrapper) {
    while (strayWrapper.firstChild) {
      container.insertBefore(strayWrapper.firstChild, strayWrapper);
    }
    strayWrapper.remove();
  }
}

class CategoryFilter {
  constructor(filtersSelector, postsContainerSelector) {
    this.filters = document.querySelectorAll(filtersSelector);
    this.postsContainer = document.querySelector(postsContainerSelector);
    this.init();
  }

  init() {
    if (!this.filters.length || !this.postsContainer) return;

    this.filters.forEach((filter) => {
      const eventType = filter.type === "checkbox" ? "change" : "click";
      filter.addEventListener(eventType, (event) =>
        this.handleFilterClick(event, filter),
      );
    });
  }

  handleFilterClick(event, filter) {
    const category = filter.getAttribute("data-category");
    if (!category) return;

    if (filter.type === "checkbox") {
      const allFilter = document.querySelector('.js-catalogFilter[data-category="all"]');
      if (category === "all") {
        if (filter.checked) {
          this.filters.forEach((f) => { f.checked = true; });
        } else {
          this.filters.forEach((f) => { f.checked = false; });
        }
      } else {
        const allChecked = Array.from(this.filters).filter(f => f.getAttribute("data-category") !== "all").every((f) => f.checked);
        const noneChecked = Array.from(this.filters).filter(f => f.getAttribute("data-category") !== "all").every((f) => !f.checked);
        if (allFilter) {
          allFilter.checked = allChecked || noneChecked;
          allFilter.indeterminate = !allChecked && !noneChecked;
        }
      }
    } else {
      this.filters.forEach((f) => f.classList.remove("active"));
      filter.classList.add("active");
    }

    // Collect all checked non-"all" categories
    const checkedCats = Array.from(this.filters)
      .filter(f => f.type === "checkbox" && f.checked && f.getAttribute("data-category") !== "all")
      .map(f => f.getAttribute("data-category"));

    const selectedCategory = checkedCats.length > 0 ? checkedCats.join(",") : "all";
    DataFilter.category = selectedCategory;

    fetchPosts(getFetchBody({ category: selectedCategory }))
      .then((data) => {
        if (data) {
          updateCatalogPosts(data, this.postsContainer);
          document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  updatePosts(data) {
    updateCatalogPosts(data, this.postsContainer);
  }
}

class CatalogFilter {
  constructor(
    tagsSelector,
    sortingSelector,
    postsContainerSelector,
    activeTagsContainerSelector,
    defaultSort = "novelty",
  ) {
    this.tags = document.querySelectorAll(tagsSelector);
    this.sorting = document.querySelectorAll(sortingSelector);
    this.postsContainer = document.querySelector(postsContainerSelector);
    this.activeTagsContainer = document.querySelector(
      activeTagsContainerSelector,
    );
    this.defaultSort = defaultSort;
    this.init();
  }

  init() {
    if (!this.postsContainer) return;

    this.tags.forEach((checkbox) => {
      checkbox.addEventListener("change", (e) =>
        this.handleTagsChange(e.target),
      );
    });

    const savedSort = localStorage.getItem("sort");
    const defaultSortRadio = document.querySelector(
      `input[value="${this.defaultSort}"]`,
    );
    if (savedSort) {
      const savedSortRadio = document.querySelector(
        `input[value="${savedSort}"]`,
      );
      if (savedSortRadio) savedSortRadio.checked = true;
    } else if (defaultSortRadio) {
      defaultSortRadio.checked = true;
      localStorage.setItem("sort", this.defaultSort);
    }

    this.sorting.forEach((radio) => {
      radio.addEventListener("change", () => this.handleSortChange());
    });

    document.addEventListener("catalog:filtersUpdated", () =>
      this.refreshActiveTags(),
    );

    // Don't applyFilters on init - page already has correct server-rendered content.
    // Applying on init caused a race: user clicks category → results show → init fetch
    // completes → overwrites with wrong data.
    this.refreshActiveTags();
  }

  syncTagsAllExclusivity() {
    const tagsAll = document.getElementById("tags-all");
    const hasSpecific = Array.from(this.tags).some(
      (cb) => cb.id !== "tags-all" && cb.checked,
    );
    if (tagsAll && hasSpecific) {
      tagsAll.checked = false;
    }
  }

  handleTagsChange(changedCheckbox) {
    if (changedCheckbox) {
      const tagsAll = document.getElementById("tags-all");
      if (changedCheckbox.id === "tags-all") {
        if (changedCheckbox.checked) {
          // "Всі" checked → check all specific tags
          this.tags.forEach((cb) => { cb.checked = true; });
        } else {
          // "Всі" unchecked → uncheck all
          this.tags.forEach((cb) => { cb.checked = false; });
        }
      } else if (tagsAll) {
        // Individual tag toggled → sync "Всі" state
        const allChecked = Array.from(this.tags).every((cb) => cb.checked);
        const noneChecked = Array.from(this.tags).every((cb) => !cb.checked);
        tagsAll.checked = allChecked || noneChecked;
        tagsAll.indeterminate = !allChecked && !noneChecked;
      }
    }

    this.tags.forEach((checkbox) => {
      localStorage.setItem(checkbox.id, checkbox.checked);
    });

    this.applyFilters();
  }

  handleSortChange() {
    const selectedSort = document.querySelector(
      `${CATALOG_SORTING_SELECTOR}:checked`,
    )?.value;
    if (selectedSort) {
      localStorage.setItem("sort", selectedSort);
    } else {
      localStorage.setItem("sort", this.defaultSort);
    }

    this.applyFilters();
  }

  applyFilters() {
    const selectedTags = Array.from(this.tags)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.id);

    const selectedSort =
      document.querySelector(`${CATALOG_SORTING_SELECTOR}:checked`)?.value ||
      this.defaultSort;

    DataFilter.tags = selectedTags;
    DataFilter.sort = selectedSort;
    DataFilter.currentPage = 1;
    DataFilter.hasMorePosts = true;
    this.refreshActiveTags();

    fetchPosts(
      getFetchBody({
        tags: JSON.stringify(selectedTags),
        sort: selectedSort,
      }),
    )
      .then((data) => {
        if (data) {
          updateCatalogPosts(data, this.postsContainer);
          document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
        }
      })
      .catch((error) => console.error("Error:", error));
  }

  updatePosts(data) {
    this.postsContainer.innerHTML = data;
  }

  refreshActiveTags() {
    if (!this.activeTagsContainer) return;

    const selectedCategoryEl = document.querySelector(
      ".js-catalogFilter[data-category]:checked",
    );
    const selectedCategory =
      selectedCategoryEl?.getAttribute("data-category") || "all";

    const selectedTags = Array.from(this.tags)
      .filter((cb) => cb.checked)
      .map((cb) => cb.id);
    const selectedSort =
      document.querySelector(`${CATALOG_SORTING_SELECTOR}:checked`)?.value ||
      this.defaultSort;

    const priceSliderEl = document.querySelector(".js-priceSlider");
    const sliderMax = priceSliderEl
      ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
      : 1000000;
    const priceMin =
      parseInt(document.querySelector(".js-priceRangeMin")?.value, 10) || 0;
    const priceMax =
      parseInt(document.querySelector(".js-priceRangeMax")?.value, 10) ||
      sliderMax;
    const noPriceChecked = document.querySelector(".js-noPrice")?.checked;
    const isPriceFiltered =
      priceMin > 0 || priceMax < sliderMax || !!noPriceChecked;

    this.activeTagsContainer.innerHTML = "";

    // Search query chip
    const searchQuery = new URLSearchParams(window.location.search).get("search_string");
    if (searchQuery) {
      const searchEl = document.createElement("div");
      searchEl.className = "catalog__tagActive__item chip-label";
      searchEl.textContent = `«${searchQuery}»`;

      const closeEl = document.createElement("span");
      closeEl.className = "catalog__tagActive__close";
      searchEl.append(closeEl);

      closeEl.addEventListener("click", () => {
        const url = new URL(window.location.href);
        url.searchParams.delete("search_string");
        window.location.href = url.toString();
      });

      this.activeTagsContainer.appendChild(searchEl);
    }

    // Multi-select: show a chip for each selected category
    const selectedCategories = Array.from(
      document.querySelectorAll(".js-catalogFilter[data-category]:checked")
    )
      .map(f => f.getAttribute("data-category"))
      .filter(cat => cat && cat !== "all");

    selectedCategories.forEach((cat) => {
      const categoryLabel = document.querySelector(`label[for="category-${cat}"]`);
      if (!categoryLabel) return;

      const categoryElement = document.createElement("div");
      categoryElement.className = "catalog__tagActive__item chip-label";
      categoryElement.textContent = categoryLabel.textContent.trim();

      const closeElement = document.createElement("span");
      closeElement.className = "catalog__tagActive__close";
      categoryElement.append(closeElement);

      closeElement.addEventListener("click", () => {
        // Uncheck this specific category
        const catCheckbox = document.querySelector(`.js-catalogFilter[data-category="${cat}"]`);
        if (catCheckbox) catCheckbox.checked = false;

        // Recompute remaining selected
        const remaining = Array.from(
          document.querySelectorAll(".js-catalogFilter[data-category]:checked")
        )
          .map(f => f.getAttribute("data-category"))
          .filter(c => c && c !== "all");

        // Sync "Всі" state
        const allFilter = document.querySelector('.js-catalogFilter[data-category="all"]');
        const allSpecific = document.querySelectorAll('.js-catalogFilter[data-category]:not([data-category="all"])');
        const allChecked = Array.from(allSpecific).every(f => f.checked);
        const noneChecked = remaining.length === 0;
        if (allFilter) {
          allFilter.checked = allChecked || noneChecked;
          allFilter.indeterminate = !allChecked && !noneChecked;
        }

        const newCategory = remaining.length > 0 ? remaining.join(",") : "all";
        DataFilter.category = newCategory;
        DataFilter.currentPage = 1;
        DataFilter.hasMorePosts = true;

        fetchPosts(getFetchBody({ category: newCategory }))
          .then((data) => {
            if (data) {
              updateCatalogPosts(data, this.postsContainer);
              document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
            }
          })
          .catch((err) => console.error("Error:", err));
      });

      this.activeTagsContainer.appendChild(categoryElement);
    });

    selectedTags
      .filter((id) => id !== "tags-all")
      .forEach((tagId) => {
        const tagLabel = document.querySelector(`label[for="${tagId}"]`);
        if (tagLabel) {
          const tagElement = document.createElement("div");
          tagElement.className = "catalog__tagActive__item chip-label";
          tagElement.textContent = tagLabel.textContent.trim();

          const closeElement = document.createElement("span");
          closeElement.className = "catalog__tagActive__close";
          tagElement.append(closeElement);

          closeElement.addEventListener("click", () => {
            document.getElementById(tagId).checked = false;
            this.handleTagsChange();
          });

          this.activeTagsContainer.appendChild(tagElement);
        }
      });

    if (selectedSort && selectedSort !== this.defaultSort) {
      const sortLabel = document.querySelector(`label[for="${selectedSort}"]`);
      if (sortLabel) {
        const sortElement = document.createElement("div");
        sortElement.className = "catalog__tagActive__item chip-label";
        sortElement.textContent = sortLabel.textContent.trim();

        const closeElement = document.createElement("span");
        closeElement.className = "catalog__tagActive__close";
        sortElement.append(closeElement);

        closeElement.addEventListener("click", () => {
          const selectedSortRadio = document.querySelector(
            `input[name="sort"]:checked`,
          );
          if (selectedSortRadio) selectedSortRadio.checked = false;
          localStorage.removeItem("sort");

          const defaultSortRadio = document.querySelector(
            `input[value="${this.defaultSort}"]`,
          );
          if (defaultSortRadio) defaultSortRadio.checked = true;
          localStorage.setItem("sort", this.defaultSort);

          this.handleSortChange();
        });

        this.activeTagsContainer.appendChild(sortElement);
      }
    }

    if (isPriceFiltered) {
      let priceText = "";
      if (noPriceChecked && (priceMin > 0 || priceMax < sliderMax)) {
        priceText = `${formatPrice(priceMin)} – ${formatPrice(priceMax)} грн + без ціни`;
      } else if (noPriceChecked) {
        priceText = "Без ціни";
      } else {
        priceText = `${formatPrice(priceMin)} – ${formatPrice(priceMax)} грн`;
      }

      const priceElement = document.createElement("div");
      priceElement.className = "catalog__tagActive__item chip-label";
      priceElement.textContent = priceText;

      const closeElement = document.createElement("span");
      closeElement.className = "catalog__tagActive__close";
      priceElement.append(closeElement);

      closeElement.addEventListener("click", () => {
        const priceMinEl = document.querySelector(".js-priceRangeMin");
        const priceMaxEl = document.querySelector(".js-priceRangeMax");
        const noPriceEl = document.querySelector(".js-noPrice");
        const minValEl = document.querySelector(".js-priceMinVal");
        const maxValEl = document.querySelector(".js-priceMaxVal");
        const minInputEl = document.querySelector(".js-priceMinInput");
        const maxInputEl = document.querySelector(".js-priceMaxInput");

        if (priceMinEl) priceMinEl.value = 0;
        if (priceMaxEl) priceMaxEl.value = sliderMax;
        if (noPriceEl) noPriceEl.checked = false;
        if (minValEl) minValEl.textContent = formatPrice(0);
        if (maxValEl) maxValEl.textContent = formatPrice(sliderMax);
        if (minInputEl) minInputEl.value = 0;
        if (maxInputEl) maxInputEl.value = sliderMax;

        DataFilter.priceMin = 0;
        DataFilter.priceMax = sliderMax;
        DataFilter.noPrice = false;
        DataFilter.currentPage = 1;
        DataFilter.hasMorePosts = true;

        const sliderTrack = document.querySelector(
          ".catalog__sidebar__sliderTrack",
        );
        if (sliderTrack) {
          sliderTrack.style.setProperty("--min-percent", "0%");
          sliderTrack.style.setProperty("--max-percent", "100%");
        }

        fetchPosts(getFetchBody())
          .then((data) => {
            if (data) {
              updateCatalogPosts(data, this.postsContainer);
              document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
            }
          })
          .catch((err) => console.error("Error:", err));
      });

      this.activeTagsContainer.appendChild(priceElement);
    }
  }
}

const PRICE_SLIDER_SELECTOR = ".js-priceSlider";

class PriceSlider {
  constructor(containerSelector, postsContainerSelector) {
    this.container = document.querySelector(containerSelector);
    this.postsContainer = document.querySelector(postsContainerSelector);
    if (!this.container || !this.postsContainer) return;

    this.minRange = this.container.querySelector(".js-priceRangeMin");
    this.maxRange = this.container.querySelector(".js-priceRangeMax");
    this.minInput = this.container.querySelector(".js-priceMinInput");
    this.maxInput = this.container.querySelector(".js-priceMaxInput");
    this.minVal = this.container.querySelector(".js-priceMinVal");
    this.maxVal = this.container.querySelector(".js-priceMaxVal");
    this.noPrice = this.container.querySelector(".js-noPrice");
    this.sliderTrack = this.container.querySelector(
      ".catalog__sidebar__sliderTrack",
    );

    const dataMin = parseInt(this.container.dataset.min, 10) || 0;
    const dataMax = parseInt(this.container.dataset.max, 10) || 1000000;
    this.absMin = dataMin;
    this.absMax = dataMax;

    this.init();
  }

  init() {
    if (!this.minRange || !this.maxRange) return;

    this.applyTimeout = null;
    this.debouncedApply = () => {
      clearTimeout(this.applyTimeout);
      this.applyTimeout = setTimeout(() => this.applyPriceFilter(), 400);
    };

    this.minRange.addEventListener("input", () => this.onMinChange());
    this.maxRange.addEventListener("input", () => this.onMaxChange());
    this.minInput?.addEventListener("change", () => this.onMinInputChange());
    this.maxInput?.addEventListener("change", () => this.onMaxInputChange());
    this.noPrice?.addEventListener("change", () => this.applyPriceFilter());

    this.syncDisplay();
  }

  onMinChange() {
    let val = parseInt(this.minRange.value, 10);
    const maxVal = parseInt(this.maxRange.value, 10);
    if (val > maxVal) {
      val = maxVal;
      this.minRange.value = val;
    }
    this.minInput && (this.minInput.value = val);
    this.minVal && (this.minVal.textContent = formatPrice(val));
    DataFilter.priceMin = val;
    this.updateTrackPosition();
    this.debouncedApply();
  }

  onMaxChange() {
    let val = parseInt(this.maxRange.value, 10);
    const minVal = parseInt(this.minRange.value, 10);
    if (val < minVal) {
      val = minVal;
      this.maxRange.value = val;
    }
    this.maxInput && (this.maxInput.value = val);
    this.maxVal && (this.maxVal.textContent = formatPrice(val));
    DataFilter.priceMax = val;
    this.updateTrackPosition();
    this.debouncedApply();
  }

  onMinInputChange() {
    let val = parseInt(this.minInput.value, 10) || this.absMin;
    val = Math.max(this.absMin, Math.min(this.absMax, val));
    const maxVal = parseInt(this.maxRange.value, 10);
    if (val > maxVal) val = maxVal;
    this.minInput.value = val;
    this.minRange.value = val;
    this.minVal && (this.minVal.textContent = formatPrice(val));
    DataFilter.priceMin = val;
    this.updateTrackPosition();
    this.applyPriceFilter();
  }

  onMaxInputChange() {
    let val = parseInt(this.maxInput.value, 10) || this.absMax;
    val = Math.max(this.absMin, Math.min(this.absMax, val));
    const minVal = parseInt(this.minRange.value, 10);
    if (val < minVal) val = minVal;
    this.maxInput.value = val;
    this.maxRange.value = val;
    this.maxVal && (this.maxVal.textContent = formatPrice(val));
    DataFilter.priceMax = val;
    this.updateTrackPosition();
    this.applyPriceFilter();
  }

  updateTrackPosition() {
    if (!this.sliderTrack) return;
    const min = parseInt(this.minRange.value, 10);
    const max = parseInt(this.maxRange.value, 10);
    const range = this.absMax - this.absMin || 1;
    const minPercent = ((min - this.absMin) / range) * 100;
    const maxPercent = ((max - this.absMin) / range) * 100;
    this.sliderTrack.style.setProperty("--min-percent", `${minPercent}%`);
    this.sliderTrack.style.setProperty("--max-percent", `${maxPercent}%`);
  }

  syncDisplay() {
    const min = parseInt(this.minRange.value, 10);
    const max = parseInt(this.maxRange.value, 10);
    this.minVal && (this.minVal.textContent = formatPrice(min));
    this.maxVal && (this.maxVal.textContent = formatPrice(max));
    this.minInput && (this.minInput.value = min);
    this.maxInput && (this.maxInput.value = max);
    DataFilter.priceMin = min;
    DataFilter.priceMax = max;
    DataFilter.noPrice = this.noPrice?.checked ?? false;
    this.updateTrackPosition();
  }

  applyPriceFilter() {
    DataFilter.noPrice = this.noPrice?.checked ?? false;
    DataFilter.currentPage = 1;
    DataFilter.hasMorePosts = true;

    fetchPosts(getFetchBody())
      .then((data) => {
        if (data) {
          updateCatalogPosts(data, this.postsContainer);
          document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
        }
      })
      .catch((err) => console.error("Error:", err));
  }

  reset() {
    this.minRange.value = this.absMin;
    this.maxRange.value = this.absMax;
    this.minInput && (this.minInput.value = this.absMin);
    this.maxInput && (this.maxInput.value = this.absMax);
    this.minVal && (this.minVal.textContent = formatPrice(this.absMin));
    this.maxVal && (this.maxVal.textContent = formatPrice(this.absMax));
    this.noPrice && (this.noPrice.checked = false);
    DataFilter.priceMin = this.absMin;
    DataFilter.priceMax = this.absMax;
    DataFilter.noPrice = false;
  }
}

const SIDEBAR_CLEAR_SELECTOR = ".js-catalogSidebarClear";
const SIDEBAR_SHOW_ALL_SELECTOR = ".js-catalogSidebarShowAll";

class CatalogSidebar {
  constructor() {
    this.clearBtn = document.querySelector(SIDEBAR_CLEAR_SELECTOR);
    this.showAllBtns = document.querySelectorAll(SIDEBAR_SHOW_ALL_SELECTOR);
    this.categoryFilter = null;
    this.catalogFilter = null;
    this.init();
  }

  init() {
    if (!this.clearBtn && !this.showAllBtns.length) return;

    this.clearBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      this.clearAllFilters();
    });

    this.showAllBtns.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        this.clearAllFilters();
      });
    });

    document.addEventListener("catalog:filtersUpdated", () =>
      this.updateButtonsVisibility(),
    );
    this.updateButtonsVisibility();
  }

  setFilters(categoryFilter, catalogFilter) {
    this.categoryFilter = categoryFilter;
    this.catalogFilter = catalogFilter;
  }

  clearAllFilters() {
    const categoryAll = document.querySelector(
      '.js-catalogFilter[data-category="all"]',
    );
    if (categoryAll) {
      categoryAll.checked = true;
      document
        .querySelectorAll(".js-catalogFilter[data-category]")
        .forEach((f) => {
          if (f !== categoryAll) f.checked = false;
        });
    }

    document.querySelectorAll(CATALOG_TAGS_SELECTOR).forEach((cb) => {
      cb.checked = cb.id === "tags-all";
      localStorage.setItem(cb.id, cb.id === "tags-all" ? "true" : "false");
    });

    const defaultSort = document.querySelector(
      'input[name="sort"][value="novelty"]',
    );
    if (defaultSort) {
      defaultSort.checked = true;
      localStorage.setItem("sort", "novelty");
    }

    DataFilter.category = "all";
    DataFilter.tags = ["tags-all"];
    DataFilter.currentPage = 1;
    DataFilter.hasMorePosts = true;

    const activeTagsContainer = document.querySelector(
      ACTIVE_TAGS_CONTAINER_SELECTOR,
    );
    if (activeTagsContainer) activeTagsContainer.innerHTML = "";

    const priceSliderEl = document.querySelector(".js-priceSlider");
    const maxVal = priceSliderEl
      ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
      : 1000000;
    const priceMinEl = document.querySelector(".js-priceRangeMin");
    const priceMaxEl = document.querySelector(".js-priceRangeMax");
    const noPriceEl = document.querySelector(".js-noPrice");
    const minValEl = document.querySelector(".js-priceMinVal");
    const maxValEl = document.querySelector(".js-priceMaxVal");
    const minInputEl = document.querySelector(".js-priceMinInput");
    const maxInputEl = document.querySelector(".js-priceMaxInput");
    if (priceMinEl) priceMinEl.value = 0;
    if (priceMaxEl) priceMaxEl.value = maxVal;
    if (noPriceEl) noPriceEl.checked = false;
    if (minValEl) minValEl.textContent = formatPrice(0);
    if (maxValEl) maxValEl.textContent = formatPrice(maxVal);
    if (minInputEl) minInputEl.value = 0;
    if (maxInputEl) maxInputEl.value = maxVal;
    DataFilter.priceMin = 0;
    DataFilter.priceMax = maxVal;
    DataFilter.noPrice = false;

    const sliderTrack = document.querySelector(
      ".catalog__sidebar__sliderTrack",
    );
    if (sliderTrack) {
      sliderTrack.style.setProperty("--min-percent", "0%");
      sliderTrack.style.setProperty("--max-percent", "100%");
    }

    fetchPosts(
      getFetchBody({
        category: "all",
        tags: JSON.stringify(["tags-all"]),
        sort: "novelty",
        price_min: 0,
        price_max: 1000000,
        no_price: "0",
      }),
    )
      .then((data) => {
        if (data) {
          updateCatalogPosts(data);
          document.dispatchEvent(new CustomEvent("catalog:filtersUpdated"));
        }
      })
      .catch((err) => console.error("Error:", err));

    this.updateButtonsVisibility();
  }

  hasActiveFilters() {
    const categoryAll = document.querySelector(
      '.js-catalogFilter[data-category="all"]',
    );
    const categoryChecked = document.querySelector(
      ".js-catalogFilter[data-category]:checked",
    );
    const isCategoryFiltered =
      categoryChecked &&
      categoryChecked.getAttribute("data-category") !== "all";

    const tagCheckboxes = document.querySelectorAll(CATALOG_TAGS_SELECTOR);
    const selectedTags = Array.from(tagCheckboxes)
      .filter((cb) => cb.checked && cb.id !== "tags-all")
      .map((cb) => cb.id);

    const sortRadio = document.querySelector(
      `${CATALOG_SORTING_SELECTOR}:checked`,
    );
    const isSortFiltered = sortRadio && sortRadio.value !== "novelty";

    const priceSliderEl = document.querySelector(".js-priceSlider");
    const sliderMax = priceSliderEl
      ? parseInt(priceSliderEl.dataset.max, 10) || 1000000
      : 1000000;
    const priceMin =
      parseInt(document.querySelector(".js-priceRangeMin")?.value, 10) || 0;
    const priceMax =
      parseInt(document.querySelector(".js-priceRangeMax")?.value, 10) ||
      sliderMax;
    const noPriceChecked = document.querySelector(".js-noPrice")?.checked;
    const isPriceFiltered =
      priceMin > 0 || priceMax < sliderMax || !!noPriceChecked;

    return (
      isCategoryFiltered ||
      selectedTags.length > 0 ||
      isSortFiltered ||
      isPriceFiltered
    );
  }

  updateButtonsVisibility() {
    const hasActive = this.hasActiveFilters();

    const sidebar = document.querySelector(".catalog__sidebar");
    if (sidebar) {
      sidebar.classList.toggle("catalog__sidebar--showActions", hasActive);
    }
  }
}

function initCatalogFilters() {
  try {
    const postsContainer = document.querySelector(POST_CONTAINER_SELECTOR);
    const hasSidebar = document.querySelector(".catalog__sidebar");
    if (!postsContainer || !hasSidebar) return;

    document.addEventListener("catalog:filtersUpdated", () => {
      if (!applyingFiltersFromUrl) {
        updateUrlFromFilters(false);
      }
    });

    const categoryFilter = new CategoryFilter(
      CATEGORY_FILTER_SELECTOR,
      POST_CONTAINER_SELECTOR,
    );
    const catalogFilter = new CatalogFilter(
      CATALOG_TAGS_SELECTOR,
      CATALOG_SORTING_SELECTOR,
      POST_CONTAINER_SELECTOR,
      ACTIVE_TAGS_CONTAINER_SELECTOR,
    );

    new PriceSlider(PRICE_SLIDER_SELECTOR, POST_CONTAINER_SELECTOR);
    const sidebar = new CatalogSidebar();
    sidebar.setFilters(categoryFilter, catalogFilter);

    if (hasFilterParamsInUrl()) {
      const params = getFiltersFromUrl();
      applyFiltersFromUrl(params);
    }

    window.addEventListener("popstate", () => {
      const params = getFiltersFromUrl();
      applyFiltersFromUrl(params);
    });
  } catch (e) {
    console.error("Catalog filters init error:", e);
  }
}

// Scripts load in footer — DOMContentLoaded may have already fired.
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCatalogFilters);
} else {
  initCatalogFilters();
}

if (scrollElement) {
  class InfiniteScroll {
    constructor(postsContainerSelector, triggerOffset = 300) {
      this.postsContainer = document.querySelector(postsContainerSelector);
      this.triggerOffset = triggerOffset;
      this.isLoading = false;

      this.init();
    }

    init() {
      if (!this.postsContainer) return;

      window.addEventListener("scroll", () => this.handleScroll());
    }

    handleScroll() {
      if (this.isLoading || !DataFilter.hasMorePosts) return;

      const scrollPosition = window.innerHeight + window.scrollY;
      const triggerPosition =
        this.postsContainer.offsetHeight +
        this.postsContainer.offsetTop -
        this.triggerOffset;

      if (scrollPosition >= triggerPosition) {
        this.loadMorePosts();
      }
    }

    loadMorePosts() {
      this.isLoading = true;
      DataFilter.currentPage++;

      fetchPosts(
        getFetchBody({
          page: DataFilter.currentPage,
          scroll: "true",
        }),
      )
        .then((data) => {
          if (!data) {
            DataFilter.currentPage--;
            return;
          }
          if (data.includes("No more posts")) {
            DataFilter.hasMorePosts = false;
          } else {
            this.appendPosts(data);
          }
        })
        .catch((error) => {
          DataFilter.currentPage--;
          console.error("Error:", error);
        })
        .finally(() => {
          this.isLoading = false;
        });
    }

    appendPosts(data) {
      const tempDiv = document.createElement("div");
      tempDiv.innerHTML = data;
      const wrapper = tempDiv.querySelector("[data-catalog-count]");
      const source = wrapper || tempDiv;
      const newPosts = source.children;

      Array.from(newPosts).forEach((post) => {
        this.postsContainer.appendChild(post);
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      new InfiniteScroll(POST_CONTAINER_SELECTOR);
    });
  } else {
    new InfiniteScroll(POST_CONTAINER_SELECTOR);
  }
}

/* ── Category "Show more" toggle ── */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".js-catalogCatsToggle").forEach((btn) => {
    const extra = btn.closest(".catalog__sidebar__section").querySelector(".catalog__sidebar__extraCats");
    if (!extra) return;
    btn.addEventListener("click", () => {
      const isHidden = extra.hasAttribute("hidden");
      if (isHidden) {
        extra.removeAttribute("hidden");
        btn.textContent = btn.dataset.labelLess || "Приховати";
      } else {
        extra.setAttribute("hidden", "");
        btn.textContent = btn.dataset.labelMore || "Показати більше";
      }
    });
  });

  /* ── Mobile catalog sidebar toggle ── */
  const catalogSidebarEl = document.querySelector(".catalog__sidebar");

  function openCatalogSidebar() {
    if (!catalogSidebarEl) return;
    catalogSidebarEl.classList.add("is-open");
    document.querySelectorAll(".js-catalogFiltersToggle").forEach((b) =>
      b.classList.add("is-active")
    );
  }
  function closeCatalogSidebar() {
    if (!catalogSidebarEl) return;
    catalogSidebarEl.classList.remove("is-open");
    document.querySelectorAll(".js-catalogFiltersToggle").forEach((b) =>
      b.classList.remove("is-active")
    );
  }

  document.querySelectorAll(".js-catalogFiltersToggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      catalogSidebarEl?.classList.contains("is-open")
        ? closeCatalogSidebar()
        : openCatalogSidebar();
    });
  });

  document.querySelectorAll(".js-catalogFiltersHide").forEach((btn) => {
    btn.addEventListener("click", closeCatalogSidebar);
  });
});

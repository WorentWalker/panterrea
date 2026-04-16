<?php
/**
 * Template Name: Blog
 */

get_header();
?>

<main class="blog">
    <div class="container">
        <div class="forum__topBlock">
            <div class="forum__topBlock__left">
                <?php blog_breadcrumbs(); ?>
                <h1 class="blog__title">
                    <?php the_title(); ?>
                </h1>
                <div class="blog__subtitle">
                    <?php the_field('page_subtitle'); ?>
                </div>
            </div>
            <div class="forum__topBlock__right">
                <input id="searchInputForum" type="text" name="search"
                    placeholder="<?php _e('Пошук', 'panterrea_v1'); ?>" class="input input__searchForum body2"
                    aria-label="Search">
                <div class="btn__searchForum"></div>
            </div>
        </div>
        <div class="blog__content">
            <?php
            // Get default category ID (usually Uncategorized)
            $default_category_id = get_option('default_category');
            
            // Get all blog categories excluding Uncategorized
            $categories = get_categories([
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => true,
                'exclude' => [$default_category_id],
            ]);

            // Get selected category from URL or default to 'all'
            $selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'all';
            ?>

            <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
            <div class="blog__filters catalog__filters">
                <div class="blog__filter catalog__filter body2 <?php echo $selected_category === 'all' ? 'active' : ''; ?> js-blogFilter"
                    data-category="all">
                    <?php _e('Всі', 'panterrea_v1'); ?>
                </div>
                <?php foreach ($categories as $category) : ?>
                <div class="blog__filter catalog__filter body2 <?php echo $selected_category === $category->slug ? 'active' : ''; ?> js-blogFilter"
                    data-category="<?php echo esc_attr($category->slug); ?>">
                    <?php echo esc_html($category->name); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php
            // Query blog posts - exclude Uncategorized and posts without categories
            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => -1, // Load all posts for filtering
                'orderby' => 'date',
                'order' => 'DESC',
                'category__not_in' => [$default_category_id], // Exclude Uncategorized
                'tax_query' => [
                    [
                        'taxonomy' => 'category',
                        'operator' => 'EXISTS', // Posts must have at least one category
                    ],
                ],
            ];

            $blog_query = new WP_Query($query_args);
            ?>

            <div id="blogItems" class="blog__items catalog__items">
                <?php if ($blog_query->have_posts()) : ?>
                <?php while ($blog_query->have_posts()) : $blog_query->the_post(); 
                    $post_categories = get_the_category();
                    
                    // Filter out Uncategorized and posts without categories
                    $valid_categories = array_filter($post_categories, function($cat) use ($default_category_id) {
                        return $cat->term_id != $default_category_id;
                    });
                    
                    // Skip posts without valid categories
                    if (empty($valid_categories)) {
                        continue;
                    }
                    
                    $category_slugs = [];
                    foreach ($valid_categories as $cat) {
                        $category_slugs[] = $cat->slug;
                    }
                ?>
                <a href="<?php the_permalink(); ?>" class="blogItem catalogItem"
                    data-categories="<?php echo esc_attr(implode(' ', $category_slugs)); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('medium', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                    <?php else : ?>
                    <img class="blogItem__image"
                        src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/logo_green.svg'); ?>"
                        alt="<?php the_title(); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>

                    <div class="blogItem__desc catalogItem__desc">
                        <?php if (!empty($valid_categories)) : ?>
                        <div class="blogItem__categories">
                            <?php foreach ($valid_categories as $category) : ?>
                            <div class="blogItem__category body2">
                                <?php echo esc_html($category->name); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="blogItem__title catalogItem__title subtitle1">
                            <h3><?php the_title(); ?></h3>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
                <?php else : ?>
                <div class="blog__empty">
                    <p class="body2"><?php _e('Постів не знайдено', 'panterrea_v1'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('.js-blogFilter');
    const blogItems = document.getElementById('blogItems');
    const blogPosts = document.querySelectorAll('.blogItem');
    const searchInput = document.getElementById('searchInputForum');
    const searchButton = document.querySelector('.btn__searchForum');
    const POSTS_PER_PAGE = 8;
    let currentPostsShown = 0;
    let currentCategory = 'all';
    let currentSearchQuery = '';
    let isLoading = false;
    let filteredPosts = [];

    if (!filters.length || !blogItems) return;

    // Get initial category from URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialCategory = urlParams.get('category') || 'all';

    // Function to get filtered posts for current category and search query
    function getFilteredPosts(category, searchQuery = '') {
        const posts = [];
        const query = searchQuery.toLowerCase().trim();
        
        blogPosts.forEach(post => {
            const postCategories = post.getAttribute('data-categories').split(' ');
            const postTitle = post.querySelector('.blogItem__title h3').textContent.toLowerCase();
            
            // Check category filter
            const categoryMatch = category === 'all' || postCategories.includes(category);
            
            // Check search query
            const searchMatch = query === '' || postTitle.includes(query);
            
            // Add post if both filters match
            if (categoryMatch && searchMatch) {
                posts.push(post);
            }
        });
        return posts;
    }

    // Function to load more posts
    function loadMorePosts(animate = false) {
        if (isLoading) return;
        isLoading = true;

        const postsToShow = filteredPosts.slice(currentPostsShown, currentPostsShown + POSTS_PER_PAGE);

        if (postsToShow.length === 0) {
            isLoading = false;
            return;
        }

        postsToShow.forEach((post, index) => {
            post.style.display = '';
            if (animate) {
                post.classList.add('blogItem--fade-in');
                setTimeout(() => {
                    post.classList.remove('blogItem--fade-in');
                }, 300);
            }
        });

        currentPostsShown += postsToShow.length;
        isLoading = false;
    }

    // Function to filter and display posts
    function filterAndDisplayPosts(category, searchQuery = '', animate = false) {
        currentCategory = category;
        currentSearchQuery = searchQuery;
        currentPostsShown = 0;

        if (animate) {
            // First, fade out all visible posts
            blogPosts.forEach(post => {
                if (post.style.display !== 'none') {
                    post.classList.add('blogItem--fade-out');
                }
            });

            // After fade out, filter and fade in new posts
            setTimeout(() => {
                // Hide all posts first
                blogPosts.forEach(post => {
                    post.classList.remove('blogItem--fade-out');
                    post.style.display = 'none';
                });

                // Get filtered posts
                filteredPosts = getFilteredPosts(category, searchQuery);

                // Load first batch with animation
                loadMorePosts(true);

                // Show empty message if no posts visible
                let emptyMessage = blogItems.querySelector('.blog__empty');
                if (filteredPosts.length === 0) {
                    if (!emptyMessage) {
                        emptyMessage = document.createElement('div');
                        emptyMessage.className = 'blog__empty';
                        emptyMessage.innerHTML =
                            '<p class="body2"><?php echo esc_js(__('Постів не знайдено', 'panterrea_v1')); ?></p>';
                        blogItems.appendChild(emptyMessage);
                    }
                } else if (emptyMessage) {
                    emptyMessage.remove();
                }
            }, 300); // Wait for fade out animation
        } else {
            // Initial load without animation
            blogPosts.forEach(post => {
                post.classList.remove('blogItem--fade-out', 'blogItem--fade-in');
                post.style.display = 'none';
            });

            // Get filtered posts
            filteredPosts = getFilteredPosts(category, searchQuery);

            // Load first batch
            loadMorePosts(false);

            // Show empty message if no posts visible
            let emptyMessage = blogItems.querySelector('.blog__empty');
            if (filteredPosts.length === 0) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('div');
                    emptyMessage.className = 'blog__empty';
                    emptyMessage.innerHTML =
                        '<p class="body2"><?php echo esc_js(__('Постів не знайдено', 'panterrea_v1')); ?></p>';
                    blogItems.appendChild(emptyMessage);
                }
            } else if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }

    // Search functionality
    function performSearch() {
        const searchQuery = searchInput.value;
        filterAndDisplayPosts(currentCategory, searchQuery, true);
    }

    // Search button click handler
    if (searchButton) {
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            performSearch();
        });
    }

    // Search input Enter key handler
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        // Clear search on input clear
        searchInput.addEventListener('input', function(e) {
            if (this.value === '') {
                filterAndDisplayPosts(currentCategory, '', true);
            }
        });
    }

    // Infinite scroll handler
    function handleScroll() {
        if (isLoading || currentPostsShown >= filteredPosts.length) return;

        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.documentElement.scrollHeight - 500; // Load 500px before bottom

        if (scrollPosition >= threshold) {
            loadMorePosts(true);
        }
    }

    // Throttle scroll event for better performance
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            window.cancelAnimationFrame(scrollTimeout);
        }
        scrollTimeout = window.requestAnimationFrame(function() {
            handleScroll();
        });
    });

    // Apply initial filter without animation
    filterAndDisplayPosts(initialCategory, '', false);

    filters.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all filters
            filters.forEach(f => f.classList.remove('active'));
            // Add active class to clicked filter
            this.classList.add('active');

            const selectedCategory = this.getAttribute('data-category');

            // Update URL without page reload
            const url = new URL(window.location);
            if (selectedCategory === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', selectedCategory);
            }
            window.history.pushState({}, '', url);

            // Filter and display posts with animation (preserve current search query)
            filterAndDisplayPosts(selectedCategory, currentSearchQuery, true);
        });
    });
});
</script>

<?php get_footer(); ?>
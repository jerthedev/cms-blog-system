// CMS Blog System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize blog functionality
    initializeBlogSearch();
    initializeBlogInteractions();
});

/**
 * Initialize blog search functionality
 */
function initializeBlogSearch() {
    const searchForm = document.querySelector('.blog-search-form');
    if (!searchForm) return;

    const searchInput = searchForm.querySelector('input[name="q"]');
    if (!searchInput) return;

    // Add search suggestions or autocomplete here if needed
    searchInput.addEventListener('input', function() {
        // Debounce search suggestions
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            // Implement search suggestions if needed
        }, 300);
    });
}

/**
 * Initialize blog interactions
 */
function initializeBlogInteractions() {
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add copy link functionality for blog posts
    const copyLinkButtons = document.querySelectorAll('.copy-link-btn');
    copyLinkButtons.forEach(button => {
        button.addEventListener('click', function() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                // Show success message
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            });
        });
    });
}

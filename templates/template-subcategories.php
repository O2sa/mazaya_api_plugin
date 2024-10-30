<?php
// Load WordPress header
get_header();

// Retrieve category ID from query variable
$category_id = get_query_var('category_id', 1);

if ($category_id) {
    echo display_cards("categories/{$category_id}", 'categories','products');
} else {
    echo '<p>Invalid category ID.</p>';
}

// Load WordPress footer
get_footer();

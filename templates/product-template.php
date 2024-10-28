<?php
// Load WordPress header
get_header();

// Retrieve category ID from query variable
$product_id = get_query_var('product_id', 1);
$product = mazaya_api_request('products/' . $product_id)['product'];
// echo $product;
if ($product) {
    $price = $product['price'];
    $name = $product['name'];
    $imageUrl = $product['img'];
    $productId = $product['id'];
?>

    <div class="w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 p-4">
        <img class="p-2 rounded-t-lg" src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_html($name); ?>" />
        <div class="px-5 pb-5">
            <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo esc_html($name); ?></h5>
            <form method="POST">

                <div class="flex items-center justify-between mt-4">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white" id="price-<?php echo $productId; ?>">
                        $<span id="total-price-<?php echo $productId; ?>"><?php echo number_format($price, 2); ?></span>
                    </span>
                    <div class="flex items-center">


                        <?php wp_nonce_field('mazaya_add_order_action', 'mazaya_add_order_nonce'); ?>
                        <input hidden type="text" name="product_id"  value="<?php echo $productId; ?>">

                        <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-l" onclick="decrement('<?php echo $productId; ?>')">-</button>

                        <input type="text" name="qty" id="quantity-<?php echo $productId; ?>" value="1" min="1" class="w-16 text-center border border-gray-300 rounded mx-2" readonly>
                        <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-r" onclick="increment('<?php echo $productId; ?>', <?php echo $price; ?>)">+</button>

                    </div>
                </div>
                <button type="submit" name="mazaya_add_order" class="mt-4 w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
              إطلب الآن
                </button>
            </form>

        </div>
    </div>

    <script>
        function increment(productId, price) {
            const quantityInput = document.getElementById('quantity-' + productId);
            const totalPriceElement = document.getElementById('total-price-' + productId);
            let quantity = parseInt(quantityInput.value);
            quantity++;
            quantityInput.value = quantity;
            totalPriceElement.textContent = (quantity * price).toFixed(2);
        }

        function decrement(productId) {
            const quantityInput = document.getElementById('quantity-' + productId);
            const totalPriceElement = document.getElementById('total-price-' + productId);
            let quantity = parseInt(quantityInput.value);
            if (quantity > 1) {
                quantity--;
                quantityInput.value = quantity;
                const price = parseFloat(totalPriceElement.textContent) / (quantity + 1);
                totalPriceElement.textContent = (quantity * price).toFixed(2);
            }
        }
    </script>

<?php



} else {
    echo '<p>المنتج غير متوفر</p>';
}

// Load WordPress footer
get_footer();

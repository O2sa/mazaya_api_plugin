<?php
/*
Plugin Name: Mazaya API Integration
Description: Integrates with the Mazaya online store API.
Version: 1.0
Author: Osama Mabkhot
*/



if (!defined('ABSPATH')) {
    exit;
}



function mazaya_set_auth_cookie($token)
{
    // Set cookie parameters
    setcookie(
        'mazaya_auth_token', // Cookie name
        $token,               // Token value
        time() + (10 * YEAR_IN_SECONDS), // Expiration time (10 years)
        '/',                  // Path - '/' for entire domain
        '',                   // Domain - blank means current domain
        // true,                 // Secure - set to true if using HTTPS
        // true                  // HTTP-only - prevents JavaScript access
    );
}

function get_mazaya_token()
{
    return isset($_COOKIE['mazaya_auth_token']) ? $_COOKIE['mazaya_auth_token'] : null;
}
function check_if_autherize()
{
    return  get_mazaya_token() ? true : false;
}

function clear_cookie_if_exists()
{
    if (isset($_COOKIE['mazaya_auth_token'])) {
        setcookie("mazaya_auth_token", "", time() - 3600, "/");
        // Optionally, you can also unset it from the $_COOKIE superglobal
        unset($_COOKIE['mazaya_auth_token']);
    }
}

function mazaya_api_request($endpoint, $method = 'GET', $data = [])
{
    $url = 'https://store.mazaya-online.com/api/v1/' . $endpoint;
    // echo $url;
    // echo json_encode($data);

    $token = get_mazaya_token();
    $args = [
        'method' => $method,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ],
    ];

    if (!empty($data)) {
        $args['body'] = json_encode($data);
    }

    $response = wp_remote_request($url, $args);
    // echo json_encode($response);

    if (!is_wp_error($response)) {
        return json_decode(wp_remote_retrieve_body($response), true);
    } else         return ['error' => $response->get_error_message()];
}


// Handle login form submission
add_action('init', 'mazaya_handle_requests');


function check_nonce($action = '')
{
    return !isset($_POST[$action . '_nonce']) || !wp_verify_nonce($_POST[$action . '_nonce'], $action . '_action');
}



function handle_request($fields = [], $action = '', $action_name = "")
{
    // echo $action;
    // echo $action_name;
    if (check_nonce($action_name)) {
        return; // Nonce verification failed
    }
    $body = [];
    foreach ($fields as $item) {
        $body[$item] = sanitize_text_field($_POST[$item]);
    }

    $response = mazaya_api_request($action, "POST", $body);
    // echo json_encode($response);



    if ($response['status'] == 'error' || $response['code'] == '403' || $response['code'] == '401') {
        // echo $response['error'];

        handle_alert($response);
        wp_redirect(home_url());
        // exit;
    } else {
        // handle_alert($response);

        if ($action == 'login') {
            mazaya_set_auth_cookie($response['token']);
            if ($response['user']['enable_tow_fa_at']) {
                display_modal('المصادقة بخطوتين', mazaya_2fa_form());
            } else {
                wp_redirect(home_url());
            }
        } else if ($action == 'register') {
            mazaya_set_auth_cookie($response['token']);
            wp_redirect(home_url());
        } else if ($action == 'verify') {
            mazaya_set_auth_cookie($response['token']);
            wp_redirect(home_url());
        } else if ($action == 'generate') {
            display_modal('تفعيل المصادقة بخطوتين', mazaya_2fa_form($response, true));
        }

        // exit;
    }
}

function mazaya_handle_requests()
{

    $mazaya_actions = [
        'login' => 'mazaya_login',
        'register' => 'mazaya_register',
        'enable' => 'mazaya_enable_2fa',
        'disable' => 'mazaya_disable_2fa',
        'generate' => 'mazaya_generate_2fa',
        'verify' => 'mazaya_verify_2fa',
        'bills' => 'mazaya_add_order',
        'logout' => 'mazaya_logout',
    ];

    $mazaya_actions_fields = [
        'login' => ['password', 'username'],
        'register' => ['password', 'email', 'phone', 'name', 'address', 'username'],
        'enable' => ['token'],
        'disable' => ['token'],
        'generate' => [],
        'verify' => ['token'],
        'bills' => ['qty', 'product_id',],
    ];


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($mazaya_actions as $key => $value) {
            if (isset($_POST[$value])) {
                if (isset($_POST['mazaya_logout'])) {
                    clear_cookie_if_exists();
                    break;
                }
                handle_request($mazaya_actions_fields[$key], $key, $value);
                break;
            }
        }
    }
}



// Register shortcode for login and 2FA management
add_shortcode('user_management', 'user_management_shortcode');

function mazaya_user_profile()
{

    $token = get_mazaya_token();
    // Check if the user is logged in
    if (isset($token)) {
        // Fetch the user's profile data

        $profile_data = mazaya_api_request('profiles')['user'];
        if ($profile_data) {
            $output = display_user_profile($profile_data);

            return $output;
        }
    }
}







// Function to display Tailwind modal
function display_modal($title, $body)
{
    add_action('wp_footer', function () use ($title, $body) {
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: "<?php echo $title; ?>",
                    html: `<?php echo $body; ?>`,
                    showConfirmButton: false,

                });
            });
        </script>
    <?php
    });
}


// Handle alert using SweetAlert2
function handle_alert($response, $isError = true)
{
    add_action('wp_footer', function () use ($response, $isError) {
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: "<?php echo $response['status'] == 'error' ? $response['msg'] ?? "Error" : 'تمت العملية'; ?>",
                    icon: "<?php echo $response['status'] == 'error' ? 'error' : 'success'; ?>",
                    showConfirmButton: false,
                    toast: true,
                    position: 'top',
                    timer: 4000
                });
            });
        </script>
<?php
    });
}









// Enqueue SweetAlert2
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);
});


// Enqueue Tailwind CSS
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('tailwindcss', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
    // wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);

    // wp_enqueue_script('myplugin-alerts', plugin_dir_url(__FILE__) . 'alerts.js', ['jquery', 'sweetalert2'], null, true);
});



// Register rewrite rule and query var
function my_plugin_add_rewrite_rules()
{
    add_rewrite_rule('^subcategories/([0-9]+)/?$', 'index.php?sub_category_page=1&category_id=$matches[1]', 'top');
    add_rewrite_rule('^products/([0-9]+)/?$', 'index.php?sub_category_id=$matches[1]', 'top');
    add_rewrite_rule('^product/([0-9]+)/?$', 'index.php?product_id=$matches[1]', 'top');
}


add_action('init', 'my_plugin_add_rewrite_rules');

function my_plugin_add_query_vars($vars)
{
    $vars[] = 'sub_category_page';
    $vars[] = 'category_id';
    $vars[] = 'product_id';
    $vars[] = 'sub_category_id';
    return $vars;
}
add_filter('query_vars', 'my_plugin_add_query_vars');
// Custom template redirect for subcategories
function my_plugin_template_redirect()
{
    if (get_query_var('sub_category_page')) {
        include plugin_dir_path(__FILE__) . 'templates/subcategories-template.php';
        exit;
    }

    if (get_query_var('sub_category_id')) {
        include plugin_dir_path(__FILE__) . 'templates/products-template.php';
        exit;
    }
    if (get_query_var('product_id')) {
        include plugin_dir_path(__FILE__) . 'templates/product-template.php';
        exit;
    }
}
add_action('template_redirect', 'my_plugin_template_redirect');


include plugin_dir_path(__FILE__) . 'frontend/components.php';

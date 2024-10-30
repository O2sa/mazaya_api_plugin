<?php


function form_item($name = 'name', $label = 'الاسم', $type = 'text', $value = '', $classes = "", $placeholder = "", $required = true)
{
    ob_start(); ?>
    <div class="mb-5">
        <label for="<?php echo $label; ?>" class="mazaya_label block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?php echo $label; ?></label>
        <input type="<?php echo $type; ?>" value="<?php echo $value; ?>" id="<?php echo $label; ?>" name="<?php echo $name; ?>" placeholder="<?php echo $placeholder; ?>" required="<?php echo $required; ?>" class="mazaya_input shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light <?php echo $classes; ?>" />
    </div>
<?php
    return ob_get_clean();
}


function mazaya_btn($name = 'submit', $label = 'أرسال', $classes = "")
{
    ob_start(); ?>
    <button type="submit" name="<?php echo $name; ?>" class="mazaya_button text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 <?php echo $classes ?>">
        <?php echo $label; ?></button>
<?php
    return ob_get_clean();
}


function mazaya_registre_form()
{

    $items = [
        [
            "name" => "name",
            "label" => "الأسم",
            "type" => "text",
        ],
        [
            "name" => "phone",
            "label" => "الهاتف",
            "type" => "text",
        ],
        [
            "name" => "address",
            "label" => "العنوان",
            "type" => "text",
        ],
        [
            "name" => "username",
            "label" => "اسم المستخدم",
            "type" => "text",
        ],
        [
            "name" => "email",
            "label" => "الإيميل",
            "type" => "email",
        ],
        [
            "name" => "password",
            "label" => "كلمة السر",
            "type" => "password",
        ],

    ];
    return    mazaya_form('mazaya_register', $items, 'إنشاء');
}


function mazaya_login_form()
{
    $items = [
        [
            "name" => "username",
            "label" => "اسم المستخدم",
            "type" => "email",
        ],
        [
            "name" => "password",
            "label" => "كلمة السر",
            "type" => "password",
        ],

    ];
    return    mazaya_form('mazaya_login', $items, 'تسجيل');
}


function mazaya_form($name = '', $items = [], $btn_label = 'submit')
{
    ob_start(); ?>
    <div class="mazaya_form max-w-sm mx-auto">
        <form id="<?php echo $name; ?>" method="POST" class="space-y-4">
            <?php wp_nonce_field($name . '_action', $name . '_nonce'); ?>
            <?php foreach ($items as $val) {
                echo form_item($val['name'], $val['label'], $val['type'], $val['value'] ?? "");
            }; ?>
            <?php
            echo mazaya_btn($name, $btn_label);
            ?>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
function mazaya_display_orders()
{
    $cates = mazaya_api_request('bills')['orders'];

    if ($cates) {
        ob_start(); ?>

        <div class="mazaya_orders relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            اسم المنتج
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                السعر

                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                الكمية

                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <div class="flex items-center">
                                الحالة

                            </div>
                        </th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cates as $val) { ?>

                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <?php echo $val['name']; ?>
                            </th>
                            <td class="px-6 py-4">
                                <?php echo $val['price']; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo $val['qty']; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo '$' . $val['status_label']; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php
        return  ob_get_clean();
    }

    // return null;
}


function mazaya_webhook_form()
{
    $items = [
        [
            "name" => "url",
            "label" => "أدخل رابط تفعيل webhook",
            "type" => "text",
        ],
    ];
    return    mazaya_form('mazaya_add_webhook', $items, 'حفظ');
}

function mazaya_logout_form()
{

    return    mazaya_form('mazaya_logout', [], 'تسجيل خروج');
}






function mazaya_categories()
{
    return display_cards('categories');
}

function display_cards($endpoint = 'categories', $subitem = null, $child_page = 'subcategories', $with_btn = true, $bank_card = false)
{

    $cates = mazaya_api_request($endpoint)[$subitem ? $subitem : $endpoint];

    $output = '';
    if ($cates) {
        ob_start(); ?>
        <div class="mazaya_cards grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($cates as $val) {
                echo card_html($val, $child_page, $with_btn, $bank_card);
            } ?>
        </div>

    <?php $output = ob_get_clean();
        return $output;
    }

    return null;
}



function mazaya_banks()
{
    return display_cards('banks', 'banks', null, false, true);
}
function mazaya_groups()
{
    return display_cards('groups', 'groups', null, false);
}
function copy_bank_wallet($data, $bank_card)
{

    if ($bank_card) {

        ob_start();
    ?>
        <div class="mt-4 ">
            <label for="<?php echo 'bank-' . $data['id']; ?>" class="text-sm font-medium text-gray-900 dark:text-white mb-2 block"><?php echo $data['description']; ?></label>
            <input id="<?php echo 'bank-' . $data['id']; ?>" type="text" class="col-span-6 bg-gray-50 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $data['wallet_id']; ?>" disabled readonly>
        </div>

    <?php
        return ob_get_clean();
    } else return null;
}

function card_html($data, $page = 'categories', $with_btn = true, $bank_card = false)
{
    // Use isset to check if 'price', 'img', and 'name' exist and have valid values
    $price = isset($data['price']) ? $data['price'] : '';
    $imageUrl = isset($data['img']) ? esc_js($data['img']) : 'default-image-url.jpg'; // Fallback image
    $name = isset($data['name']) ? esc_js($data['name']) : 'المنتج غير متوفر'; // Fallback name
    ob_start();
    ?>
    <div class="mazaya_card w-full max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <img class="p-8 rounded-t-lg" src="<?php echo $imageUrl; ?>" alt="product image" />
        <div class="px-5 pb-5">
            <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white"><?php echo $name; ?></h5>
            <div class="<?php echo $price ? 'flex' : ''; ?> mt-4 text-center items-center justify-between">
                <span class="<?php echo $price ? '' : 'block w-100'; ?> text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo $price ? '$' . esc_js($price) : ''; ?>
                </span>
                <a href="<?php echo $page ? home_url() . '/' . $page . '/' . $data['id'] : ''; ?>" class="<?php echo $with_btn ? '' : 'hidden'; ?> text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">إستعراض</a>
                <?php echo copy_bank_wallet($data, $bank_card) ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}







// Function to display the login form


// Function to display user profile
function display_user_profile($profile_data)
{
    ob_start(); ?>

    <dl class="mazaya_profile max-w-md text-gray-900 divide-y divide-gray-200 dark:text-white dark:divide-gray-700">
        <div class="flex flex-col pb-3">
            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">الأسم</dt>
            <dd class="text-lg font-semibold"><?php echo esc_html($profile_data['name']); ?></dd>
        </div>
        <div class="flex flex-col pb-3">
            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">البريد الإلكتروني</dt>
            <dd class="text-lg font-semibold"><?php echo esc_html($profile_data['email']); ?></dd>
        </div>
        <div class="flex flex-col py-3">
            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">العنوان</dt>
            <dd class="text-lg font-semibold"><?php echo esc_html($profile_data['address']); ?></dd>
        </div>
        <div class="flex flex-col pt-3">
            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">رقم الهاتف</dt>
            <dd class="text-lg font-semibold"><?php echo esc_html($profile_data['phone']); ?></dd>
        </div>
        <div class="flex flex-col pt-3">
            <dt class="mb-1 text-gray-500 md:text-lg dark:text-gray-400">المصادقة بخطوتين</dt>
            <dd class="text-lg font-semibold"><?php echo display_2fa_form($profile_data['enable_tow_fa_at']); ?></dd>
        </div>
    </dl>

<?php return ob_get_clean();
}

// Function to display 2FA form
function display_2fa_form($two_fa_enabled)
{
    // Check if 2FA is enabled
    if ($two_fa_enabled) {
        return    mazaya_form('mazaya_disable_2fa', [
            [
                "name" => "token",
                "label" => "أدخل رمز التحقق بخطوتين للإلغاء",
                "type" => "text",
            ],
        ], 'إلغاء');
    } else {
        return    mazaya_form('mazaya_generate_2fa', [], 'Submit');
    }
}



function two_fa_qr($response)
{
    ob_start(); ?>
    <div class="mazaya-2fa-generate">
        <h2>قم بمسح الكود التالي بأستخدم تطبيق المصادقة</h2>
        <img class="m-auto" src="<?php echo esc_attr($response['google2fa_url'] ?? '') ?>" alt="QR Code" id="mazaya-qr-code">
        <p><strong>2FA Secret:</strong> <?php echo  esc_html($response['secret'] ?? '') ?></p>
        <p>استخدم هذا الكود إذا لم تكن قادرا على مسح الصورة</p>
    </div>

<?php return ob_get_clean();
}


function mazaya_2fa_form($response = [], $enable = false)
{
    // $response = mazaya_api_request('generate', 'POST');
    ob_start(); ?>

    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
        <div class="">
            <?php echo $enable ? two_fa_qr($response) : null  ?>
            <?php echo mazaya_form($enable ? 'mazaya_enable_2fa' : 'mazaya_verify_2fa', [
                [
                    "name" => "token",
                    "label" => "أدخل رمز التحقق بخطوتين",
                    "type" => "text",
                ],
            ], 'حفظ'); ?>
        </div>
    </div>

<?php return ob_get_clean();
}


function my_modal($response = [], $enable = false)
{
    // $response = mazaya_api_request('generate', 'POST');
    ob_start(); ?>
    <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="">
                            <?php echo $enable ? two_fa_qr($response) : null  ?>
                            <?php echo mazaya_form($enable ? 'mazaya_enable_2fa' : 'mazaya_verify_2fa', [
                                [
                                    "name" => "token",
                                    "label" => "أدخل رمز التحقق بخطوتين للتفعيل:",
                                    "type" => "text",
                                ],
                            ], 'حفط'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php return ob_get_clean();
}



function data_display()
{


    $output = mazaya_user_profile();
    // $output .= mazaya_generate_2fa_shortcode();
    $output .= mazaya_categories();
    $output .= mazaya_webhook_form();
    $output .= mazaya_display_orders();
    $output .= mazaya_groups();
    $output .= mazaya_banks();
    $output .= mazaya_logout_form();
    $output .= mazaya_registre_form();
    return $output;
}

add_shortcode('mazaya_display_orders', 'mazaya_display_orders');
add_shortcode('mazaya_webhook_form', 'mazaya_webhook_form');
add_shortcode('mazaya_banks', 'mazaya_banks');
add_shortcode('mazaya_logout_form', 'mazaya_logout_form');
add_shortcode('mazaya_groups', 'mazaya_groups');
add_shortcode('mazaya_categories', 'mazaya_categories');
add_shortcode('mazaya_login_form', 'mazaya_login_form');
add_shortcode('mazaya_registre_form', 'mazaya_registre_form');
add_shortcode('data_display', 'data_display');
add_shortcode('mazaya_user_profile', 'mazaya_user_profile');

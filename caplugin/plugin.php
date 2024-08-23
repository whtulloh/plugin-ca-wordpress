<?php
/**
 * Plugin Name: CA Plugin
 * Description: Storing function to connect with kms REST API, Case API and Community Rest API
 **/

function caplugin_register_settings()
{
    register_setting("caplugin_options_group", "caplugin_option_ca_url");
    register_setting("caplugin_options_group", "caplugin_option_ca_tenant");
    register_setting("caplugin_options_group", "caplugin_option_ca_scope");
    register_setting("caplugin_options_group", "caplugin_option_kms_username");
    register_setting("caplugin_options_group", "caplugin_option_kms_password");
    register_setting("caplugin_options_group", "caplugin_option_cc_username");
    register_setting("caplugin_options_group", "caplugin_option_cc_password");
}

add_action("admin_init", "caplugin_register_settings");

function caplugin_setting_page()
{
    add_options_page(
        "CA Plugin",
        "CA Config",
        "manage_options",
        "vkms-api",
        "caplugin_options_page"
    );
}
add_action("admin_menu", "caplugin_setting_page");

function caplugin_options_page()
{
    ?>
  <div>
  <?php screen_icon(); ?>
  <h1>CA Endpoint Integration Setting</h1>
  <form method="post" action="options.php">
  <?php settings_fields("caplugin_options_group"); ?>
  <p>API URL Result: example.com/wp-json/ca/v1/ca-action/[action_id]</p>
  <h2>CA Configuration</h2>
  <table>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_ca_url">CA URL: </label></th>
                <td><input type="text" id="caplugin_option_ca_url_id" name="caplugin_option_ca_url" value="<?php echo get_option(
                    "caplugin_option_ca_url"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_ca_tenant">CA Tenant: </label></th>
                <td><input type="text" id="caplugin_option_ca_tenant_id" name="caplugin_option_ca_tenant" value="<?php echo get_option(
                    "caplugin_option_ca_tenant"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_ca_scope">CA Scope: </label></th>
                <td><input type="text" id="caplugin_option_ca_scope_id" name="caplugin_option_ca_scope" value="<?php echo get_option(
                    "caplugin_option_ca_scope"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_kms_username">kms Enterprise Username: </label></th>
                <td><input type="text" id="caplugin_option_kms_username_id" name="caplugin_option_kms_username" value="<?php echo get_option(
                    "caplugin_option_kms_username"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_kms_password">kms Enterprise Password: </label></th>
                <td><input type="password" id="caplugin_option_kms_password_id" name="caplugin_option_kms_password" value="<?php echo get_option(
                    "caplugin_option_kms_password"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_cc_username">Create Case Username: </label></th>
                <td><input type="text" id="caplugin_option_cc_username_id" name="caplugin_option_cc_username" value="<?php echo get_option(
                    "caplugin_option_cc_username"
                ); ?>" /></td>
        </tr>
        <tr valign="top">
                <th scope="row"><label for="caplugin_option_cc_password">Create Case Password: </label></th>
                <td><input type="password" id="caplugin_option_kms_password_id" name="caplugin_option_cc_password" value="<?php echo get_option(
                    "caplugin_option_cc_password"
                ); ?>" /></td>
        </tr>
  </table>
  <p>
        <br/><strong>Action ID:</strong>
        <br/>1: KMS - Search Article. URL Params: searchText.
        <br/>2: KMS - View Article. URL Params: contentID, locale, version, type.
        <br/>3: KMS - Featured Content.
        <br/>4: KMS - Search Article By Tag. URL Params: tag.
        <br/>5: KMS - Search Tag By Tag. URL Params: tag.
        <br/>6: Case Management - Create Case for logged in customer. URL Params: caseTypeName.
  </p>
  <br/>
  <?php submit_button(); ?>
  </form>
  </div>
<?php
}

$global_user = "";

add_action("rest_api_init", function () {
    // when WP sets up the REST API
    $GLOBALS["global_user"] = wp_get_current_user();

    register_rest_route(
        // tell it we want an endpoint
        "ca/v1",
        "/ca-action/(?P<id>\d+)", // at example.com/wp-json/ca/v1/ca-action/<action_id>
        [
            "methods" => "GET", // that it handles GET requests
            "callback" => "ca_action_endpoint", // and calls this function when hit
        ]
    );
});

function ca_action_endpoint($data)
{
    $workforceUrl = get_option("caplugin_option_ca_url");
    $workforceTenant = get_option("caplugin_option_ca_tenant");
    $workforceScope = get_option("caplugin_option_ca_scope");
    $workforceUsername = get_option("caplugin_option_kms_username");
    $workforcePassword = get_option("caplugin_option_kms_password");
    $ccUsername = get_option("caplugin_option_cc_username");
    $ccPassword = get_option("caplugin_option_cc_password");

    $action = $data["id"];

    switch ($action) {
        case "1":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $workforceUsername,
                $workforcePassword
            );
            echo searchArticle($workforceUrl, $auth, $workforceTenant);
            break;
        case "2":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $workforceUsername,
                $workforcePassword
            );
            echo viewArticle($workforceUrl, $auth, $workforceTenant);
            break;
        case "3":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $workforceUsername,
                $workforcePassword
            );
            echo featuredContent($workforceUrl, $auth, $workforceTenant);
            break;
        case "4":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $workforceUsername,
                $workforcePassword
            );
            echo searchArticleByTag($workforceUrl, $auth, $workforceTenant);
            break;
        case "5":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $workforceUsername,
                $workforcePassword
            );
            echo searchTagByTag($workforceUrl, $auth, $workforceTenant);
            break;
        case "6":
            $auth = oidcAuth(
                $workforceUrl,
                $workforceTenant,
                $workforceScope,
                $ccUsername,
                $ccPassword
            );
            echo createCase($workforceUrl, $auth, $workforceTenant);
            break;
    }
}

function oidcAuth(
    $workforceUrl,
    $workforceTenant,
    $workforceScope,
    $workforceUsername,
    $workforcePassword
) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/oidc-token-service/" .
            $workforceTenant .
            "/token?grant_type=password&password=" .
            $workforcePassword .
            "&username=" .
            $workforceUsername .
            "&client_id=" .
            $workforceTenant .
            "&scope=" .
            $workforceScope,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response);
}

function searchArticle($workforceUrl, $auth, $workforceTenant)
{
    // action 1
    $searchText = $_GET["searchText"];
    $tag = isset($_GET["tag"]) ? $_GET["tag"] : "*";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/kms-search-service/" .
            $workforceTenant .
            "/search?query=" .
            urlencode($searchText) .
            "&category=vkms:FAQCategory&category=vkms:ArticleCategory&category=vkms:AlertCategory&size=100&start=0&tag=" .
            urlencode($tag),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function viewArticle($workforceUrl, $auth, $workforceTenant)
{
    // action 2
    $contentID = $_GET["contentID"];
    $locale = $_GET["locale"];
    # $version = $_GET['version'];
    $type = $_GET["type"];

    $curl = curl_init();
    if ($type == "Spidered") {
        $url =
            $workforceUrl .
            "/kms-content-service/" .
            $workforceTenant .
            "/content/vkms:SpideredContent/" .
            $contentID .
            "/" .
            $locale;
    } else {
        $url =
            $workforceUrl .
            "/kms-content-service/" .
            $workforceTenant .
            "/content/vkms:AuthoredContent/" .
            $contentID .
            "/" .
            $locale .
            "?version=";
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function featuredContent($workforceUrl, $auth, $workforceTenant)
{
    // action 3
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/kms-search-service/" .
            $workforceTenant .
            "/search?featured=vkms:MatchOnly&start=0&size=100",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function searchArticleByTag($workforceUrl, $auth, $workforceTenant)
{
    // action 4
    $tag = isset($_GET["tag"]) ? $_GET["tag"] : "topic_baggagefees";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/kms-search-service/" .
            $workforceTenant .
            "/search?tag=" .
            $tag .
            "&start=0&size=100",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function searchTagByTag($workforceUrl, $auth, $workforceTenant)
{
    // action 5
    $tag = isset($_GET["tag"]) ? $_GET["tag"] : "topic_reservations";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/kms-tag-service/" .
            $workforceTenant .
            "/tag/" .
            $tag .
            "?start=0&size=100",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    echo $response;
}

function createCase($workforceUrl, $auth, $workforceTenant)
{
    // action 6
    $caseTypeName = isset($_GET["caseTypeName"])
        ? $_GET["caseTypeName"]
        : "vcs:NewInternetServiceOrder";
    $email = $GLOBALS["global_user"]->user_email;
    // echo $email;

    // Find customer based on their WordPress username
    $data = [
        "query" => [
            "emailAddress" => $email,
        ],
    ];

    $encodedData = json_encode($data);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl .
            "/customer-service-v2/" .
            $workforceTenant .
            "/search",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $encodedData,
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
            "Content-Type: application/json",
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    // echo $response;

    // Use customer ID from response in request to create case.
    $json = json_decode($response, true);
    $customerId = $json["items"][0]["reference"];
    // echo $customerId;

    $data = [
        "@type" => $caseTypeName,
        "summary" => "",
        "notes" => [
            "@type" => "vcs:Note",
            "content" => "New case created via website.",
        ],
        "associatedCustomers" => [
            "@type" => "vcs:CustomerAssociation",
            "isPrimary" => true,
            "vcust:identifier" => $customerId,
        ],
    ];

    $encodedData = json_encode($data);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>
            $workforceUrl . "/case-service/" . $workforceTenant . "/cases",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $encodedData,
        CURLOPT_HTTPHEADER => [
            "Authorization: OIDC_id_token " . $auth->access_token,
            "Content-Type: application/ld+json",
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    echo $response;
    // $json = json_decode($response, true);
    // $caseId = $json["identifier"];
    // echo $caseId;
}

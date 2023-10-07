<?php

include "CyUtils.php";

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

echo " ______ ____    ____.______     _______. ______      .__   __.   ______
/      |\   \  /   / |   _  \  |   ____||   _  \     |  \ |  |  /  __  \
| ,----' \   \/   /  |  |_)  | |  |__   |  |_)  |    |   \|  | |  |  |  |
| |       \_    _/   |   _  <  |   __|  |      /     | . `   | |  |  |  |
| `----.    |  |     |  |_)  | |  |____ |  |\  \----.|  |\   | |  `--'  |
\______|    |__|     |______/  |_______|| _| `._____||__| \__|  \______/
\n";
// Get user input
$server_address = readline("Please insert API server address [Default=https://multiscannerdemo.cyberno.ir/]: ");
if ($server_address === "") {
    $server_address = "https://multiscannerdemo.cyberno.ir/";
}
if(!endsWith($server_address, "/")){
	$server_address = $server_address . "/";
}
$cyutils = new CyUtils($server_address);
$username = readline("Please insert identifier (email): ");
$password = readline("Please insert your password: ");

// Log in
$login_response = $cyutils->call_with_json_input('user/login', ["email" => $username, "password" => $password]);
$cyutils->check_response_result($login_response);
$apikey = $login_response->data;

echo "Please select scan mode:" . PHP_EOL;
printf("1- Scan local folder\n");
printf("2- Scan file\n");
$index = readline("Enter Number=");
if ($index === "1") {
    // Initialize scan
    $file_path = readline("Please enter the paths of file to scan (with spaces): ");
    $avs = readline("Enter the name of the selected antivirus (with spaces): ");
    $file_path = explode(" ", $file_path);
    $avs = explode(" ", $avs);
    $scan_response = $cyutils->call_with_json_input('scan/init', ['token' => $apikey, 'avs' => $avs, 'paths' => $file_path]);
    $cyutils->check_response_result($scan_response);
} else {
    // Initialize scan
    $file_path = readline("Please enter the path of file to scan: ");
    $avs = readline("Enter the name of the selected antivirus (with spaces): ");
    $scan_response = $cyutils->call_with_form_input('scan/multiscanner/init', ["token" => $apikey, "avs" => $avs], 'file', $file_path);
    $cyutils->check_response_result($scan_response);
}

$guid = $scan_response->guid;
// Check Password  in Path Address
if ($scan_response->password_protected) {
    foreach ($scan_response->password_protected as $item) {
        $password = readline(sprintf("|Enter the Password file -> %s |: ", $item));
        $scan_extract_response = $cyutils->call_with_json_input(sprintf('scan/extract/%s', $guid), ['token' => $apikey, 'path' => $item, 'password' => $password]);
        if ($scan_extract_response->success === false) {
            echo $cyutils->get_error($scan_extract_response);
        }
    }
}
print("=========  Start Scan ===========" . PHP_EOL );
$scan_start_response = $cyutils->call_with_json_input(sprintf('scan/start/%s', $guid), ['token' => $apikey]);
$cyutils->check_response_result($scan_start_response);
// Wait for scan results
if ($scan_response->success === true) {
    $is_finished = false;
    while (!$is_finished) {
        print("Waiting for result..." . PHP_EOL);
        $scan_result_response = $cyutils->call_with_json_input(sprintf('scan/result/%s', $guid), array('token' => $apikey));
        if (isset($scan_result_response->data->finished_at)) {
            $is_finished = true;
            print(json_encode($scan_result_response->data, JSON_PRETTY_PRINT));
        }
        sleep(5);
    }
} else {
    print($cyutils->get_error($scan_response));
    exit(0);
}

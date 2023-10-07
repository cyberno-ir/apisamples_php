<?php


class CyUtils
{
    private $server_address;
	const USER_AGENT = 'Cyberno-API-Sample-PHP';

    public function __construct($server_address)
    {
        $this->server_address = $server_address;
    }

    public static function get_sha256($file_path)
    {
        $hash_sha256 = hash_file('sha256', $file_path);
        return $hash_sha256;
    }

    public static function get_error($return_value)
    {
        $error = "Error!\n";
        if (isset($return_value->error_code)) {
            $error .= ("Error code: " . $return_value->error_code . "\n");
        }
        if (isset($return_value->error_desc)) {
            $error .= ("Error description: " . $return_value->error_desc . "\n");
        }
        return $error;
    }

    public function call_with_json_input($api, $json_input)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->server_address . $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
			CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($json_input),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function call_with_form_input($api, $data_input, $file_param_name, $file_path)
    {
        $file_type = filetype($file_path);
        $filename = basename($file_path);
        $data = [];
        foreach ($data_input as $key => $item) {
            $data[$key] = $item;
        }
        $data[$file_param_name] = new CurlFile($file_path, $file_type, $filename);
        // curl connection
        $ch = curl_init();
        // set curl url connection
        $curl_url = $this->server_address . $api;
        // pass curl url
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        // image upload Post Fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // set CURL ETURN TRANSFER type
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);


    }

    function clear_screen()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
        } else {
            system('clear');
        }
    }


    function check_response_result($response)
    {
        if ($response->success == false) {
            echo $this->get_error($response);
            exit(0);
        }
        $this->clear_screen();
    }


}
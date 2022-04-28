<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
	public function __construct()
	{
		helper(["url"]);
	}

    function strhex($string) {
		$hexstring = unpack('H*', $string);
		return array_shift($hexstring);
    }
 
    function getRandomString($length = 8) {
        $validCharacters = "1234567890abcdefABCDEF";
        $validCharNumber = strlen($validCharacters);
    
        $result = "";
    
        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, $validCharNumber - 1);
            $result .= $validCharacters[$index];
        }
    
        return $result;
    }
    
    function newdecryptdata($content){
        $encryption_key1 = '1234567890123456';
        $encryption_key = strhex($encryption_key1);
        $content = base64_decode($content);
        $iv1 = substr($content,0,8);
        $iv = strhex($iv1);
        $contentdata = substr($content,8);
    
        $hasilnyaaa = openssl_decrypt($contentdata, 'aes-128-cbc', $encryption_key, 0, $iv);
        return $hasilnyaaa;
    }
    
    function newencryptdata($content){
        $encryption_key1 = '1234567890123456';
        $encryption_key = $this->strhex($encryption_key1);
        $iv1 = $this->getRandomString(8);
        $iv = $this->strhex($iv1);
    
        $encrypted = openssl_encrypt($content, 'aes-128-cbc', $encryption_key, 0 , $iv);
        
        $hasilnyaaa = base64_encode($iv1.''.$encrypted);
        return $hasilnyaaa;
    }

	public function addUser()
	{
		// layout of add user form
        $data['myClass'] = $this;
		return view('add-user',$data);
	}

    public function saveUser2()
	{
        $response = [
            'success' => false,
            'msg' => "There are some validation errors",
        ];

        return $this->response->setJSON($response);
    }

	public function saveUser()
	{
		if ($this->request->getMethod() == "post") {

			$rules = [
				"ran_id" => "required"
			];

			if (!$this->validate($rules)) {

				$response = [
					'success' => false,
					'msg' => "There are some validation errors",
				];

				return $this->response->setJSON($response);
			} else {
                $ran_id = $this->request->getVar("ran_id");
                $act = $this->newencryptdata('replace'); //new or replace
                $act = str_replace('=', '', $act);
                $rid = $this->newencryptdata($ran_id);
                $rid = str_replace('=', '', $rid);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://localhost:8081/init?act=$act&id=$rid");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                $rida = $this->newencryptdata($ran_id);
                $rida = str_replace('=', '', $rida);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://localhost:8081/$output");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'id='.$rida);
                $outputya = curl_exec($ch);
                $status = str_replace(' ', '', $outputya);
                if($status == 6300){
                    $response = [
                        'success' => true,
                        'msg' => "Failed to Pairing",
                    ];
                }
                else{
                    $act = $this->newencryptdata('readNFC'); //new or replace
                    $act = str_replace('=', '', $act);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://localhost:8081/read?act=$act");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $outputya = curl_exec($ch);
                    //$status = substr($outputya,0,6);
                    $status = str_replace(' ', '', $outputya);
                    $pecahinya23 = substr($status,0,4);
                    $pecahinya2 = substr($outputya,6,12);
                    $nospasi = trim(str_replace(' ', '', $pecahinya2));
                    $userModel = new UserModel();

                    $data = [
                        "ran_id" => $ran_id,
                        "kartu_tag_no" => $nospasi,
                    ];

                    if ($userModel->insert($data)) {

                        $response = [
                            'success' => true,
                            'msg' => "Pairing Success",
                        ];
                    } else {
                        $response = [
                            'success' => true,
                            'msg' => "Pairing Succes But Failed Input DB",
                        ];
                    }

                    return $this->response->setJSON($response);
                }
                
			}
		}
	}
    
}
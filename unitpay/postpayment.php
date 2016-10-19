<?php 

	//file which has required functions
	require("functions.php");
	require("config.php");

	srand((double)microtime()*1000000);
	$rkey = rand();


	$checksum =generateChecksum($transId,$sellingCurrencyAmount,$accountingCurrencyAmount,$status, $rkey,$key);

	function callbackHandler($data)
	{
	    $method = '';
	    $params = array();
	    if ((isset($data['params'])) && (isset($data['method'])) && (isset($data['params']['signature']))){
	        $params = $data['params'];
	        $method = $data['method'];
	        $signature = $params['signature'];
	        if (empty($signature)){
	            $status_sign = false;
	        }else{
	            $status_sign = verifySignature($params, $method);
	        }
	    }else{
	        $status_sign = false;
	    }
	//    $status_sign = true;
	    if ($status_sign){
	        switch ($method) {
	            case 'check':
	                $result = check( $params );
	                break;
	            case 'pay':
	                $result = pay( $params );
	                break;
	            case 'error':
	                $result = $this->error( $params );
	                break;
	            default:
	                $result = array('error' =>
	                    array('message' => 'неверный метод')
	                );
	                break;
	        }
	    }else{
	        $result = array('error' =>
	            array('message' => 'неверная сигнатура')
	        );
	    }
	    hardReturnJson($result);
	}
	function check( $params )
	{

        $result = array('result' =>
            array('message' => 'Запрос успешно обработан')
        );

	    return $result;
	}
	function pay( $params )
	{


		$data = explode('|', $params['account']);

		$redirectUrl = $data[0];  // redirectUrl received from foundation
		$transId = $data[1];		 //Pass the same transid which was passsed to your Gateway URL at the beginning of the transaction.
		$sellingCurrencyAmount = $data[2];
		$accountingCurrencyAmount = $data[3];
		global $rkey, $checksum;

		$url = $redirectUrl . '?' . 'transid=' . $transId . '&status=Y' . '&rkey=' . $rkey . '&checksum=' . $checksum . '&sellingamount=' . $sellingCurrencyAmount . '&accountingamount=' . $accountingCurrencyAmount;


		if( $curl = curl_init() ) {
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			
			$out = curl_exec($curl);

			if ($out == false){
				$result = array('result' =>
            		array('message' => 'Ошибка curl: ' . curl_error($curl))
        		);
				
			}else{
				$result = array('result' =>
            	array('message' => 'Запрос успешно обработан')
        	);
			}
			curl_close($curl);

			

		}else{
			$result = array('error' =>
	            array('message' => 'Не могу инициализировать curl')
	        );
		}

	    return $result;
	}
	function error( $params )
	{
        $result = array('result' =>
            array('message' => 'Запрос успешно обработан')
        );

	    return $result;
	}
	function getSignature($method, array $params, $secretKey)
	{
	    ksort($params);
	    unset($params['sign']);
	    unset($params['signature']);
	    array_push($params, $secretKey);
	    array_unshift($params, $method);
	    $str = join('{up}', $params);
	    return hash('sha256', $str);
	}
	function verifySignature($params, $method)
	{
		global $secret_key;
	    $secret = $secret_key;
	    $signature = getSignature($method, $params, $secret);
	    return $params['signature'] == $signature;
	}
	function hardReturnJson( $arr )
	{
	    header('Content-Type: application/json');
	    $result = json_encode($arr);
	    die($result);
	}

	callbackHandler($_GET);

?>
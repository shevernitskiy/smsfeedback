<?php

class smsfeedbackSMS extends waSMSAdapter
{

    /**
     * @return array
     */
    public function getControls()
    {
        return array(
            'host' => array(
                'title'       => 'Host',
                'description' => 'Введите url, например api.smsfeedback.ru',
            ),
            'port' => array(
                'title'       => 'Port',
                'description' => 'Введите порт, например 80',
            ),
            'login' => array(
                'title'       => 'Login',
                'description' => '',
            ),
            'password' => array(
                'title'       => 'Password',
                'description' => '',
                'control_type' => waHtmlControl::PASSWORD,
            ),                            
        );
    }

    /**
     * @param string $to
     * @param string $text
     * @param string $from
     * @return mixed
     */
    public function send($to, $text, $from = null)
    {
        $params = array(
            "host" => $this->getOption('host'),
            "port" => $this->getOption('port'),
            "login" => $this->getOption('login'),
            "password" => $this->getOption('password'),
            "from" => $from,                                 
            "to"     => $this->smsCleanNum($to),
            "text"   => $text
        );
        
        $result = $this->smsWrapper(
            $params['host'],
            $params['port'],
            $params['login'],
            $params['password'],
            $params['to'],
            $params['text'],
            $params['from']
        );

        return $result;
    }

    public function smsSend($host, $port, $login, $password, $phone, $text, $sender = false, $wapurl = false )
    {
        $fp = fsockopen($host, $port, $errno, $errstr);
        if (!$fp) {
            return "errno: $errno \nerrstr: $errstr\n";
        }
        fwrite($fp, "GET /messages/v2/send/" .
            "?phone=" . rawurlencode($phone) .
            "&text=" . rawurlencode($text) .
            ($sender ? "&sender=" . rawurlencode($sender) : "") .
            ($wapurl ? "&wapurl=" . rawurlencode($wapurl) : "") .
            "  HTTP/1.0\n");
        fwrite($fp, "Host: " . $host . "\r\n");
        if ($login != "") {
            fwrite($fp, "Authorization: Basic " . 
            base64_encode($login. ":" . $password) . "\n");
        }
        fwrite($fp, "\n");
        $response = "";
        while(!feof($fp)) {
            $response .= fread($fp, 1);
        }
        fclose($fp);
        list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
        return $responseBody;

    }    

    public function smsWrapper($host, $port, $login, $password, $phone, $text, $sender)
    {
        if(!empty($phone) && !empty($text)) 
        {
            $phonenum = preg_replace("/[^0-9]/", '', $phone);
            $result = $this->smsSend($host, $port, $login, $password, $phonenum, $text, $sender);
            return $result;
        }
        else return false;
    }

    private function smsCleanNum($phone)
    {
        return str_replace(array('+','(',')','-'),"",$phone);
    }
}

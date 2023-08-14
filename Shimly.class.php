<?php

/**
   * Shimly API Class
   * 
   * @package    FlareCO/ShimlyAPI
   * @author     FlareCO (SM: 5032)
   * @copyright  Copyright (c) 2022, FlareCO
   * @license    http://opensource.org/licenses/gpl-3.0.html GNU Public License
   * @link       http://www.shimly.de
   * @version    1.4
   */

/*

    Functions:
        - hasSuccess() - return true if the last request was successful
            - true/false
        - validate($sID, $sPW) - validates provided Shimly credentials
            - { "code": "1001", "username": "FlareCO", "status": "1", "country": "DE" }       
        - validateSimple($sID) - validates provided Shimly credentials
            - { "code": "1001", "username": "FlareCO", "status": "1", "country": "DE" }
        - getUserBalance($sID) - returns the balance of the provided Shimly account
            - { "code": "1001", "smi": "9999999", "user": "100" }
        - payIn($sID, $sPW, $amount, $reference) - debit the provided amount from the provided Shimly account
            - { "code": "1001", "smi": "9999999", "user": "200" }
        - payOut($sID, $sPW, $amount, $reference) - credit the provided amount to the provided Shimly account
            - { "code": "1001", "smi": "9999999", "user": "100" }
        - smiBalance() - returns the balance of the provided SMI account
            - { "code": "1001", "smi": "100", "vault": "0" }

*/

class Shimly {

    private $appID;
    private $appSecret;
    private $mode = 'shimlys';
    private $baseURL = 'https://www.shimly.de/external/';

    private $success = false;

    public function __construct($appID, $appSecret, $mode = 'shimlys') { // $mode = shimlys or boostpoints
        $this->appID = $appID;
        $this->appSecret = $appSecret;
        $this->mode = $mode;
    }

    private function codeToText($code = 1099){

        $arr = array(
            "1001" => "Erfolgreicher API Zugriff",
            "1002" => "SMI Account existiert nicht",
            "1003" => "SMI Account-Passwort ist falsch",
            "1006" => "Shimly Nutzer existiert nicht",
            "1009" => "Shimly Nutzer SMI-Passwort ist falsch",
            "1050" => "Ungültige IP-Adresse",
            "1095" => "SMI im Wartungsmodus",
            "1097" => "SMI überlastet",
            "1098" => "SMI Account gesperrt",
            "1099" => "Unbekannter Fehler",
        );

        return $arr[$code];

    }

    private function layout($data = [], $scheme = ''){

        $arr = array(
            'validate' => array('code', 'username', 'status', 'country'),
            'userBalance' => array('code', 'smi', 'user'),
            'payIn' => array('code', 'smi', 'user'),
            'payOut' => array('code', 'smi', 'user'),
            'rate' => array('code', 'rate'),
            'smi' => array('code', 'smi', 'vault')
        );

        if(array_key_exists($scheme, $arr)){
            return json_encode(array_combine($arr[$scheme], $data), JSON_PRETTY_PRINT);
        }

        return json_encode($data);

    }

    private function curl($url, $data = null, $method = 'GET') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
        
    }

    /**
     * Checks if the last request was successful
     * @access public
     * @return true|false
     */

    public function hasSuccess(){
        $tmp = $this->success;
        $this->success = false;
        return $tmp;
    }

    /**
     * Checks if the user provided real credentials (shimly id and password)
     * 
     * @param int $sID Shimly ID
     * @param string $sPW SMI Password
     * @access public
     * @return json|exception
     */
    public function validate($sID = 0, $sPW = ''){
        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret,
            's_id' => $sID,
            's_pw' => $sPW
        );
        $result = $this->curl($this->baseURL.$this->mode.'/validate.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'validate');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }

    }

    /**
     * Checks if the user provided a real shimly id
     * 
     * @param int $sID Shimly ID
     * @access public
     * @return json|exception
     */
    public function validateSimple($sID = 0){
        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret,
            's_id' => $sID
        );
        $result = $this->curl($this->baseURL.$this->mode.'/lookup.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'validate');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }

    }

    /**
     * Retrieves the shimly balance of the user
     * 
     * @param int $sID Shimly ID
     * @param string $sPW SMI Password
     * @access public
     * @return json|exception
     */
    public function getUserBalance($sID = 0, $sPW = ''){

        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret,
            's_id' => $sID,
            's_pw' => $sPW
        );
        $result = $this->curl($this->baseURL.$this->mode.'/saldo.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'userBalance');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }

    }

    /**
     * Debit the shimly account of a user (-)
     * 
     * @param int $sID Shimly ID
     * @param string $sPW SMI Password
     * @param int $n Amount to debit
     * @param string $s Comment
     * @access public
     * @return json|exception
     */
    public function payIn($sID = 0, $sPW = '', $n = 100, $s = ''){

        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret,
            's_id' => $sID,
            's_pw' => $sPW,
            'n' => $n,
            's' => $s
        );

        $result = $this->curl($this->baseURL.$this->mode.'/get.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'payIn');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }

    }

    /**
     * Credit the shimly account of a user (+)
     * 
     * @param int $sID Shimly ID
     * @param string $sPW SMI Password
     * @param int $n Amount to debit
     * @param string $s Comment
     * @access public
     * @return json|exception
     */
    public function payOut($sID = 0, $sPW = '', $n = 100, $s = ''){

        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret,
            's_id' => $sID,
            's_pw' => $sPW,
            'n' => $n,
            's' => $s
        );

        $result = $this->curl($this->baseURL.$this->mode.'/send.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'payOut');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }

    }
    

    /**
     * Returns the current balance of the SMI account.
     * 
     * @access public
     * @return json|exception
     */
    public function smiBalance(){
            
        $data = array(
            'mi_id' => $this->appID,
            'mi_pw' => $this->appSecret
        );

        $result = $this->curl($this->baseURL.$this->mode.'/smi_saldo.php?'.http_build_query($data));
        
        $parse = explode('|', $result);
        if($parse[0] == 1001){
            $this->success = true;
            return $this->layout($parse, 'smi');
        } else {
            throw new Exception($this->codeToText($parse[0]), $parse[0]);
        }
    
    }


}

?>

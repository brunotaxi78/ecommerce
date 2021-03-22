<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model{

    const SESSION_ERROR = "AddressError";

    public static function getCP($nr_cp){

        // https://api.duminio.com/ptcp/v2/{AppID}/{0000000}
        
        $nr_cp = str_replace("-", "", $nr_cp);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.duminio.com/ptcp/v2/ptapi605796ce0a1cb2.43543450/$nr_cp");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;
    }

    public function loadFromCP($nr_cp){

        $data = Address::getCP($nr_cp);

        if(isset($data[0]['Morada']) && $data[0]['Morada']){

            $this->setdesaddress($data[0]['Morada']);
            $this->setdescity($data[0]['Localidade']);
            $this->setdesstate($data[0]['Concelho']);
            $this->setdesdistrict($data[0]['Distrito']);
            $this->setdescountry('Portugal');
            $this->setdeszipcode($nr_cp);
        }
        
    }

    public function save(){

        $sql = new Sql();


        $results =  $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, 
                                    :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
                        'idaddress'=>$this->getidaddress(),
                        'idperson'=>(int)$this->getidperson(),
                        'desaddress'=>utf8_decode($this->getdesaddress()),
                        'descomplement'=>$this->getdescomplement(),
                        'descity'=>utf8_decode($this->getdescity()),
                        'desstate'=>utf8_decode($this->getdesstate()),
                        'descountry'=>$this->getdescountry(),
                        'deszipcode'=>$this->getdeszipcode(),
                        'desdistrict'=>utf8_decode($this->getdesdistrict())
                        ]);


            if (count($results) > 0){
                $this->setData($results[0]);
            }
    }

    public static function setMsgError($msg)
	{

		$_SESSION[Address::SESSION_ERROR] = $msg;

	}

	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;

	}

	public static function clearMsgError()
	{

		$_SESSION[Address::SESSION_ERROR] = NULL;

	}

}

?>
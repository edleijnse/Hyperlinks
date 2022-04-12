<?php
/* 
A domain Class to demonstrate RESTful web services
*/
Class Cellphone {
    public $vendor;
    public $type;
}

Class Mobile {


	private $mobiles = array(
		1 => 'Apple iPhone 6S',  
		2 => 'Samsung Galaxy S6',  
		3 => 'Apple iPhone 6S Plus',  			
		4 => 'LG G4',  			
		5 => 'Samsung Galaxy S6 edge',  
		6 => 'OnePlus 2');
		
	/*
		you should hookup the DAO here
	*/
	public function getAllMobile(){
        $cellphone1 = new Cellphone();
        $cellphone1->vendor = 'Apple';
        $cellphone1->type = 'Apple iPhone 6S';
        $mobilephones = array();
        $mobilephones[0] = new Cellphone();
        $mobilephones[0]->vendor = 'Apple';
        $mobilephones[0]->type = 'Apple iPhone 6s';
        $mobilephones[1] = new Cellphone();
        $mobilephones[1]->vendor = 'Apple';
        $mobilephones[1]->type = 'Apple iPhone 13s';
		return $mobilephones;
	}
	
	public function getMobile($id){
        $cellphone1 = new Cellphone();
        $cellphone1->vendor = 'Apple';
        $cellphone1->type = 'Apple iPhone 6S';
        $cellphone2 = new Cellphone();
        $cellphone2->vendor = 'Apple';
        $cellphone2->type = 'Apple iPhone 14S';
        $cellphone3 = new Cellphone();
        $cellphone3->vendor = 'Samsung';
        $cellphone3->type = 'Samsung Galaxy 10';
        $mobilephones = array();
        $mobilephones[0] = $cellphone1;
        $mobilephones[1] = $cellphone2;
        $mobilephones[2] = $cellphone3;
		// $mobile = array($id => $mobilephones[$id] ?: $mobilephones[0]);
        $mobile = $mobilephones[$id] ?: $mobilephones[0];
		return $mobile;
	}	
}
?>
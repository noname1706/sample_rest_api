<?php
namespace models;

class User {

	const MOCK_USERS = array(
		
		345 => array('name' => 'Andrew', 'city' => 'Kyiv', 'lastModified' => 1538659329),
		254 => array('name' => 'Joe', 'city' => 'New York', 'lastModified' => 1538659329),
		370 => array('name' => 'Mark', 'city' => 'Kharkiv', 'lastModified' => 1538659329),
		
		);

	public function getOneById($id, $page=false){

		if (!array_key_exists($id, self::MOCK_USERS)){
			
			return false;
		
		}
		
		return array($id => self::MOCK_USERS[$id]);
	}

	public function getAll($page=false){

		return array('response' => self::MOCK_USERS, 'lastModified' => 1538659329);
	
	}

}

?>
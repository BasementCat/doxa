<?php
	class PasswordException extends Exception{}

	class User{
		protected $ID, $Username, $Password;
		protected static $UCache=array();

		public function __construct(){
			do{
				$this->ID=sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff),
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
					mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff),
					mt_rand(0, 0xffff));
			}while(isset(self::$UCache[$this->ID]));
			self::$UCache[$this->ID]=$this;
		}

		public static function loadAll(){
			$userfile=sprintf("%s/USERS.json", DATA_DIR);
			if(!file_exists($userfile)) return;
			$users=json_decode(file_get_contents($userfile), true);
			foreach($users as $userdata){
				$u=User::createFrom($userdata);
				self::$UCache[$u->ID]=$u;
			}
		}

		public static function saveAll(){
			$out=array();
			foreach(self::$UCache as $user){
				$out[]=$user->toArray();
			}
			file_put_contents(sprintf("%s/USERS.json", DATA_DIR), json_encode($out));
		}

		public static function createFrom($array){
			$u=new User();
			unset(self::$UCache[$u->ID]);
			$u->populate($array);
			self::$UCache[$u->ID]=$u;
			return $u;
		}

		private function populate($in){
			foreach($in as $k=>$v)
				$this->$k=$v;
		}

		private function toArray(){
			static $fields=array('ID', 'Username', 'Password');
			$out=array();
			foreach($fields as $f){
				$v=$this->$f;
				if(is_object($v)&&method_exists($v, 'toArray')) $v=$v->toArray();
				$out[$f]=$v;
			}
			return $out;
		}

		public function __set($k, $v){
			switch($k){
				case 'ID':
					return;
			}
			$this->$k=$v;
		}

		public function __get($k){
			switch($k){
				default:
					return $this->$k;
			}
		}

		public static function me(){
			//TODO: implement me!
			return null;
		}

		public function setPassword($password, $custom_salt=null, $custom_wf=null){
			global $_CONFIG;
			$this->Password=self::hashpass($_CONFIG['PasswordHashAlgorithm'], $password, $custom_salt, $custom_wf?$custom_wf:$_CONFIG['PasswordHashRounds']);
		}

		public function checkPassword($otherPassword){
			return $this->Password==crypt($otherPassword, $this->Password);
		}
		/*
			Internal password stuff
		*/
		public static $HashAlgos=array(
			'MD5'		=>	array('$1$%2$s$',				12,	null,	null,	null,		'CRYPT_MD5'),
			'BCrypt'	=>	array('$2a$%1$02d$%2$s$',		22,	10,		4,		31,			'CRYPT_BLOWFISH'),
			'SHA256'	=>	array('$5$rounds=%1$d$%2$s$',	16,	5000,	1000,	999999999,	'CRYPT_SHA256'),
			'SHA512'	=>	array('$6$rounds=%1$d$%2$s$',	16,	5000,	1000,	999999999,	'CRYPT_SHA512')
		);
		private static $SaltChars="./0-9A-Za-z";

		public static function gensalt($chars){
			$out="";
			$strip=sprintf("#[^%s]#", self::$SaltChars);
			while(strlen($out)<$chars){
				for($i=0; $i<$chars; $i++){
					$out.=chr(mt_rand(46, 122)); //. through z, ascii
				}
				$out=preg_replace($strip, '', $out);
			}
			return substr($out, 0, $chars);
		}

		private static function hashpass($algo, $password, $salt=null, $rounds=null){
			if(!isset(self::$HashAlgos[$algo])) throw new PasswordException(sprintf("The specified algorithm doesn't exist (%s)", $algo));
			list($salt_fmt, $salt_chars, $dfl_rounds, $min_rounds, $max_rounds, $algo_def)=self::$HashAlgos[$algo];
			if(!defined($algo_def)||!constant($algo_def)) throw new PasswordException("This server does not support ".$algo);
			if($salt){
				if(!preg_match(sprintf("#[%s]{%d}#", self::$SaltChars, $salt_chars), $salt))
					throw new PasswordException(sprintf("For %s, the salt must be exactly %d characters matching %s", $algo, $salt_chars, self::$SaltChars));
			}else{
				$salt=self::gensalt($salt_chars);
			}
			if($rounds){
				if($rounds<$min_rounds||$rounds>$max_rounds)
					throw new PasswordException(sprintf("For %s, the rounds must be between %d and %d, inclusive", $min_rounds, $max_rounds));
			}else{
				$rounds=$dfl_rounds;
			}
			return crypt($password, sprintf($salt_fmt, $rounds, $salt));
		}
	}
<?php
	class PageIndex{
		private static $forPath=array();

		public $Path, $Pages;

		private function __construct($path){
			$this->Path=$path;
			$this->Pages=array();
		}

		public static function forPath($path){
			$path=trim($path, "\r\n\t /");
			if(preg_match("#\\.".URL_SUFFIX."\$#", $path)) return self::forPage($path);
			if(!$path) $path='root';
			$path=preg_replace("#[^\w\d_]+#", '_', $path);
			$index_file=DATA_DIR.$path.'.page-index';
			if(isset(self::$forPath[$index_file])) return self::$forPath[$index_file];
			if(file_exists($index_file)){

			}else return false;
		}
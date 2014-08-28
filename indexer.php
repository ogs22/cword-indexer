<?php

$pass = file_get_contents('/Users/ogs22/password');


define("DB_HOST",'localhost');
define("DB_USER",'root');
define("DB_PASS",$pass);
define("DB_NAME",'d7undunc');
ini_set('memory_limit', '512M');

include_once('ps.php');

/**
* 
*/
class Cword {
	public $word_array = array();
	public $word_stem_array = array();
	
	function __construct()
	{
		$this->cdb();
		$this->getBody();
		$this->clean();
		$this->stem();
		$this->createDict();
		print_r($this->word_stem_array);
	}

	private function cdb() {
		$this->link = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME) or die("Error " . mysqli_error($this->link));
	}

	private function insertWords() {
		foreach ($this->word_stem_array as $key => $value) {
			
		}
	}

	private function createDict() {
		$sql = 'DROP TABLE IF EXISTS uudiction';
		$this->link->query($sql) or die($sql);
		$sql = "CREATE TABLE `uudiction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` text,
  `stem` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		$this->link->query($sql) or die($sql);
		foreach ($this->dictionSQL as $key => $sql) {
			$this->link->query($sql) or die($sql);
		}

	}

	private function stem(){
		$sql = array();
		foreach ($this->word_array as $key => $value) {
			$stem = PorterStemmer::Stem($value);
			$sql[]= "INSERT INTO uudiction (word,stem) VALUES ('".$this->link->real_escape_string($value)."'
				,'".$this->link->real_escape_string($stem)."'); ";
		}
		$this->dictionSQL = $sql;
	}

	private function getBody() {
		$sql = 'select field_data_body.body_value from node join field_data_body where 
		node.type="coincidence" and node.nid = field_data_body.entity_id and node.status = 1';
		$this->result = $this->link->query($sql) or die($sql);
		while($row = mysqli_fetch_assoc($this->result)) {
                $this->raw .= $row['body_value'];
        }
        unset($this->result);
	}

	private function clean() {
		//take out all the punctuation except \' 
		//remove \'s and 'quotation' -> quotation
		//get rid of stopwords
		$stopwords = file('stopwords.txt',FILE_IGNORE_NEW_LINES);
		$str = strtolower($this->raw);
		$str = str_replace("'"," UUAPOST ",$str);// preserve apostrophes as UUAPOST
		$str = preg_replace('/[[:^alpha:]]/', ' ', $str); // remove all non-alphanumeric
		$str = str_replace(" UUAPOST ","'",$str); // replace UUAPOST with \'
		$str = preg_replace('/\s\s+/', ' ', $str); //remove multi space
		$all = explode(' ', $str);
		asort($all);
		$all = array_unique($all);
		foreach ($all as $key => $value) {	
			$value = preg_replace('/^(\')*/', "", $value);//remove starting '
			$value = preg_replace('/(\')*$/', "", $value);// remove trailing '
			$value = preg_replace('/\'s$/', "", $value); //remove 's
			if (in_array($value, $stopwords)) {
				unset($all[$key]);
			} else {
				$all[$key] = $value;
			}
		}
		$this->word_array = array_unique($all);
		unset($stopwords);
	}

}

$x = new Cword();

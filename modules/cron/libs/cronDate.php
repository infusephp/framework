<?php

namespace nfuse\libs;
 
class CronDate
{
	///////////////////////////
	// Private Class Variables
	///////////////////////////		

	private $myTimestamp;
	static private $dateComponent = array(
		'second' => 's',
		'minute' => 'i',
		'hour' => 'G',
		'day' => 'j',
		'month' => 'n',
		'year' => 'Y',
		'dow' => 'w',
		'timestamp' => 'U'
	);
	static private $weekday = array(
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday',
		0 => 'sunday'
	);

	/**
	* Constructor
	* @param int $timestamp timestamp
	*/
	function __construct($timestamp = null)
	{
		$this->myTimestamp = is_null($timestamp)?time():$timestamp;
	}

	/**
	* Getter
	* @param string $var value to get
	* @return mixed value
	*/
	function __get($var) {
			return date(self::$dateComponent[$var], $this->myTimestamp);
	}

	/**
	* Setter
	* @param string $var type of set to perform
	* @param mixed $value value to set
	*/
	function __set($var, $value) {
			list($c['second'], $c['minute'], $c['hour'], $c['day'], $c['month'], $c['year'], $c['dow']) = explode(' ', date('s i G j n Y w', $this->myTimestamp));
			switch ($var)
			{
			case 'dow':
				$this->myTimestamp = strtotime(self::$weekday[$value], $this->myTimestamp);
				break;
			case 'timestamp':
				$this->myTimestamp = $value;
				break;
			default:
				$c[$var] = $value;
				$this->myTimestamp = mktime($c['hour'], $c['minute'], $c['second'], $c['month'], $c['day'], $c['year']);
			}
	}

	/**
	* Modifies the timestamp using PHP's strtotime()
	* @param string $how date
	* @return boolean success
	*/
	function modify($how)
	{
		return $this->myTimestamp = strtotime($how, $this->myTimestamp);
	}
}
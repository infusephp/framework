<?php

/*
* Smarty plugin
* -------------------------------------------------------------
* Type: modifier
* Name: no2words
* Version: 0.1
* Date: 22nd July 2003
* Author: Bas Jobsen (bas@startpunt.cc) - original class
* Peter Morgan <pmorgan@ukds.net> - created smarty modifier
* Purpose: converts a numbers to words eg 1000 becomes one thousand
* Note: This is a hack of the original class object by Bas Jobsen
* http://phpclasses.lightwood.net/browse.html/package/754.html
* Input: number and style
* Example: {$number|no2words:1}
* -------------------------------------------------------------
*/
/****** Original Copyright notice ******

numbers2words -numbers2words.php-
converts numbers to words
numbers range from 0 to 999.999.999.999.999

Note: argument to n2w is a string use
strings for big number (usally bigger then 2^32)

For british (GB) numbers call with a optional
second argument = 1.

British (GB)
n2w('1000000000',1) gives "one milliard"
n2w('1000000000000',1) gives "one billion"

American (US/FR)
n2w('1000000000') gives "one billion"
n2w('1000000000000') gives "one trillion"

Version 0.2b
Last change: 2002/09/12
copyrigth 2002 Email Communications, http://www.emailcommunications.nl/
written by Bas Jobsen (bas@startpunt.cc)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2, or (at your option)
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software Foundation,
Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

function smarty_modifier_no2words($number, $uk=0)
{

if(!is_string($number))$number.="";
if(!$uk)$many=array('', ' thousand ',' million ',' billion ',' trillion ');
else $many=array('', ' thousand ',' million ',' milliard ',' billion ');
$string='';
if(strlen($number)%3!=0)
{
$string.=smarty_modifier_no2words_hunderds(substr($number,0, strlen($number)%3 ));
$string.=$many[floor(strlen($number)/3)];

}
for($i=0; $i<floor(strlen($number)/3); $i++)
{

$string.=smarty_modifier_no2words_hunderds(substr($number,strlen($number)%3+($i*3),3));
if($number[strlen($number)%3+($i*3)]!=0)$string.=$many[floor(strlen($number)/3)-1-$i];

}

return $string;

}

function smarty_modifier_no2words_hunderds($number)
{

$test=$number*1;
if (empty($test))return;
$lasts=array('one','two','three','four','five','six','seven','eight','nine');
$teens=array('eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen');
$teen=array('ten','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety');

/* written by bas@startpunt.cc */

$string='';
$j=strlen($number);
$done=false;
for($i=0; $i<strlen($number); $i++)
{


if($j==2)
{
if(strlen($number)>2)
{
if($number[0]!=0)$string.= ' hundred ';
if(substr($number,$i+1))$string.= 'and ';
}
if ($number[$i]==1)
{
if($number[$i+1]==0) $string.=$teen[$number[$i]-1];
else
{
$string.=$teens[$number[$i+1]-1];
$done=true;
}
}
else
{
if(!empty($teen[$number[$i]-1]))$string.=$teen[$number[$i]-1].' ';
}
}

elseif($number[$i]!=0 && !$done) $string.=$lasts[$number[$i]-1];

$j--;
}

return $string;
}
?>
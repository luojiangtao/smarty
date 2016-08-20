<?php
	include "lib/Smarty.class.php";
	header("Content-type:text/html;charset=utf-8");
	$smarty = new Smarty();
	$name = "罗江涛";
	$person = array(
		array(
			'name' =>"罗江涛" ,
			'age'=>18
			 ),
		array(
			'name' =>"longlong" ,
			'age'=>19
			 ),
		array(
			'name' =>"mama" ,
			'age'=>20
			 ),
		);
	$smarty->assign("name", $name);
	$smarty->assign("person", $person);
	$smarty->display("index.html");
?>
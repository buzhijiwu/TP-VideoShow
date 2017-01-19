<?php
function getRoomNiceNumber($num) {
	switch($num) {
		case $num<10 :
			$num = '00000'.$num;
			break;
		case $num<100 :
			$num = '0000'.$num;
			break;
		case $num<1000 :
			$num = '000'.$num;
			break;
		case $num<10000 :
			$num = '00'.$num;
			break;
		case $num<100000 :
			$num = '0'.$num;
			break;
	}
	return $num;
}
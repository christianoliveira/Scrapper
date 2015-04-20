<?php 

	function getSERP($keyword, $country, $language, $googledomain){
		$url = "http://www.".$googledomain."/search?q=".$keyword."&hl=".$language."&gl=".$country."&pws=0";
		$html = file_get_contents($url);
	}

	function saveSerp($html, $keyword, $country, $language){
		$fecha = New DateTime();
		$fileName = $keyword."_".$language."_".$country."_".$fecha->getTimeStamp();
		file_put_contents($fichero, $html);
	}
	/* Código básico que obtiene el html del resultado de búsqueda de la keyword definida */
	$keyword = "alumno ballesta";
	$keyword = str_replace(' ', '+', $keyword);
	$country = "es";
	$language = "es";
	$domain = "google.es";
	$url = "http://www.".$domain."/search?q=".$keyword."&hl=".$language."&gl=".$country."&pws=0";

	print $url."<br />";

	$html = file_get_contents($url);

	$fecha = New DateTime();

	$fichero = $keyword."_".$language."_".$country."_".$fecha->getTimeStamp();
	file_put_contents($fichero, $html);
 ?>
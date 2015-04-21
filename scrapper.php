<?php 

	function getSERP($keyword, $country, $language, $domain){
		$url = "http://www.".$domain."/search?q=".$keyword."&hl=".$language."&gl=".$country."&pws=0";
		$html = file_get_contents($url);
		return $html;
	}

	function saveSERP($html, $keyword, $country, $language){
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
	
	$html = getSERP($keyword, $country, $language, $domain);
	saveSERP($html, $keyword, $country, $language);
 ?>
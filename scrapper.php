<?php 
	class SERPResult{
		var $type; //news, organic, image, video,..
		var $url; //url de destino del resultado
		var $title; //qué muestra Google como anchor, si lo muestra
		var $newssite; //para Google News, el nombre del sitio
		var $lastModifiedTimeNews; //para Google News, el hace X tiempo
		var $description; //la descripción que muestra Google del resultado
		var $rankposition; //la posición en el ranking
	}

	class Project{
		var $name;
		var $id;
		var $domain;
		var $language;
		var $country;
		var $keywords;
	}

	class SERP{
		var $html; //el html completo por si falla el procesado
		var $keyword; //la keyword 
		var $country; //el pais
		var $domain; //el dominio de google
		var $language; //el idioma de la búsqueda
		var $SERPResult; //array con cada uno de los resultados del SERP
		var $timeStamp; //timestamp del momento en el que se pide el resultado

		function getSERP(){
			$url = "http://www.".$this->domain."/search?q=".$this->keyword."&hl=".$this->language."&gl=".$this->country."&pws=0";
			$tempHtml = new DOMDocument;
			$tempHtml->loadHtmlFile($url);
			if($tempHtml == FALSE){
				echo "<br>ERROR<br>";
			}


			$date = new DateTime();
			$this->timeStamp = $date->getTimestamp();
			$this->html = $tempHtml;
		}

		function saveSERP(){
			$date = New DateTime();
			$fileName = $this->keyword."_".$this->language."_".$this->country."_".$date->getTimeStamp().".html";
			$this->html->saveHTMLFile($fileName);
		}

		function getCleanURL($element){
			preg_match('~q=(https?://.*)&sa~', $element, $url);
			return $url[1];
		}

		function extractInfoSERP(){
			/* testing xpath */
			$xpath = new DOMXPath($this->html);

			//esto parte el html en los serpresult unicamente, con lo cual podemos ir uno por uno comprobando el tipo 
			//y guardando la info que corresponda
			$nodelist = $xpath->query("//li[@class='g']");

			$this->SERPResult = array();
			$posicionNews = 1;
			$posicionImages = 1;
			$posicionOrganic = 1;
			$organicCount = 0;

			//hacemos un bucle para recorrer los bloques de resultados
			foreach ($nodelist as $serpnode) {
				$tempSERPresult = new SERPResult;
				$tempSERPresult->type = 0;
				$indexNews = 0;
				

				//es news?
				$links = $xpath->query(".//a/@href", $serpnode);
				foreach ($links as $link) {
					if (strpos($link->nodeValue, 'QqQIw') != false) {
						//es news, un link
						$tempSERPresult = new SERPResult;
						$tempSERPresult->type = "news";

						
						//pedimos el title
						$title = $xpath->query(".//a[contains(@href, 'QqQIw')]", $serpnode);
						$tempSERPresult->title = $title->item($indexNews)->nodeValue;

						//el site
						$newssite = $xpath->query(".//a[contains(@href, 'QqQIw')]//..//div//cite", $serpnode);
						$tempSERPresult->newssite = $newssite->item($indexNews)->nodeValue;

						//la url limpia
						$tempSERPresult->url = $this->getCleanURL($link->nodeValue);

						//el ranking
						$tempSERPresult->rankposition = $posicionOrganic."-".$posicionNews;

						//el hace x horas/minutos
						$time = $xpath->query(".//a[contains(@href, 'QqQIw')]//..//div//span//span[@class='nobr']");
						$timeindex = 2*($indexNews);
						$tempSERPresult->lastModifiedTimeNews = $time->item($timeindex)->nodeValue;

						//la descripción
						$description = $xpath->query(".//a[contains(@href, 'QqQIw')]//..//div//span[@class='st']");
						$tempSERPresult->description = $description->item($indexNews)->nodeValue;


						$posicionNews++;
						$indexNews++;

						array_push($this->SERPResult, $tempSERPresult);
					}else if(strpos($link->nodeValue, 'QpwI') != false){
						//es news, la imagen
						$tempSERPresult = new SERPResult;
						$tempSERPresult->type = "news-image";
						$newssite = $xpath->query(".//a[contains(@href, 'QpwI')]//..//span", $serpnode);
						$tempSERPresult->newssite = $newssite->item(0)->nodeValue;
						$tempSERPresult->url = $this->getCleanURL($link->nodeValue);
						$tempSERPresult->rankposition = $posicionOrganic."-0";
						array_push($this->SERPResult, $tempSERPresult);
					}else if(strpos($link->nodeValue, 'QFjA') != false){
						//es un resultado orgánico normal
						$tempSERPresult = new SERPResult;
						$tempSERPresult->type = "normal";
						$title = $xpath->query(".//a[contains(@href, 'QFjA')]", $serpnode);
						$tempSERPresult->title = $title->item(0)->nodeValue;
						$tempSERPresult->url = $this->getCleanURL($link->nodeValue);
						if($posicionNews!=1 || $posicionImages!=1){
							$posicionOrganic++;
							$posicionNews=1;
							$posicionImages=1;
						}
						$tempSERPresult->rankposition = $posicionOrganic;
						$description = $xpath->query(".//a[contains(@href, 'QFjA')]//..//..//span[@class='st']");

						$tempSERPresult->description = $description->item($organicCount)->nodeValue;
						$organicCount++;
						$posicionOrganic++;
						array_push($this->SERPResult, $tempSERPresult);
					}else if(strpos($link->nodeValue, 'Q9Q') != false){
						$tempSERPresult = new SERPResult;
						$tempSERPresult->type = "images";
						$tempSERPresult->url = $this->getCleanURL($link->nodeValue);
						$tempSERPresult->rankposition = $posicionOrganic."-".$posicionImages;
						$posicionImages++;
						array_push($this->SERPResult, $tempSERPresult);
					}
				}	
			}	



			foreach ($this->SERPResult as $result) {
				echo "tipo de resultado: ".$result->type."<br>";
				echo "title: ".$result->title."<br>";
				echo "description: ".$result->description."<br>";
				echo "url: ".$result->url."<br>";
				echo "rank: ".$result->rankposition."<br>";
				echo "time: ".$result->lastModifiedTimeNews."<br>";
				echo "site: ".$result->newssite."<br><br>";
			}


		}

		function SERP($keyword, $country, $domain, $language){
			$html = new DOMDocument;
			$this->keyword = $keyword;
			$this->country = $country;
			$this->domain = $domain;
			$this->language = $language;
		}

	}

	if( $_GET["keyword"]){
		$keyword = $_GET["keyword"];
		$keyword = str_replace(' ', '+', $keyword);
		$country = "es";
		$language = "es";
		$domain = "google.es";

		$googleSERP = new SERP($keyword, $country, $domain, $language);
		$googleSERP->getSERP();
		$googleSERP->saveSERP();
		$googleSERP->extractInfoSERP();

	}else{
		echo "El parámetro keyword es obligatorio.";
	}

	//$keyword = "atentado francia";
	
 ?>
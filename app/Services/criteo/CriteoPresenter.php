<?php


namespace App\Services\criteo;

use App\Services\AMSPresenterInterface;
use DateTime;

/*
	Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class CriteoPresenter implements AMSPresenterInterface
{

	var $date_format = 'Y-m-d';
	
	public function __construct()
	{
		error_log('AdsensePresenter Constructed');
	}

	public function present($data, $format, $echo = true)
	{
		
		$this->date_format = $format;
		
		// Passed a string, turn it into an array 
	    if (is_array($data) === false) {
	      $data = json_decode($data, true);
	    }
				
		$strTempFile = 'csvOutput' . date("U") . ".csv";
		$f = fopen($strTempFile,"w+");
				
		$firstLineKeys = false;	
		foreach ($data as $line)
		{			
			$array = $this->mapping($line);	
			if (empty($firstLineKeys))
			{
		        $firstLineKeys = array_keys($array);
		        fputcsv($f, $firstLineKeys);
		        $firstLineKeys = array_flip($firstLineKeys);
		     }
			
			/*
				Using array_merge is important to maintain the order of keys acording to the first element
			*/ 
			fputcsv($f, array_merge($firstLineKeys, $array));
		}
		fclose($f);
		
		$echo ? $this->echoCsv($strTempFile) : $this->attachCsv($strTempFile);	
		
		// Delete the temp file
		unlink($strTempFile);	
	}
	
	private function mapping($line)
	{
		$array = array(
			"date" => $this->convertDate($line["date"]),
			"site" => $line["siteName"],
			"impressions reçues" => $line["totalImpression"],
			"impressions prises" => $line["impression"],
			"revenu" => $line["revenue"]["value"], 
			"key" => $line["placementId"],
			"inventaire" => "AMS Market Place",
			"cpm" => $line["cpm"]["value"],
			"annonceur" => "criteo"
		);
		
		return $array;
	}
	
	private function convertDate($date)
	{
		$date = DateTime::createFromFormat($this->date_format, $date);
		
		return $date->format('Y-m-d'); 
	}
	
	private function echoCsv($file)
	{
		if (($handle = fopen($file, "r")) !== FALSE) 
		{
			while (($data = fgetcsv($handle)) !== FALSE) 
			{
				echo implode(",",$data);
				echo "<br />";
			}
			fclose($handle);
		}
	}
	
	/*
	Output the file to the browser (for open/save)
	*/
	private function attachCsv($file)
	{
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Length: ' . filesize($file));
		readfile($file);
	}
}
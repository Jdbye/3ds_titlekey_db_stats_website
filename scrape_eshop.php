<?php

// Output path for json files.
$output_path = "/var/www/3ds.jdbye.com";

$daily_completion = array();
if (file_exists("$output_path/3ds_key_db_completion_daily.json")) {
	$daily_completion = json_decode(file_get_contents("$output_path/3ds_key_db_completion_daily.json"), true);
}
else $daily_completion[date("d-m-Y")] = json_decode(file_get_contents("$output_path/3ds_key_db_completion.json"), true);

function find_game($product_code, $array, $region) {
    foreach($array as $key=>$value){
       if(is_array($value) && array_search($product_code, $value) !== false) {
          if (($array[$key]['region'] == $region) || ($array[$key]['region'] == "ALL")) return $key;
       }
    }
    return false;
}

function file_get_contents_noverify($url) {	
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  

$response = file_get_contents($url, false, stream_context_create($arrContextOptions));
return $response;
}

function file_get_contents_cookie($url, $cookie) {

// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
              "Cookie: $cookie\r\n"
  )
);

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$file = file_get_contents($url, false, $context);
return $file;
}

$eshop_entries = array();
$db_completion = array();

$count_wup = 0;
$count_ctr = 0;
$count_ctr_retail = 0;
$count_ctr_unreleased = 0;
$count_unk = 0;
$total = 0;
$count_ctr_in_db = 0;

echo "Grabbing 3ds_key_db.json...";
$key_json = file_get_contents("https://3ds.titlekeys.com/json_enc");
file_put_contents("$output_path/3ds_key_db.json", $key_json);
$key_db = json_decode($key_json,true);

function scrape_eshop($region, $lang) {
	global $count_wup, $count_ctr, $count_ctr_retail, $count_ctr_unreleased, $count_unk, $total, $count_ctr_in_db, $key_db, $db_completion;
	$url = 'https://samurai.ctr.shop.nintendo.net/samurai/ws/'.$lang.'/titles?offset=';
	$page = 0;
	$lastpage = 1;
	//$cookie = "sid=UnBtzG-tJCNlzDoytobuarOnQJNIYNs5fGg=; pgid=KDlIlR0P8ZxSR0EKznIP3.0d0000viBAY59-";

	$count_ctr_in_db_r = 0;
	$count_ctr_r = 0;
	$first = true;
	$total_r = 0;

	$results = array();
	
	echo "\n\nScraping region $region/$lang... ";

	for ($i = $page; $i <= $lastpage; $i++) {
		// get DOM from URL or file
		$offset = $i * 50;
		$pageurl = $url.$offset;
		$xmlstr = file_get_contents_noverify($pageurl);
		$xml = simplexml_load_string($xmlstr);
		if ($xml === false) {
			echo "Failed loading XML: ";
			foreach(libxml_get_errors() as $error) {
				echo "\n", $error->message;
			}
			die();
		}

		$xml = $xml->contents;

		if ($first) {
			$first = false;
			$total_r = $xml['total'];
			$total = $total + $total_r;
			$lastpage = floor(($total_r - 1) / 50);
			echo "Page $i of $lastpage... ";
		}
		else echo "$i... ";
		
		foreach($xml->content as $a) {
			$title = $a->title;
			$plat = $title->platform;
			$platform = $plat['device'];
			//$release_type = $plat['id'];
			$eshop_sales = $title->eshop_sales;
			$release_date = $title->release_date_on_eshop;
			if (strlen($release_date) == 4) $release_date = $release_date."-01-01";
			if ($platform == "CTR") {
				if ($eshop_sales == "false") {
					// Retail only
					$count_ctr_retail++;
				}
				else if (($release_date == "TBD") || (strtotime($release_date) > time())) {
					$count_ctr_unreleased++;
				}
				else {
					$count_ctr++;
					$count_ctr_r++;
					$code = $title->product_code->__toString();
					$index = $a['index']->__toString();
					$id = $title['id']->__toString();
					$name = $title->name->__toString();
					$icon_url = $title->icon_url->__toString();
					$banner_url = $title->banner_url->__toString();
					$publisher = $title->publisher->name->__toString();
					$genre = $title->display_genre->__toString();
					$rating_info = $title->rating_info;
					if ($rating_info) {
						$rating_system = $rating_info->rating_system->name->__toString();
						$rating = $rating_info->rating->name->__toString();
						$rating_url = $rating_info->rating->icons->icon[0]['url']->__toString();
					} else {
						$rating_system = "N/A";
						$rating = null;
						$rating_url = null;
					}
					$star_rating_info = $title->star_rating_info;
					if ($star_rating_info) {
						$star_rating = $star_rating_info->score->__toString();
						$star_rating_votes = $star_rating_info->votes->__toString();
					} else {
						$star_rating = -1;
						$star_rating_votes = 0;
					}
					$release_date_eshop = $release_date->__toString();
					$release_date_retail = $title->release_date_on_retail->__toString();
					if (strlen($release_date_retail) == 4) $release_date_retail = $release_date_retail."-01-01";
					if (!$release_date_retail) $release_date_retail = "N/A";
					$release_type = $plat->name->__toString();
					$inDB = 0;
					if (($key = find_game($code, $key_db, $region)) !== FALSE) {
						$count_ctr_in_db++;
						$count_ctr_in_db_r++;
						$inDB = 1;
					}
					$name_replaced = trim(str_replace(array("–"),array(" -"),str_replace(array("    ","   ","  "," : "," –"),array(" "," "," ",": ","–"),str_replace(array("\n","’"),array(" ","'"),str_replace(array("™", "©", "®", "\r"), "", $name)))));
					$entry = array('id' => $id, 'name' => $name_replaced, 'code' => $code, 'index' => $index, 'icon_url' => $icon_url, 'banner_url' => $banner_url, 'publisher' => $publisher, 'genre' => $genre, 'rating_system' => $rating_system, 'rating' => $rating, 'rating_url' => $rating_url, 'star_rating' => $star_rating, 'star_rating_votes' => $star_rating_votes, 'release_date_eshop' => $release_date_eshop, 'release_date_retail' => $release_date_retail, 'release_type' => $release_type, 'region' => $region, 'lang' => $lang, 'inDB' => $inDB);
					$results[] = $entry;
				}
			}
			else if ($platform == "WUP") {
				$count_wup++;
			}
			else {
				$count_unk++;
			}
		}
	}

	$percentage = round($count_ctr_in_db_r / $count_ctr_r * 100, 2);
	$missing = $count_ctr_r-$count_ctr_in_db_r;
	
	$db_completion["$region/$lang"] = array('have' => $count_ctr_in_db_r, 'missing' => $missing, 'total' => $count_ctr_r, 'percentage' => $percentage);

	return $results;
}

$eshop_entries['USA/US'] = scrape_eshop("USA","US");
$eshop_entries['EUR/GB'] = scrape_eshop("EUR","GB");
$eshop_entries['JPN/JP'] = scrape_eshop("JPN","JP");

$missing = $count_ctr - $count_ctr_in_db;
$percentage = round($count_ctr_in_db / $count_ctr * 100, 2);
$db_completion["Total"] = array('have' => $count_ctr_in_db, 'missing' => $missing, 'total' => $count_ctr, 'percentage' => $percentage);
	
$daily_completion[date("d-m-Y")] = $db_completion;
	
echo "\n\n\nNumber of CTR (Retail Only): ".$count_ctr_retail."\n";
echo "Number of CTR (Unreleased): ".$count_ctr_unreleased."\n";
echo "Number of CTR (eShop): ".$count_ctr."\n";
echo "Number of WUP: ".$count_wup."\n";
echo "Number of Unknown: ".$count_unk."\n";
echo "Total on server: ".$total."\n\n\n";

foreach ($db_completion as $key=>$value) {
	echo "$key Title Key Completion: ".$value['percentage']."% (have ".$value['have']." of ".$value['total'].", missing ".$value['missing'].")\n\n";
}

flush();
echo "Writing 3ds_eshop_db.json... ";
file_put_contents("$output_path/3ds_eshop_db.json", json_encode($eshop_entries));
echo "Writing 3ds_key_db_completion.json... ";
file_put_contents("/$output_path/3ds_key_db_completion.json", json_encode($db_completion));
echo "Writing 3ds_key_db_completion_daily.json... ";
file_put_contents("/$output_path/3ds_key_db_completion_daily.json", json_encode($daily_completion));

echo "Finished successfully.\n";
?>

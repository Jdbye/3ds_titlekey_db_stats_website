<?php
	$completion = json_decode(file_get_contents("3ds_key_db_completion.json"), true);
	$details = isset($_GET['details']);
	$faq = isset($_GET['faq']);
	$split = (isset($_GET['split']) ? $_GET['split'] : 0);
	if ($details) {
		$eshop_entries = json_decode(file_get_contents("3ds_eshop_db.json"), true);
		if ($_GET['details']) {
			$d = strtoupper($_GET['details']);
			if ($d == "USA") { $region = "USA"; $lang = "US"; }
			elseif ($d == "EUR") { $region = "EUR"; $lang = "GB"; }
			elseif ($d == "JPN") { $region = "JPN"; $lang = "JP"; }
			else { $region = null; }
		}
		else { $region = null; $lang = null; }
		if ($region != null) {
			$c_have = $completion["$region/$lang"]['have'];
			$c_missing = $completion["$region/$lang"]['missing'];
			$c_total = $completion["$region/$lang"]['total'];
			$c_percentage = $completion["$region/$lang"]['percentage'];
		}
		else {
			$c_have = $completion["Total"]['have'];
			$c_missing = $completion["Total"]['missing'];
			$c_total = $completion["Total"]['total'];
			$c_percentage = $completion["Total"]['percentage'];
		}
	}
	
	function merge_regions($eshop_db) {
		
		$merged = array();
		foreach ($eshop_db as $region) $merged = array_merge($merged, $region);
		return $merged;
	}
	
	function array_msort($array, $cols)
	{
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;
	}
	
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

	<title>3DS Title Key DB Stats</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->

    <!-- Custom styles for this template -->
    <link href="theme.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="?">3DS Key Stats</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <?php
			if ($details) { ?>
				<li><a href="?">Summary</a></li>
				<li class="active"><a href="?details=">Details</a></li>
				<li><a href="?faq">FAQ - What is it and how does it work?</a></li>
			<?php }
			else if ($faq) { ?>
				<li><a href="?">Summary</a></li>
				<li><a href="?details=">Details</a></li>
				<li class="active"><a href="?faq">FAQ - What is it and how does it work?</a></li>
			<?php }
			else { ?>
				<li class="active"><a href="?">Summary</a></li>
				<li><a href="?details=">Details</a></li>
				<li><a href="?faq">FAQ - What is it and how does it work?</a></li>
			<?php }
			?>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container theme-showcase" role="main">
      <div class="well">
		<?php
		if ($faq) { ?>
        <h2>3DS Title Key DB - FAQ</h2>
        <p>This page was made to list information about the completion of the 3DS Title Key Database website at any given time. It is automatically updated once a day and so will always have up to date information.<br/>
		Still not sure what all of this is for? Read below.</p>
		<hr>
		<h3>What is all this about title keys? What are they and what do they do?</h3>
		<p>
		Title keys, put simply, are a way to download games <b>directly from Nintendo's servers</b> and install them to your hacked 3DS, <b>without having purchased them</b>. This includes downloading from eShop and using other tools that download from Nintendo's servers. Doing this requires generating tickets, which can be generated using nothing but the title key.<br />
		All you need to do this is a tool that supports title keys directly and automates most of the process (CIAngel or TIKDevil for example), or a tool that can generate tickets (.tik files) from them that you can install with FBI. There are multiple tools for generating tickets.<br />
		As long as you have the correct ticket installed to your system and you are using CFW then you can <b>download the games from eShop without any other homebrew or programs</b>. Installing these tickets does require homebrew though.<br /><br />
		<b>FBI</b> can install these tickets for you so you can download the game using the eShop app, or it can <b>download the game directly from Nintendo's servers and install it, without using the eShop app</b>, both from .tik files and already installed tickets.<br />
		<b>CIAngel</b> uses the title key website directly, and so it doesn't require pre-generated tickets. It can download and install games <b>directly from Nintendo's servers without using the eShop app.</b> and you don't need any other programs or homebrew other than CFW to use it.<br />
		<b>TIKDevil</b> is the counterpart to this, it also uses the title key website and can generate and install tickets for all known title keys giving you the ability to download most popular titles and a lot of less popular titles <b>directly from the eShop app</b>. The installed tickets can also be used with FBI to download the game from Nintendo's servers <b>without using the eShop app.</b><br/><br/>
		A few PC-side programs also exist that can generate these .tik files for you as well, for use with FBI.
		</p>
		<hr>
		<h3>I don't get it. What's the point of this website?</h3>
		<p>
		The purpose of this website is to serve as a tool for people to check if the games they own are already in the list or not, so missing games can be submitted to the title key website more easily, and to provide statistics on overall completion for the curious and as a way to compare the usefulness of the title key database compared to other methods of obtaining 3DS games. It also serves as a convenient and easy to use way to tell you exactly what you'll be able to download with these tools, and what games are missing.
		</p>
		<hr>
		<h3>How does it work?</h3>
		<p>Once a day, the JSON file (json_enc) from the title key website is downloaded.<br/>
		Then Nintendo's servers are parsed for all listed and released downloadable games, in other words, games not currently listed on the eShop, unreleased games or games only available at retail are not listed. Updates and DLC are also not listed on this website, since they are part of the game and aren't listed as separate entries.</br>
		The results are compared and stored into a JSON file containing all listed eShop titles and whether their title keys are already dumped.<br/><br />
		All this website does is display those results in an easily accessible and readable way.<br /><br />
		Currently, localized games (Spanish, French, German etc.) are not listed, nor are Korean, Taiwanese or Chinese titles. Only titles released in Great Britain, United States and Japan are listed. This could be changed however, if there is a demand.</p>
		<hr>
		<h3>Okay, I got it, now where can I find these title keys?</h3>
		<p>Unfortunately, I can't link to them here, as that would be akin to linking to ROMs directly, and for obvious reasons that's not something I want to risk.<br />
		The website is fairly well known, however, so you shouldn't have much trouble finding it.</p>
		<?php }
		elseif ($details) {
			$html_have = "";
			$html_missing = "";
			$cur = "even";
			$cur_have = "even";
			$cur_missing = "even";
			$last_name = "";
			if ($region == null) $c_eshop_entries = merge_regions($eshop_entries);
			else $c_eshop_entries = $eshop_entries["$region/$lang"];
			$c_eshop_entries = array_msort($c_eshop_entries, array('name'=>SORT_ASC, 'region'=>SORT_ASC, 'lang'=>SORT_ASC, 'code'=>SORT_ASC, 'index'=>SORT_ASC));
			foreach ($c_eshop_entries as $eshop_entry) {
				if ($split == 2) {
					if ($eshop_entry['inDB']) {
						$cur_have = ($cur_have == "even" ? "odd" : "even");
						$cur = $cur_have;
					}
					else {
						$cur_missing = ($cur_missing == "even" ? "odd" : "even");
						$cur = $cur_missing;
					}
				}
				elseif ($split < 2 || ($eshop_entry['inDB'] == 1 && $split == 3) || ($eshop_entry['inDB'] == 0 && $split == 4)) {
					$name_replaced = strtolower($eshop_entry['name']);
					if ($name_replaced != $last_name) {
						$cur = ($cur == "even" ? "odd" : "even");
						$last_name = $name_replaced;
					}
				}
				if ($eshop_entry['inDB'] == 1 && $split != 4) {
					if ($split == 2) {
						$html_have = $html_have . "<tr class='table-striped-green $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
					}
					elseif ($split == 1) {
						$html_have = $html_have . "<tr class='table-striped-green $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
						$html_missing = $html_missing . "<tr class='table-striped $cur'><td class='table-striped'>&nbsp;</td><td class='table-striped'>&nbsp;</td><td class='table-striped'>&nbsp;</td></tr>\n";
					}
					else $html_have = $html_have . "<tr class='table-striped-green $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
				}
				elseif ($eshop_entry['inDB'] == 0 && $split != 3) {
					if ($split == 2) {
						$html_missing = $html_missing . "<tr class='table-striped-red $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
					}
					elseif ($split == 1) {
						$html_missing = $html_missing . "<tr class='table-striped-red $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
						$html_have = $html_have . "<tr class='table-striped $cur'><td class='table-striped'>&nbsp;</td><td class='table-striped'>&nbsp;</td><td class='table-striped'>&nbsp;</td></tr>\n";
					}
					else $html_have = $html_have . "<tr class='table-striped-red $cur'><td>".$eshop_entry['region']."/".$eshop_entry['lang']."</td><td>".$eshop_entry['name']."</td><td>".$eshop_entry['code']."</td></tr>\n";
				}
			}
		?>
        <h2>3DS Title Key DB - Details</h2>
        <p>This page was made to list information about the completion of the 3DS Title Key Database website at any given time. It is automatically updated once a day and so will always have up to date information.</p>
		<p>You can click the "Summary" button above to view overall statistics.</p>
		<hr>
		<div class="row">
		<div class="col-sm-6">
		<h4>Region:</h4>
		<p>
        <a href='?details=&amp;split=<?php echo $split; ?>' class="btn btn-<?php echo ($region ? "default" : "primary"); ?>" style="width:px;">All</a>
        <a href='?details=USA&amp;split=<?php echo $split; ?>' class="btn btn-<?php echo ($region == "USA" ? "primary" : "default"); ?>" style="width:56px;">USA</a>
        <a href='?details=EUR&amp;split=<?php echo $split; ?>' class="btn btn-<?php echo ($region == "EUR" ? "primary" : "default"); ?>" style="width:56px;">EUR</a>
        <a href='?details=JPN&amp;split=<?php echo $split; ?>' class="btn btn-<?php echo ($region == "JPN" ? "primary" : "default"); ?>" style="width:56px;">JPN</a>
        </p>
		</div>
		<div class="col-sm-6">
		<h4>Display Style:</h4>
        <p>
		<?php 
		echo "	<a href=\"?details=$region&amp;split=0\" class=\"btn btn-".(($split == 0) ? "primary" : "default")."\">Combined</a>\n";
		echo "			<a href=\"?details=$region&amp;split=1\" class=\"btn btn-".(($split == 1) ? "primary" : "default")."\">Split</a>\n";
		echo "			<a href=\"?details=$region&amp;split=2\" class=\"btn btn-".(($split == 2) ? "primary" : "default")."\">Separate</a>\n";
		echo "			<a href=\"?details=$region&amp;split=3\" class=\"btn btn-".(($split == 3) ? "primary" : "default")."\">Only Have</a>\n";
		echo "			<a href=\"?details=$region&amp;split=4\" class=\"btn btn-".(($split == 4) ? "primary" : "default")."\">Only Missing</a>\n";
		?>
		</p>
		</div>
		</div>
		<div class="row">
		<div class="col-xs-6 row-sm-6">
		  <h3>Have: <?php echo $c_have; ?></h3>
		</div>
		<div class="col-xs-6 row-sm-6">
		  <h3>Miss: <?php echo $c_missing; ?></h3>
		</div>
		</div>
	  <?php if ($split == 1 || $split == 2) { ?>
      <div class="row">
        <div class="col-sm-6">
	  <?php } ?>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Region</th>
                <th>Name</th>
                <th>Product Code</th>
              </tr>
            </thead>
            <tbody>
				<?php echo $html_have; ?>
            </tbody>
          </table>
	  <?php if ($split == 1 || $split == 2) { ?>
        </div>
        <div class="col-sm-6">
            <table class="table table-striped">
            <thead>
              <tr>
                <th>Region</th>
                <th>Name</th>
                <th>Product Code</th>
              </tr>
            </thead>
            <tbody>
              <?php echo $html_missing; ?>
            </tbody>
            </table>
        </div>
      </div>
	  <?php
	  }
	} else { ?>
        <h2>3DS Title Key DB - Summary</h2>
        <p>This page was made to list information about the completion of the 3DS Title Key Database website at any given time. It is automatically updated once a day and so will always have up to date information.</p>
		<p>You can click the "Details" button on the top to view the full list of missing and non-missing games.</p>
		<hr>
		<h3>Current Progress:</h3>
		<hr>
		<h4>Total: <?php echo "Have ".$completion['Total']['have']." of ".$completion['Total']['total'].", missing ".$completion['Total']['missing']."."; ?></h4>
		<div class="progress main" style="height: 175%;">
		<div class="progress-bar main progress-bar-<?php $c = floor($completion['Total']['percentage']); if ($c >= 50) echo "success"; elseif($c >= 25) echo "warning"; else echo "danger"; ?>" role="progressbar" aria-valuenow="<?php echo round($completion['Total']['percentage']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($completion['Total']['percentage']); ?>%; height: 175%;"><h4><?php echo $completion['Total']['percentage']; ?>%</h4></div>
        </div>
		<?php
			
			foreach ($completion as $key=>$value) {
				if ($key != "Total") {
					?>
					<hr>
		<h4><?php echo $key.": Have ".$completion[$key]['have']." of ".$completion[$key]['total'].", missing ".$completion[$key]['missing']."."; ?></h4>
		<div class="progress" style="height: 175%;">
		<div class="progress-bar progress-bar-striped progress-bar-<?php $c = floor($completion[$key]['percentage']); if ($c >= 50) echo "success"; elseif($c >= 25) echo "warning"; else echo "danger"; ?>" role="progressbar" aria-valuenow="<?php echo round($completion[$key]['percentage']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($completion[$key]['percentage']); ?>%; height: 175%;"><h4><?php echo $completion[$key]['percentage']; ?>%</h4></div>
        </div>
					<?php
				}
			}
		} ?>
      </div>
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="js/bootstrap.min.js"></script>
	<center>Code is © 2016 Jdbye and licensed under the GNU LGPLv3&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Uses Bootstrap</center>
	<center>Source code is <a href="https://github.com/Jdbye/3ds_titlekey_db_stats_website">available here</a></center>
  </body>
</html>

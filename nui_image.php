<?php /* Template Name: NUIphotomanager */

include 'nui_image_config.php';

if (!$GLOBALS['nui_debug'])
	if (!is_user_logged_in()) die;

$sqlconn = mysqli_connect($GLOBALS['nui_sql_host'], $GLOBALS['nui_sql_username'],
	$GLOBALS['nui_sql_password'], $GLOBALS['nui_sql_database']);

?>
<!doctype html>
<html class="no-js" lang="en">
<head>
<?php include 'nui_image_header.php';?>
</head>
<body>

<img id="cloud1" src="<?php echo $nui_asses_dir ?>clouds.png" style="height: 80px; float: right; padding: 0px;border: none;"/><br/>
<textarea style="position: fixed; width:75%; height: 50px; z-index:100; display:none" id="link"></textarea>
<div id="photoPreview" class="photoPreview" onclick="dismissPreview();">
	<button onclick="dismissPreview();" style="position: fixed; right:0; z-index: 100"> X </button>
	<img id="imgPreview" class="fit" onclick="dismissPreview();" ></img>
</div>


    <header class="header"><br/>
        <center><h1>Photos Manager</h1><br/>
		<b>
		<?php
			if (isset($_GET['date']) || isset($_POST['date'])) {
				echo '<a href="?">[Back to menu]</a>  ';
			}
			if (isset($_GET['date'])) {
				echo '<a href="?action=upload&date='.urlencode($_GET['date']).'">[Upload]</a>';
			}
		?>
		</b><br/><br/>
		</center>
    </header>
	
<div class="loading" id="loading" style=""><center>
	<img src="<?php echo $nui_asses_dir ?>loading.gif" height="100px"/>
</center></div>

<div id="allImages">
<center>
<?php include 'nui_image_upload.php';?>
<h2>
<?php 

if (!isset($_GET['date']) && !isset($_POST['date'])) {
	$nui_month = 10; $nui_year = 2017;
	for ($year = date('Y');$year >= $nui_year;$year--) {
		if ($nui_year == $year) {
			for ($month=12;$month >= $nui_month;$month--) {
				renderMonthIndex($month, $year);
			}
		} else if ($year == date('Y')) {
			for ($month=date('m');$month>=1;$month--) {
				renderMonthIndex($month, $year);
			}
		} else {
			for ($month=12;$month>=1;$month--) {
				renderMonthIndex($month, $year);
			}
		}
	}
}

function renderMonthIndex($month, $year) {
	echo '<a href="?date='.$year.'-'.$month.'">&#8226; Tháng '.$month.' năm '.$year.'</a></br>';
}
?>
</h2></center>
<section class="Collage effect-parent">
	<?php
	if (isset($_GET['date']) && !isset($_GET['action']) && !isset($_POST['action'])) {
		$res = $sqlconn->query('SELECT name,date,username FROM images WHERE date=\''.mysql_real_escape_string($_GET['date']).'\' ORDER BY timestamp DESC');
		$ret_sql = array();
		$imgcount = 0;
		if (mysqli_num_rows($res) > 0) {
			while($row = mysqli_fetch_assoc($res)) {
				array_push($ret_sql, $row);
				echo '<div class="Image_Wrapper"><img src="https://raw.githubusercontent.com/'.$GLOBALS['nui_github_username'].'/test_spl/master/'.$row['date'].'/'.$row['name'].'" onclick="openPreview('.$imgcount.')"/></div>';
				$imgcount++;
			}
		} else {
			echo '<h1>EMPTY</h1>';
		}
	}
	?>
	<!--div class="Image_Wrapper"><img src="https://avatars2.githubusercontent.com/u/7702203?s=400&u=85eb16ea9b0cbf6afdfa48f407316a53b567a5a0&v=4"/></div-->
</section>
</div>

<script>var data = <?php echo json_encode($ret_sql); ?>;</script>
<script>

$('#allImages').waitForImages(function() {
	$('#loading').fadeOut(200);
	collage('.Collage');
});
	
function openPreview(id) {
	//$("#imgPreview").attr("src","img/"+id+".jpg");
	var lnk = 'https://raw.githubusercontent.com/'+data[id].username+'/test_spl/master/'+data[id].date+'/'+data[id].name;
	$("#imgPreview").attr("src",lnk);
	$("#link").fadeIn(200);
	$("#link").text(lnk);
	$("#photoPreview").fadeIn(200);
}

function dismissPreview() {
	$("#photoPreview").fadeOut(200);
	$("#link").fadeOut(200);
}
</script>

    <center><p id="footnote"></p></center>
</body>
</html>
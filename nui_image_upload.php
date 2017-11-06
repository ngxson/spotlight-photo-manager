<?php 

include 'nui_image_config.php';
if (!$GLOBALS['nui_debug'])
	if (!is_user_logged_in()) die;

if (isset($_GET['action']) && $_GET['action'] == 'upload') {
	?><center><form action="?" method="post" enctype="multipart/form-data">
		<b>UPLOAD VÀO THƯ MỤC <?php echo htmlspecialchars($_GET['date']); ?>:</b><br/>
		Cỡ tối đa: 8MB. Định dạng jpg, jpeg hoặc png<br/><br/>
		<input type="file" name="fileToUpload" id="fileToUpload">
		<input type="hidden" name="date" value="<?php echo htmlspecialchars($_GET['date']); ?>">
		<input type="hidden" name="action" value="sendfile"><br/><br/>
		<input type="submit" value="Upload Image" name="submit">
	</form></center><?php //uploadToGithub(null);
} else if (isset($_POST['action']) && $_POST['action'] == 'sendfile') {
	handleUpload($sqlconn);
}

function handleUpload($sqlconn) {
	$target_dir = "uploads/";
	$target_file = basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
		$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
		if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		} else {
			echo "File is not an image.";
			$uploadOk = 0;
		}
	}
	// Check if file already exists
	/*if (file_exists($target_file)) {
		echo "Sorry, file already exists.";
		$uploadOk = 0;
	}*/
	// Check file size
	if ($_FILES["fileToUpload"]["size"] > 8*1024*1024) {
		echo "Sorry, your file is too large.";
		$uploadOk = 0;
	}
	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" &&
		$imageFileType != "JPG" && $imageFileType != "PNG" && $imageFileType != "JPEG") {
		echo "Sorry, only JPG, JPEG & PNG files are allowed.";
		$uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	} else {
		try {
			$resized_img = resize($_FILES["fileToUpload"]["tmp_name"],
					$GLOBALS['nui_img_max_width'],
					$GLOBALS['nui_img_max_height']);
		} catch (Exception $e) {
			echo 'ERROR'; die;
		}
		uploadToGithub($resized_img, basename($_FILES["fileToUpload"]["name"]), $sqlconn);
	}
}

function uploadToGithub($file, $filename, $sqlconn) {
	$url = 'https://api.github.com/repos/ngxson/test_spl/contents/'.$_POST['date'].'/'.$filename.'.jpg';
	$data = json_encode(array(
		'path' => $filename.'.jpg',
		'message' => 'upload '.$filename,
		'content' => nui_base64_encode($file),
		'branch' => "master"
	));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['nui_github_username'].":".$GLOBALS['nui_github_token']);
	
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	
	try {
		$result = json_decode($result);
		if (isset($result->content)) {
			saveImgToSever($sqlconn, $filename, $_POST['date']);
			?>
				<br/><b>UPLOAD COMPLETE!</b>
				 <a href="?action=upload&date=<?php echo urlencode($_POST['date']); ?>">[Upload thêm]</a>
				<br/>
				Link ảnh: <br/>
				<textarea style="width:75%; height: 50px"><?php echo 'https://raw.githubusercontent.com/'.$GLOBALS['nui_github_username'].'/test_spl/master/'.htmlspecialchars($_POST['date']).'/'.$filename.'.jpg'; ?></textarea>
				<br/><br/>
			<?php 
			echo '<img src="https://raw.githubusercontent.com/'.$GLOBALS['nui_github_username'].'/test_spl/master/'.htmlspecialchars($_POST['date']).'/'.$filename.'.jpg" width="400px"/>';
		} else {
			?>
				<br/><b>ERROR: </b><?php echo json_encode($result); ?>
			<?php 
		}
	} catch (Exception $e) {
		?>
			<br/><b>SEVER ERROR</b>
		<?php 
	}
	
}

function saveImgToSever($sqlconn, $name, $date) {
	$sqlconn->query('INSERT INTO images (name,date,timestamp,username) VALUES (\''.mysql_real_escape_string($name).'.jpg\','.
		'\''.mysql_real_escape_string($date).'\','.
		''.time().','.
		'\''.mysql_real_escape_string($GLOBALS['nui_github_username']).'\')');
}

function nui_base64_encode($input) {
    $CODES = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    $r = "";
    $p = "";
    $c = strlen($input) % 3;
    if ($c > 0) {
        for (; $c < 3; $c++) {
            $p .= "=";
            $input .= "\0";
        }
    }
    for ($c = 0; $c < strlen($input); $c += 3) {
		// we add newlines after every 76 output characters, according to the MIME specs
        /*if ($c > 0 && ($c / 3 * 4) % 76 == 0)
            $r += "\r\n";*/
        $n = (ord($input[$c]) << 16) + (ord($input[$c + 1]) << 8) + (ord($input[$c + 2]));
        $n1 = $n >> 18 & 63;
        $n2 = $n >> 12 & 63;
        $n3 = $n >> 6 & 63;
        $n4 = $n & 63;
        $r .= "".$CODES[$n1].$CODES[$n2].$CODES[$n3].$CODES[$n4];
    }
    return substr($r, 0, (strlen($r) - strlen($p))).$p;
}

function resize($path,$new_width,$new_height) {
    $mime = getimagesize($path);

    if($mime['mime']=='image/png') { 
        $src_img = imagecreatefrompng($path);
    }
    if($mime['mime']=='image/jpg' || $mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
        $src_img = imagecreatefromjpeg($path);
    }   

    $old_x          =   imageSX($src_img);
    $old_y          =   imageSY($src_img);
	
	if ($old_x <= $GLOBALS['nui_img_max_width'] && $old_y <= $GLOBALS['nui_img_max_height']) {
		$dst_img = $src_img;
	} else {
		if($old_x > $old_y) 
		{
			$thumb_w    =   $new_width;
			$thumb_h    =   $old_y*($new_height/$old_x);
		}
	
		if($old_x < $old_y) 
		{
			$thumb_w    =   $old_x*($new_width/$old_y);
			$thumb_h    =   $new_height;
		}
	
		if($old_x == $old_y) 
		{
			$thumb_w    =   $new_width;
			$thumb_h    =   $new_height;
		}
	
		$dst_img        =   ImageCreateTrueColor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
	}
	
	if ($old_x > $GLOBALS['nui_img_no_logo_pixel'] &&
			$old_y > $GLOBALS['nui_img_no_logo_pixel']) {
		$dst_img = addLogo($dst_img);
	}
	
    if($mime['mime']=='image/jpg' || $mime['mime']=='image/png' ||
			$mime['mime']=='image/jpeg' || $mime['mime']=='image/pjpeg') {
		ob_start();
		imagejpeg($dst_img);
		$img = ob_get_clean();
		return $img;
    } else {
		return null;
	}
}

function addLogo($im) {
	// Charge le cachet et la photo afin d'y appliquer le tatouage numérique
	$stamp = imagecreatefrompng(__DIR__.'/logo.png');
	//$im = imagecreatefromjpeg('23146347_299267777256478_1998639907_n.jpg');
	
	// Définit les marges pour le cachet et récupère la hauteur et la largeur de celui-ci
	$marge_right = 10;
	$marge_bottom = 10;
	$sx = imagesx($im) * ($GLOBALS['nui_logo_ratio']);
	$sy = imagesy($stamp) * ($sx/imagesx($stamp));
	
	$r_logo = imagecreatetruecolor($sx, $sy);
	$color = imagecolorallocatealpha($r_logo, 0, 0, 0, 127); //fill transparent back
	imagefill($r_logo, 0, 0, $color);
	imagesavealpha($r_logo, true);
	imagecopyresampled($r_logo, $stamp, 0, 0, 0, 0, $sx, $sy, imagesx($stamp), imagesy($stamp));
	
	// Copie le cachet sur la photo en utilisant les marges et la largeur de la
	// photo originale  afin de calculer la position du cachet 
	imagecopy($im, $r_logo, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, $sx, $sy);
	
	// Affichage et libération de la mémoire
	/*header('Content-type: image/png');
	imagepng($im);
	imagedestroy($im);*/
	imagedestroy($stamp);
	imagedestroy($r_logo);
	return $im;
}

?>
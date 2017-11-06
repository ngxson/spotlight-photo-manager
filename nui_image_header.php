<?php $nui_asses_dir = '//nui.chuyennguyenhue.com/photomanager/'; ?>

    <meta charset="utf-8">
    <title>NUI media manager</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo $nui_asses_dir ?>examples.css" media="all" />
    <link rel="stylesheet" type="text/css" href="<?php echo $nui_asses_dir ?>transitions.css" media="all" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="<?php echo $nui_asses_dir ?>jquery.collagePlus.js"></script>
    <script src="<?php echo $nui_asses_dir ?>jquery.removeWhitespace.js"></script>
    <script src="<?php echo $nui_asses_dir ?>jquery.collageCaption.js"></script>
    <script src="<?php echo $nui_asses_dir ?>jquery.waitforimages.js"></script>
    <script type="text/javascript">
    $(window).load(function () {$(document).ready(function(){
        collage('.Collage');
        $('.Collage').collageCaption();
    });});
    function collage(id) {
        $('.Collage').removeWhitespace().collagePlus(
            {
                'fadeSpeed'     : 1200,
                'targetHeight'  : 300,
                //'effect'        : 'effect-6',
                'direction'     : 'vertical'
            }
        );
    };
    var resizeTimer = null;
    $(window).bind('resize', function() {
        $('.Collage .Image_Wrapper').css("opacity", 0);
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(collage, 200);
    });

    </script>
	
	<style>
		img {
			/*height:300px;*/
		}
		
		.Image_Wrapper {
			/*max-height:300px;*/
		}
		
		.loading {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background-color: rgba(0,0,0,0.5);
			z-index: 100;
		}
		
		.fit { /* set relative picture size */
			max-width: 100%;
			max-height: 100%;
			transform: translate(-50%, -50%);
			top: 50%;
			left: 50%;
			position: fixed;
			margin: auto;
		}
		
		.photoPreview {
			position: fixed;
			width: 100%;
			height: 100%;
			background-color: rgba(0,0,0,0.5);
			z-index: 99;
			display: none;
		}
	</style>
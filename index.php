<html lang="en">
<head>
	<?php include_once 'includes/analytics.php'; ?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="format-detection" content="telephone=no" />
	<meta name="apple-mobile-web-app-title" content="NJT Departure">
	<meta name="viewport" content="initial-scale=1, viewport-fit=cover, minimum-scale=1, width=device-width">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link rel="apple-touch-icon" href="apple-touch-icon.png?m=<?php echo filemtime('apple-touch-icon.png'); ?>" />
	<!-- <link rel="preload" href="css/styles.min.css?v=0.25" as="style" onload="this.rel='stylesheet'"> -->
	<link rel="stylesheet" type="text/css" href="css/styles.css?v=0.26">
	<meta name="theme-color" content="#234177"/>
	<title>NJT Departure</title>
</head>
<body>
<header>
	<div class="widthWrap">
      <h1>NJTransit DepartureVision</h1>
	</div>
</header>
<div id="overlay">
	<div class="spinner element" id="spinner"></div>
	<div id="spnMessage"></div>
</div>
<div class="wrapper">
	<div class="infoBlock">	
		<div id="banner" class="title"></div>
		<div class="information">
			  <div class="form">
			  	<label for="stnInput">Station Name:</label>
			  	<input type="text" name="stnInput" id="stnInput" value="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="Station">
			  </div>
		</div>
	</div>
	<div id="results"></div>
<div class="push"></div>
</div>
<footer>
	<p class="widthWrap disclaimer">Information provided by NJTransit, all rights reserved.<br>&copy; 2018 DLevine.us</p>
</footer>
<script async type="text/javascript" src="js/main.js?v=0.26"></script>
</body>
</html>
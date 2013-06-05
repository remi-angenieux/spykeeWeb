<!DOCTYPE HTML>
<!--
	ZeroFour 1.0 by HTML5 Up!
	html5up.net | @n33co
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>{$pageTitle}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,700,800" rel="stylesheet" type="text/css" />
		<script src="{$rootUrl}js/jquery-1.8.3.min.js"></script>
		<script src="{$rootUrl}css/5grid/init.js?use=mobile,desktop,1000px&amp;mobileUI=1&amp;mobileUI.theme=none"></script>
		<script src="{$rootUrl}js/jquery.dropotron-1.2.js"></script>
		<script src="{$rootUrl}js/init.js"></script>
		<!-- <script src="{$rootUrl}js/jquery-1.9.1.min.js" type="text/javascript" charset="utf-8"></script>-->
		{foreach $additionalJs as $jsFile}
		<script src="{$jsFile}" type="text/javascript" charset="utf-8"></script>
		{/foreach}
		{foreach $additionalCss as $cssFile}
		<link rel="stylesheet" href="{$cssFile}" type="text/css" />
		{/foreach}
		<noscript>
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-desktop.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-1200px.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-noscript.css" />
			<link rel="stylesheet" href="{$rootUrl}css/style.css" />
			<link rel="stylesheet" href="{$rootUrl}css/style-desktop.css" />
		</noscript>
		<!--[if lte IE 9]><link rel="stylesheet" href="css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie8.css" /><![endif]-->
		<!--[if lte IE 7]><link rel="stylesheet" href="css/ie7.css" /><![endif]-->
	</head>
	<body class="no-sidebar">

		<!-- Header Wrapper -->
			<div id="header-wrapper">
				<div class="5grid-layout">
					<div class="row">
						<div class="12u">
						
							<!-- Header -->
								<header id="header">
									<div class="inner">
									
										<!-- Logo -->
											<h1><a href="#" class="mobileUI-site-name">Spykee Play</a></h1>
										
										<!-- Nav -->
											<nav id="nav" class="mobileUI-site-nav">
												<ul>
													<li><a href="/">Accueil</a></li>
													<li>
														<a href="" class="arrow">Admin</a>
														<ul>
															<li><a href="#">Lorem ipsum dolor</a></li>
															<li><a href="#">Magna phasellus</a></li>
															<li>
																<span class="arrow">Phasellus consequat</span>
																<ul>
																	<li><a href="#">Lorem ipsum dolor</a></li>
																	<li><a href="#">Phasellus consequat</a></li>
																	<li><a href="#">Magna phasellus</a></li>
																	<li><a href="#">Etiam dolore nisl</a></li>
																</ul>
															</li>
															<li><a href="#">Veroeros feugiat</a></li>
														</ul>
													</li>
													<li><a href="/play">Jouer</a></li>
													<li><a href="/account/login">Login</a></li>
													<li class="current_page_item"><a href="no-sidebar.html">No Sidebar</a></li>
												</ul>
											</nav>
									
									</div>
								</header>

						</div>
					</div>
				</div>
			</div>
		
		<!-- Main Wrapper -->
			<div id="main-wrapper">
				<div class="main-wrapper-style2">
					<div class="inner">
						<div class="5grid-layout">
							<div class="row">
								<div class="12u mobileUI-main-content">
									<div id="content">

<!DOCTYPE HTML>
<!--
	ZeroFour 1.0 by HTML5 Up!
	html5up.net | @n33co
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>Joueur - {$pageTitle}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,700,800" rel="stylesheet" type="text/css" />
		<script src="{$rootUrl}js/jquery-1.8.3.min.js"></script>
		<script src="{$rootUrl}css/5grid/init.js?use=mobile,desktop,1000px&amp;mobileUI=1&amp;mobileUI.theme=none"></script>
		<script src="{$rootUrl}js/jquery.dropotron-1.2.js"></script>
		<script src="{$rootUrl}js/init.js"></script>
		{foreach $additionalJs as $jsFile}
		<script src="{$jsFile}" type="text/javascript" charset="utf-8"></script>
		{/foreach}
		{foreach $additionalCss as $cssFile}
		<link rel="stylesheet" href="{$cssFile}" type="text/css" />
		{/foreach}
		<link rel="stylesheet" href="{$rootUrl}css/ui-darkness/jquery-ui-1.10.3.custom.min.css" type="text/css" />
		<noscript>
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-desktop.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-1200px.css" />
			<link rel="stylesheet" href="{$rootUrl}css/5grid/core-noscript.css" />
			<link rel="stylesheet" href="{$rootUrl}css/style.css" />
			<link rel="stylesheet" href="{$rootUrl}css/style-desktop.css" />
		</noscript>
		<!--[if lte IE 9]><link rel="stylesheet" href="{$rootUrl}css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="{$rootUrl}css/ie8.css" /><![endif]-->
		<!--[if lte IE 7]><link rel="stylesheet" href="{$rootUrl}css/ie7.css" /><![endif]-->
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
											<h1><a href="#" class="mobileUI-site-name"> SpykeePlay</a></h1>
										
										<!-- Nav -->
											<nav id="nav" class="mobileUI-site-nav">
												<ul>
													<li><a href="{$rootUrl}">Accueil</a></li>
													{if $isAdmin}
													<li>
														<a href="{$rootUrl}Admin" class="arrow">Admin</a>
														<ul >
															<li><a href="{$rootUrl}Admin">Panneau d'administrateur</a></li>
																<li><a href="{$rootUrl}admin/listUser">Listes des utilisateurs</a></li>
																	</ul>
																</li>
																{/if}
																<li><a href="{$rootUrl}play">Jouer</a></li>
																{if !$isConnected}
																<li><a href="{$rootUrl}account/login">Se connecter</a></li>
																<li><a href="{$rootUrl}account/register">S'inscrire</a></li>
																{else}
																<li><a href="{$rootUrl}account">Profil</a></li>
																<li><a href="{$rootUrl}account/logout">Se déconnecter</a></li>
																{/if}
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
							{if !empty($littleMessage) || !empty($littleError)}
							<div id="messages" class="ui-widget" style="float: left; margin-bottom: 25px;">
							{/if}
								{if !empty($littleMessage)}
								<div class="ui-state-highlight ui-corner-all" style="margin-top: 15px; padding: 0 .7em; font-size:11px; line-height:1.3em; width: 600px; margin-left: 90px; margin-bottom: 15px">
									<p style="padding:0px; margin:0px; margin-top: 11px; margin-bottom: 11px; font-size: 1.1em;"><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
										<strong style="color: rgb(46, 125, 178); font-weight: bold;">{$littleMessageTitle}</strong> {$littleMessage}</p>
								</div>
								{/if}
								{if !empty($littleError)}
								<div class="ui-state-error ui-corner-all" style="margin-top: 15px; padding: 0 .7em; font-size:11px; line-height:1.3em; width: 600px; margin-left: 90px; margin-bottom: 15px">
									<p style="padding:0px; margin:0px; margin-top: 11px; margin-bottom: 11px; font-size: 1.1em;"><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
										<strong style="color: rgb(17, 17, 17); font-weight: bold;">{$littleErrorTitle}</strong> {$littleError}</p>
								</div>
								{/if}
							{if !empty($littleMessage) || !empty($littleError)}
							</div>
							{/if}
							<div class="main-wrapper-style2">
								<div class="inner">

<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			<article>
				<header class="major">
					<h2>Info : {$title}</h2>
				</header>
				<p>{$message}</p>
				{if !empty($url)}
				<a href="{$rootUrl}{$url}" class="button">Cliquez ici pour être redirigé</a>
				{/if}
			</article>

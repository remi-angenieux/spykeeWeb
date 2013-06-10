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
				<p>Cliquez <a href="{$rootUrl}{$url}">ici</a> pour être redirigé</p>
				{/if}
			</article>

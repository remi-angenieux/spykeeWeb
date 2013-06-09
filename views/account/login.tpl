<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			<article>
				<header class="major">
					<h2>Connexion</h2>
					<span class="byline">Sidebars are not welcome here</span>
				</header>
				<form method="post" action="/account/login">
					<table>
						<tr>
							<td><label for="pseudo">Pseudo : </label></td>
							<td><input type="text" name="pseudo" id="pseudo" /></td>
						</tr>
						<tr>
							<td><label for="password">Mot de passe : </label></td>
							<td><input type="password" name="password" id="password" /></td>
						</tr>
					</table>
					<input type="submit" name="submit" value="Se connecter" class="button button-alt button-icon button-icon-rarrow" />
				</form>
				<p><br />Si tu as pas de compte tu peux t'en faire un : <a href="{$rootUrl}account/register">Clique ici</a>.</p>
			</article>
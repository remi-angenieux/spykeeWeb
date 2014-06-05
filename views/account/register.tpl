<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			<article>
				<header class="major">
					<h2>Inscription</h2>
				</header>
				<form method="post" action="https://srv-prj.iut-acy.local/RT/Projet28/spykeeWeb-master/views/account/register.tpl">
					<table>
						<tr>
							<td><label for="pseudo">Pseudo : </label></td>
							<td><input type="text" name="pseudo" id="pseudo" /></td>
						</tr>
						<tr>
							<td><label for="password">Mot de passe : </label></td>
							<td><input type="password" name="password" id="password" /></td>
						</tr>
						<tr>
							<td><label for="password2">Vérification du mot de passe : </label></td>
							<td><input type="password" name="password2" id="password2" /></td>
						</tr>
						<tr>
							<td><label for="e-mail">Adresse mail : </label></td>
							<td><input type="email" name="e-mail" id="e-mail" /></td>
						</tr>
					</table>
					<input type="submit" name="submit" value="S'inscrire" class="button button-alt button-icon button-icon-rarrow" />
				</form>
			</article>

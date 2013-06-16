<div class="5grid-layout">
	<div class="row">
		<div class="8u">
			<div id="content mobileUI-main-content">
				<article>
					<header class="major">
						<h2>Jouer avec le robot</h2>
						<span class="byline">Have fun!</span>
					</header>
					<!-- <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="up" value="Haut" onclick="up()" /><br />
					<input type="button" name="left" value="Gauche" onclick="left()" />&nbsp;&nbsp;<input type="button" name="right" value="Droite" onclick="right()" /><br />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="down" value="Bas" onclick="down()" /><br />
					<br />
					<br />
					<input type="button" name="move" value="Move" onclick="move()" />
					<br />
					<br />-->
					<div id="left">
						<h4>Contrôle des mouvements</h4>
						<div id="robotMove">
							<p class="center">
								<input type="button" name="holdingUp" value="Holding Up"
									onclick="holdingUp()" id="holdingUp" class="up" />
							<p>
							<p class="left">
								<input type="button" name="holdingLeft" value="Holding Left"
									onclick="holdingLeft()" id="holdingLeft" class="up" />
							</p>
							<p class="right">
								<input type="button" name="holdingRight" value="Holding Right"
									onclick="holdingRight()" id="holdingRight" class="up" />
							</p>
							<p class="center">
								<input type="button" name="holdingDown" value="Holding Down"
									onclick="holdingDown()" id="holdingDown" class="up" />
							</p>
						</div>
						<input type="button" name="stop" value="Stop" onclick="stop()"
							id="stop" class="clear" />&nbsp;&nbsp;Arrêter le robot
					</div>
					<div id="right">
						<h4>Vitesse :</h4>
						<div id="speed"></div>
						<div id="speedDescription">
							<p class="lowest">Lent</p>
							<p class="highest">Rapide</p>
						</div>
						<p>
							<!-- <br />
						<br />
						<input type="button" name="enableVideo" value="Activer la vidéo" onclick="enableVideo()" />-->
						</p>
					</div>
					<div class="clear">
						<br />
						<p>
							<a href="{$rootUrl}play" class="button">Se retirer de la
								partie</a>
						</p>
						<h4>
							<a href="#console" id="showHideConsole">Console:</a>
						</h4>
						<div id="console">
							<p></p>
						</div>
						<br />
						<h4>
							<img src="{$rootUrl}images/informations.png" alt="Informations" />
							Astuces :
						</h4>
						<ul>
							<li class="keyboardTop">En appuyant sur cette touche vous
								demanderez au robot d'<strong>avancer</strong>. Un second appui
								lui demandera d'arrêter d'<strong>avancer</strong>
							</li>
							<li class="keyboardLeft">En appuyant sur cette touche vous
								demanderez au robot de tourner a <strong>gauche</strong>. Un
								second appui lui demandera d'arrêter de tourner à <strong>gauche</strong>
							</li>
							<li class="keyboardRight">En appuyant sur cette touche vous
								demanderez au robot de tourner a <strong>droite</strong>. Un
								second appui lui demandera d'arrêter de tourner à <strong>droite</strong>
							</li>
							<li class="keyboardDown">En appuyant sur cette touche vous
								demanderez au robot de <strong>reculer</strong>. Un second appui
								lui demandera d'arrêter de <strong>reculer</strong>
							</li>
							<li class="keyboardCtrl">En appuyant sur cette touche vous
								demandera au robot de s'<strong>immobiliser</strong>
							</li>
						</ul>
					</div>
				</article>

			</div>
		</div>
		<div class="4u">
			<div id="sidebar">
				<!-- Sidebar -->

				<section>
					<header class="major">
						<h2>Vue embarquée</h2>
						<span class="byline">Attention aux obstacles !</span>
					</header>
					<p>
						<img src="{$rootUrl}videoStream/video.jpeg" alt="Vidéo"
							id="imgStream" />
					</p>
				</section>
			</div>
		</div>
	</div>
</div>

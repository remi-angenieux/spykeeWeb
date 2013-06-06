<article>
	<header class="major">
		<h2>Jouer avec le robot</h2>
		<span class="byline">Have fun!</span>
	</header>
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="up" value="Haut" onclick="up()" /><br />
	<input type="button" name="left" value="Gauche" onclick="left()" />&nbsp;&nbsp;<input type="button" name="right" value="Droite" onclick="right()" /><br />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="down" value="Bas" onclick="down()" /><br />
	<br />
	<br />
	<input type="button" name="move" value="Move" onclick="move()" />
	<br />
	<br />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="holdingUp" value="Holding Haut" onclick="holdingUp()" id="holdingUp" /><br />
	<input type="button" name="holdingLeft" value="Holding Gauche" onclick="holdingLeft()" id="holdingLeft" />&nbsp;&nbsp;<input type="button" name="holdingRight" value="Holding Droite" onclick="holdingRight()" id="holdingRight" /><br />
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="holdingDown" value="Holding Bas" onclick="holdingDown()" id="holdingDown" /><br />
	<br /></p>
	<div id="speed" style="width: 200px"></div>
	<p><br />
	<input type="button" name="stop" value="Stop" onclick="stop()" />
	<br />
	<br />
	<input type="button" name="enableVideo" value="Activer la vidéo" onclick="enableVideo()" />
	</p>
	<!-- TODO Récupérer la racine du site via un fichier de configuration -->
	<p><img src="{$rootUrl}videoStream/video.jpeg" alt="Vidéo" id="imgStream" /></p>
	<p><code class="result"></code></p>
</article>

</div>
								</div>
								<div class="4u">
									<div id="sidebar">

										<!-- Sidebar -->
									
											<section>
												<header class="major">
													<h2>Subheading</h2>
												</header>
												<p>Phasellus quam turpis, feugiat sit amet ornare in, hendrerit in lectus. 
												Praesent semper mod quis eget mi. Etiam eu ante risus. Aliquam erat volutpat. 
												Aliquam luctus et mattis lectus sit amet pulvinar. Nam turpis nisi 
												consequat etiam.</p>
												<footer>
													<a href="#" class="button button-icon button-icon-info">Find out more</a>
												</footer>
											</section>

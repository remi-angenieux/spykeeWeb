<script>
var lastId={$lastId}
</script>
<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			<article>
				<header class="major">
					<h2>File d'attente</h2>
					<span class="byline">Prennez un café en attendant ;)</span>
				</header>
				<table wdith="100%" border="1px">
					<tr>
						<th>Pseudo</th>
						<th>Place</th>
					</tr>
					{foreach $usersInQueue as $user}
					<tr>
						<td><span class="byline">{$user.pseudo}</span></td>
						<td><span class="byline">{$user.position}</span></td>
					</tr>
					{/foreach}
				</table>
				<p>
					<input type="button" name="enableVideo" value="Activer la vidéo"
						onclick="enableVideo()" class="button" />
				</p>

				<p>
					<img src="{$imageUserPlaying}" alt="Avatar" />
				</p>
				<a href="/play" class="button button-alt button-icon button-icon-rarrow">Se
			retirer de la liste d'attente</a>
				<div id="chat" class="chat">
					<table style="margin: 0">
						<tr>
							<th>Date</th>
							<th><strong>Pseudo</strong></th>
							<th>Message</th>
						</tr>
				
						{foreach $messages as $message}
						<tr>
							<td>{$message.timestamp}</td>
							<td><span class="chatText">{$message.pseudo}</span></td>
							<td>{$message.message}</td>
						</tr>
						{/foreach}
					</table>
				</div>
				<div id="chatForm">
					<form method="post" action="#">
						<div style="margin-right: 110px;">
							<input type="text" class="textarea" name="message" />
						</div>
						<div>
							<input type="submit" value="Envoyer" class="button"
								onclick="name.message.value=''" />
						</div>
					</form>
				</div>
				<div id="loader"></div>
			</article>

<head>
<script src="{$rootUrl}js/jquery-1.9.1.js"></script>
<script src="{$rootUrl}js/askplay.js"></script>
<script src="{$rootUrl}js/jquery-ui-1.10.3.dialog.js"></script>
<script src="{$rootUrl}js/jquery-ui-1.10.3.dialog.min.js"></script>
<link rel="stylesheet" href="{$rootUrl}css/ui-darkness/jquery-ui-1.10.3.dialog.css" type="text/css" />
<link rel="stylesheet" href="{$rootUrl}css/queue.css" type="text/css" />		

</head>


<div class="5grid-layout box-feature1">
	<div class="row">
		<div class="15u">
			<div id="content mobileUI-main-content">
				<header class="major">
							<h2>File d'attente</h2>
							<span class="byline">Just wait.</span>
				</header>
					</div>
						 </div>
						 	<div class="5u">
					<div id="content mobileUI-main-content">
						<table wdith=100% border=1px>
							<tr>
								<th><a class="button button-alt">Pseudo</a></th>
								<th><a class="button button-alt">Place</a></th>
							</tr>
							{foreach $arr4 as $user=>$key}
								<tr>
									<td><span class="byline">{$user}</span></td> 
									<td><span class="byline">{$key}</span></td>
								</tr>
							{/foreach}
						</table>
							</div>
						</div>
						<div class="15u">
							<div id="content mobileUI-main-content">
							<p>
							<input type="button" name="enableVideo" value="Activer la vidéo" onclick="enableVideo()" class="button" />
							</p>
						
							<p>
								 <img src="{$src}" alt="Avatar" />
							 </p>
						  </div>
					</div>
				<a href="/play" target="_self" class="button button-alt button-icon button-icon-rarrow">Se retirer de la liste d'attente</a>
			</div>
		</div>
<div id="askplay" title="Wanna play ?">
 <span class="askplay" >It's your turn!</span>
</div>

<p><a href="#" id="dialog-link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Open Dialog</a></p>

	</div>
</div>

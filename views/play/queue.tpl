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
	</div>
</div>

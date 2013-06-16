
<head>
<script src="{$rootUrl}js/jquery-ui-1.10.3.dialog.js"></script>
<script src="{$rootUrl}js/jquery-ui-1.10.3.dialog.min.js"></script>
<link rel="stylesheet" href="{$rootUrl}css/ui-darkness/jquery-ui-1.10.3.dialog.css" type="text/css" />
<link rel="stylesheet" href="{$rootUrl}css/queue.css" type="text/css" />		
</head>
<script>
var lastId={$lastId}
</script>

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



<div id="conteneur">
 <h4>It's your turn!</h4>
 <form action="#" method="post">
						    <input type="submit" value="Tchatter !" class="button button-alt button-icon button-icon-rarrow"/>
						    
						</form>
		</div>
			<div id="chat" class="chat">
			<table style="margin:0">
						<tr>
							<th>Date</th>
							<th><strong>Pseudo</strong></th>
							<th>Message</th>
						</tr>
						
							{foreach name=outer item=arr from=$data}
							 <tr>
								    {foreach key=key item=item from=$arr}
								   
								   		 <td>
								   		 {if $item==$arr['pseudo']}
								       		 <span class="chatText">{$item}</span>
								         {else}
								        	{$item}
								         {/if}
								        </td>
								    {/foreach}
								    </tr>
								{/foreach}
							</table>
						</div>

<div id="chatForm"  >
<form method="post" action="#">

	<div style="margin-right:110px;">
		<input type="text" class="textarea" name="message"/>
	</div>
	<div>
		<input type="submit" value="Envoyer" class="button" onclick="name.message.value=''"/>
	</div>
</form>
	
</div>
<div id="loader">
		
	</div>

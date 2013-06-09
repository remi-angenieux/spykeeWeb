

<h1>THE QUEUE </h1>


<a href="/play" target="_self">Se retirer de la liste d'attente</a>
<a href="/play/play" target="_self">Jouez</a>
	
	

<table wdith=100% border=1px>
<tr>
<th>Pseudo</th>
<th>Place</th>
</tr>

{foreach $arr4 as $user=>$key}
<tr>
<td>{$user} </td> 
<td>{$key}</td>
</tr>
{/foreach}
</table>

<p>
<input type="button" name="enableVideo" value="Activer la vidéo" onclick="enableVideo()" />
</p>

<p>
 <img src="{$src}" alt="Avatar" />
 </p>
 


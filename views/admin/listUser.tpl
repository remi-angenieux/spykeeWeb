<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			<article>
				<header class="major">
					<h2>Liste des Utilisateurs</h2>
				</header>
				<form method="post" action="/admin/delUser">
				
					<table wdith=\"100%\" border=\"1px\">
						<tr>
							<th><span class="byline"><a class="button button-alt">ID</a></span></th>
							<th><span class="byline"><a class="button button-alt">Name</a></span></th>
							<th><span class="byline"><a class="button button-alt">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspE-mail&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</a></span></th>
							<th><span class="byline"><a class="button button-alt">Admin</a></span></th>
							<th><span class="byline"><a class="button button-alt">Supprimer</a></span></th>
						</tr>
						
						{foreach name=outer item=arr from=$array4}
							<tr>
    							{foreach key=key item=item from=$arr}
        							<td><span class="text">{$item}&nbsp&nbsp&nbsp&nbsp</span></td>
    						    {/foreach}
    						    <td><span class="text"><input type="radio" name="id" value="{$arr['id']}"/></span></td>
							</tr>
						{/foreach}
						
						</tr>
					</table>
					
				<input type="submit" name="submit" value="Valider" class="button button-alt button-icon button-icon-rarrow" />
				</form>
			</article>
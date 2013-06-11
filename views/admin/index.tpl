	<div class="5grid-layout box-feature1">
	<div class="row">
		<div class="15u">
			<div id="content mobileUI-main-content">
			
			<header>
			<h1 class="titre">Panneau d'administrateur</h1>
			</header>
			
		<article>
			<div class="6u">
					<h3>Partie(s) en cours :</h3>
						<table>
							<tr>
								<th><img src="{$rootUrl}images/joueur.jpg" width="130" /></th>
								<th><img src="{$rootUrl}images/idJoueur.jpg" width="130" /></th>
								<th><img src="{$rootUrl}images/idRobot.jpg" width="130" /></th>
								<th><img src="{$rootUrl}images/robot.jpg" width="130" /></th>
							</tr>
							{foreach name=outer item=arr from=$array3}
								<tr>
   									{foreach key=key item=item from=$arr}
   											<td>
      	  									<span class="text">{$item}</span>
		      	  			   				</td>
		  					   		 {/foreach}
								</tr>
							{/foreach}
						</table>
				</div>
					
				<div class="4u">
		 				<h3>Etats des robots</h3>
		
						<table>
							<tr>
								<span class="byline"><th><img src="{$rootUrl}images/id.jpg" width="130" /></th></span>
								<span class="byline"><th><img src="{$rootUrl}images/name.jpg" width="130" /></th></span>
								<span class="byline"><th><img src="{$rootUrl}images/ip.jpg" width="130" /></th></span>
								<span class="byline"><th><img src="{$rootUrl}images/port.jpg" width="130" /></th></span>
								<span class="byline"><th><img src="{$rootUrl}images/locked.jpg" width="130" /></th></span>
								<span class="byline"><th><img src="{$rootUrl}images/used.jpg" width="130" /></th></span>
							<tr>
									{foreach name=outer item=arr from=$array}
										<tr>
							  				{foreach key=key item=item from=$arr}
							        			<td>
							        			<span class="text">{$item}&nbsp&nbsp&nbsp</span>
							        			</td>
							  				{/foreach}
										</tr>
									{/foreach}
							</table>
						
					
		
		<br />
		
									</div>
</article>
				
				<div class="6u">
		 				<form action="/admin/changePass" method="post">
		 				<input name="pass" type="password" >
							<select name="pseudo">
								{foreach name=outer item=arr from=$array5}
    								{foreach key=key item=item from=$arr}
        								<option value="{$item}">{$item}</option>
    								{/foreach}
								{/foreach}
							</select>
				   		    <blockquote><input type="submit" value="Changer le mot de passe" class="button button-alt button-icon button-icon-rarrow"/></blockquote>
						</form><br />
							
								<form action="/admin/addAdmin" method="post">
								    <input  name="addAdmin" type=text id="addAdmin">
								    <blockquote><input type="submit" value="Ajouter un administrateur" class="button button-alt button-icon button-icon-rarrow"/></blockquote>
								    
								</form>
								<br />
								
						
						<form action="/admin/block" method="post">
							<select name="block">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
						    <input type="submit" value="Bloquer le robot"  class="button button-alt button-icon button-icon-rarrow"/>
						</form>
								
						<form action="/admin/deblock" method="post">
							<select name="deblock">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
						    <input type="submit" value="Débloquer le Robot" class="button button-alt button-icon button-icon-rarrow"/>
						</form>
								
						<form action="/admin/setNotUsed" method="post">
							<select name="setNotUsed">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
						    <input type="submit" value="Désassocier le Robot" class="button button-alt button-icon button-icon-rarrow"/>
						</form>
								
						<form action="/admin/delRobot" method="post">
							<select name="delRobot">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
	 						</select>
						    <input type="submit" value="Supprimer le Robot" class="button button-alt button-icon button-icon-rarrow" />
						</form>
								
						<form action="/admin/takeControlAs" method="post">
							<select name="takeControl">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
								    <input type="submit" value="Contrôler le Robot" class="button button-alt button-icon button-icon-rarrow"/>
						</form>
								
								
																		</div>
																		
								<article>
								
								  	<div class="2u">
								
								<form action="/admin/addRobot" method="post">
								    <p> <span class="text">Id du robot</span> <p>
								    <input name="addId" type=text id="addId">
								    <p><span class="text"> Nom du robot </span><p>
								    <input name="addName" type=text id="addName">
								    <p><span class="text"> Adresse IP du robot</span> <p>
								    <input name="addCtrip" type=text id="addCtrip">
								    <p> <span class="text">Port du robot </span><p>
								    <input name="addCtrport" type=text id="addCtrport">
								    <input type="submit" value="Ajouter Robot" class="button button-alt button-icon button-icon-rarrow" />
								</form>
								 

										</div>
									
								<div class="3u">
					      <br /><br /><br /><br />
								<form action="/admin/modifyRobot" method="post">
								    <p> <span class="text">Modifier le Nom du robot </span><p>
								    <input name="modifyName" type=text id="modifyName" >
								    <p> <span class="text">Modifier l'adresse IP du robot </span><p>
								    <input name="modifyCtrip" type=text id="modifyCtrip" >
								    <p> <span class="text">Modifier le Port du robot</span> <p>
								    <input name="modifyCtrport" type=text id="modifyCtrport" >
								     <input type="submit" value="Modifier Robot" class="button button-alt button-icon button-icon-rarrow"/>
								   <span class="textpasalign"> Du robot ayant l'Id :</span>
								<select name="modify">
								{foreach name=outer item=arr from=$array2}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
								</select>
								</form>
										</div>
									</article>
									</div>
								</div>
							</div>
						</div>
				
		
	
	
	
	
	
	
	
	
	







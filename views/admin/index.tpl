	<div class="5grid-layout box-feature1">
	<div class="row">
		<div class="18u">
			<div id="content mobileUI-main-content">
			
			<header>
			<h1 class="titre">Panneau d'administrateur</h1>
			</header>
			
		<article>
			<div class="6u">
					<h3>Partie(s) en cours :</h3>
						<table>
							<tr>
								<th><span class="byline"><a class="button button-alt">ID Joueur</a></span></th>
								<th><span class="byline"><a class="button button-alt">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspJoueur&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</a></span></th>								
								<th><span class="byline"><a class="button button-alt">ID Robot</a></span></th>
								<th><span class="byline"><a class="button button-alt">Robot</a></span></th>
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
				
						<div class="6u">
					<h3>File d'attente :</h3>
						<table>
							<tr>
								<th><span class="byline"><a class="button button-alt">ID Joueur</a></span></th>
								<th><span class="byline"><a class="button button-alt">Joueur</a></span></th>								
								<th><span class="byline"><a class="button button-alt">Temps d'attente</a></span></th>
								<th><span class="byline"><a class="button button-alt">Place</a></span></th>
							</tr>
							{foreach name=outer item=arr from=$file}
								<tr>
   									{foreach key=key item=item from=$arr}
   											<td>
      	  									<span class="text">{$item}</span>
		      	  			   				</td>
		  					   		 {/foreach}
								</tr>
							{/foreach}
						</table>
								<form action="/admin/puOutOfQueue" method="post">
							<select name="block">
								{foreach name=outer item=arr from=$member_queue}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
						    <input type="submit" value="Enlever de la file d'attente"  class="button button-alt button-icon button-icon-rarrow"/>
						</form>
				</div>
					
				<div class="4u">
		 				<h3>Etats des robots</h3>
		
						<table>
							<tr>
								<th><span class="byline"><a class="button button-alt">Id</a></span></th>
								<th><span class="byline"><a class="button button-alt">Name</a></span></th>
								<th><span class="byline"><a class="button button-alt">&nbsp&nbsp&nbsp&nbspIP&nbsp&nbsp&nbsp&nbsp</a></span></th>
								<th><span class="byline"><a class="button button-alt">Port</a></span></th>
								<th><span class="byline"><a class="button button-alt">Locked</a></span></th>
								<th><span class="byline"><a class="button button-alt">Used</a></span></th>
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
		 				<form method="post">
		 				<input name="pass" type="password" >
							<select name="pseudo">
								{foreach name=outer item=arr from=$array5}
    								{foreach key=key item=item from=$arr}
        								<option value="{$item}">{$item}</option>
    								{/foreach}
								{/foreach}
							</select>
				   		    	<blockquote><button type="submit" formaction="/admin/ChangePass" class="button button-alt ">Changer de mot de passe</button></blockquote>
								<blockquote><button type="submit" formaction="/admin/addAdmin" class="button button-alt ">Ajouter l' administrateur</button></blockquote>
								<button type="submit" formaction="/admin/delAdmin" class="button button-alt ">Enlever l' administrateur</button>
						</form>
							<br />
								</div>
						<div class="6u">
						<form method="post">
							<select name="robot">
								{foreach name=outer item=arr from=$array1}
								    {foreach key=key item=item from=$arr}
								        <option value="{$item}">{$item}</option>
								    {/foreach}
								{/foreach}
							</select>
							<br />
						    <button type="submit" formaction="/admin/deblock" class="button button-alt ">debloquer le robot</button>
						    <button type="submit" formaction="/admin/block" class="button button-alt ">bloquer le robot</button>
						    <button type="submit" formaction="/admin/SetNotUsed" class="button button-alt ">désassocier le robot</button>
						    <button type="submit" formaction="/admin/delRobot" class="button button-alt ">supprimer le robot</button>
						    <button type="submit" formaction="/admin/takeControlAs" class="button button-alt ">prendre le controle du robot</button>
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
				
		
	
	
	
	
	
	
	
	
	







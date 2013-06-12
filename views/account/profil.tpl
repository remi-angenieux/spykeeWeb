<div class="5grid-layout">
	<div class="row">
		<div class="12u mobileUI-main-content">
			<div id="content">
			<!-- Content -->
			
<section class="5grid-layout box-feature1">
	<div class="row">
		<div class="12u">
			<header class="first major">
				<h2>Profil de {$username}</h2>
				<span class="byline">Vous pouvez modifier votre profil <strong>OU</strong> regarder votre historique</span>
					<section>		
				<form action="/account/visitProfil" method="post">
					<select name="pseudo">
						{foreach name=outer item=arr from=$array2}
							{foreach key=key item=item from=$arr}
								<option value="{$item}">{$item}</option>
							{/foreach}
						{/foreach}
					</select>
    				<input type="submit" value="Visiter le profil" class="button button-alt button-icon button-icon-rarrow"/>
				</form>
			</section>	
			</header>
		</div>
	</div>
</section>


			
			
	<div class="5grid-layout">
	<div class="row">
		<div class="6u">
			<div id="content mobileUI-main-content">
			<h3>Vos paramètres</h3>
			
			<section>
					
					<table>
					{foreach name=outer item=arr from=$array}

   							{foreach key=key item=item from=$arr}
   							    <tr>
   							   		<td>
   										<span class="byline">{$key}:&nbsp&nbsp</span>
   									</td>
   									<td>
      	  								<span class="text"> {$item} </span>
      	  			   				</td>
      	  			   			</tr>
  					   		 {/foreach}

					{/foreach}
				</table>
			</section>
			<section>
				<p>
					<img src="{$src}" alt="Avatar" />
				</p>
 			</section>
			
			<article class="blocGauche" >
			<section>								
				<span class="text">Modifier votre mot de passe</span>
				<form action="/account/changePass" method="post">
  					<input name="pass" type="password" ><br />
  					<input type="submit" value="Validez" />
				</form>
			</section>
			<section>	
				<span class="text">Modifier votre e-mail</span>
				<form action="/account/changeMail" method="post">
 					<input name="email" type=text ><br />
    				<input type="submit" value="Validez" />
				</form>
			</section>
		
			<section>	
				<span class="text">Modifier votre Avatar</span>
				<form action="/account/uploadImg" enctype="multipart/form-data" method="post" >
					<input type="file" name="icone" /><br />
					<input type="submit" value="Validez" />
				</form>
			</section>
			</article>
			
			
			
		

			</div>
		</div>
		<div class="4u">
			<div id="sidebar">
			
									
			<article>
 				<h3>Historique des Parties</h3>

				<table wdith=\"100%\" border=\"1px\">
					<tr>
						<th><span class="byline">Robot&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span></th>
						<th><span class="byline">Durée (sec)&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span></th>
						<th><span class="byline">Date<blockquote></blockquote></span></th>
					</tr>
					{foreach name=outer item=arr from=$array1}
						<tr>
    						{foreach key=key item=item from=$arr}
        						<td>{$item}</td>
    						{/foreach}
						</tr>
					{/foreach}

				</table>
				<form action="/account/delHistory" method="post">
   					 <input type="submit" value="Effacer l'historique" class="button button-alt button-icon button-icon-rarrow" />
				</form>
			</article>



										</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			
			

 
 


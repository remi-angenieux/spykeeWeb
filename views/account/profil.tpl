<h1>Profil</h1>

{foreach name=outer item=arr from=$array}
    <hr />
    {foreach key=key item=item from=$arr}
        {$key}: {$item}<br />
    {/foreach}
{/foreach}

<form action="/account/changePass" method="post">
 <input name="pass" type="password" >
    <input type="submit" value="Changer le mot de passe" />
</form>

<form action="/account/changeMail" method="post">
 <input name="email" type=text >
    <input type="submit" value="Changer son e-mail" />
</form>



<form action="/account/uploadImg" enctype="multipart/form-data" method="post" >
<input type="file" name="icone" />
<input type="submit" value="Uploader l'image" />
</form>
<p>
 <img src="{$src}" alt="Avatar" />
 </p>
 
 <h1>Historique des Parties</h1>

</table>

<table wdith=\"100%\" border=\"1px\">
<tr>
<th>Robot</th>
<th>Durée</th>
<th>Date</th>
</tr>


{foreach name=outer item=arr from=$array1}
<tr>
    {foreach key=key item=item from=$arr}
        <td>{$item}</td>
    {/foreach}
</tr>
{/foreach}
</tr>
</table>

<form action="/account/delHistory" method="post">
    <input type="submit" value="Effacer l'historique" />
</form>

<form action="/account/visitProfil" method="post">
<select name="pseudo">
{foreach name=outer item=arr from=$array2}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Visiter le profil" />
</form>

<a href="/account/logout" target="_self">Se déconnecter</a></br>
<a href="/home" target="_self">Retourner a l'acceuil</a></br>

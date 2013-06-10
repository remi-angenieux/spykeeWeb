<h1>Panneau d'Administrateur de spykee</h1>	
<p>Etats des robots</p>

<table wdith=\"100%\" border=\"1px\">
<tr>
<th>Id</th>
<th>Name</th>
<th>IP</th>
<th>Port</th>
<th>Locked</th>
<th>Used</th>
</tr>

{foreach name=outer item=arr from=$array}
<tr>
    {foreach key=key item=item from=$arr}
        <td>{$item}</td>
    {/foreach}
</tr>
{/foreach}
</table>

<p>Partie(s) en cours :</p>
<table wdith=\"100%\" border=\"1px\">
<tr>
<th>Id joueur</th>
<th>Joueur</th>
<th>Id robot</th>
<th>Robot</th>
</tr>


{foreach name=outer item=arr from=$array3}
<tr>
    {foreach key=key item=item from=$arr}
        <td>{$item}</td>
    {/foreach}
</tr>
{/foreach}
</tr>
</table>

<p>Membres :</p>
<table wdith=\"100%\" border=\"1px\">
<tr>
<th>Id </th>
<th>Nom </th>
<th>email</th>
<th>Admin</th>
</tr>


{foreach name=outer item=arr from=$array4}
<tr>
    {foreach key=key item=item from=$arr}
        <td>{$item}</td>
    {/foreach}
</tr>
{/foreach}
</tr>
</table>
&nbsp;

<form action="/admin/changePass" method="post">
<select name="pseudo">
{foreach name=outer item=arr from=$array5}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
 <input name="pass" type="password" >
    <input type="submit" value="Changer le mot de passe" />
</form>


&nbsp;
<form action="/admin/block" method="post">
<select name="block">
{foreach name=outer item=arr from=$array1}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Bloquer le robot" />
</form>

<form action="/admin/deblock" method="post">
<select name="deblock">
{foreach name=outer item=arr from=$array1}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Débloquer le Robot" />
</form>

<form action="/admin/setNotUsed" method="post">
<select name="setNotUsed">
{foreach name=outer item=arr from=$array1}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Désassocier le Robot" />
</form>

<form action="/admin/delRobot" method="post">
<select name="delRobot">
{foreach name=outer item=arr from=$array1}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Supprimer le Robot" />
</form>

<form action="/admin/takeControl" method="post">
<select name="takeControl">
{foreach name=outer item=arr from=$array1}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    <input type="submit" value="Contrôler le Robot" />
</form>

  
<div>
<form action="/admin/modifyRobot" method="post">
    <p> Modifier le Nom du robot <p>
    <input name="modifyName" type=text id="modifyName">
    <p> Modifier l'adresse IP du robot <p>
    <input name="modifyCtrip" type=text id="modifyCtrip">
    <p> Modifier le Port du robot <p>
    <input name="modifyCtrport" type=text id="modifyCtrport">
    </br>
    Du robot ayant l'Id :
<select name="modify">
{foreach name=outer item=arr from=$array2}
    {foreach key=key item=item from=$arr}
        <option value="{$item}">{$item}</option>
    {/foreach}
{/foreach}
</select>
    </br>
    <input type="submit" value="Modifier Robot" />
</form>
</div>
&nbsp;
<div>
<form action="/admin/addRobot" method="post">
    <p> Id du robot <p>
    <input name="addId" type=text id="addId">
    <p> Nom du robot <p>
    <input name="addName" type=text id="addName">
    <p> Adresse IP du robot <p>
    <input name="addCtrip" type=text id="addCtrip">
    <p> Port du robot <p>
    <input name="addCtrport" type=text id="addCtrport">
    </br>
    <input type="submit" value="Ajouter Robot" />
</form>
</div>
&nbsp;
<form action="/admin/addAdmin" method="post">
    <input name="addAdmin" type=text id="addAdmin">
    <input  type="submit" value="Ajouter un administrateur" />
    <p> Saisir son pseudo <p>
</form>


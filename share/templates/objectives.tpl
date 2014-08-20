{extends file="base.tpl"}

{block name=title}{$page_title}{/block}
{block name=description}{$smarty.block.parent}{/block}
{block name=nav}{$smarty.block.parent}{/block}

{block name=additional_scripts}{$smarty.block.parent}{/block}
{block name=additional_stylesheets}{$smarty.block.parent}{/block}

{block name=content} 

<div class=" border-radius gray-border">	
    <div class="border-top-radius contentheader ">{$page_title}{*<div class="printbtn floatright" onclick="printPage('printContent');"> </div>*}</div>
    <div class="space-top-padding gray-gradient border-bottom-radius box-shadow ">
      
        {if isset($user->avatar) and $user->avatar != 'noprofile.jpg'}
            <div id="right">
                <img class="border-radius gray-border" src="{$avatar_url}{$user->avatar}" alt="Profilfoto">
            </div>
        {/if}    
        {if isset($courses)}
          <p>
              <select {*class="makeMeFancy"*} id='course' name='course' onchange="window.location.assign('index.php?action=objectives&course='+this.value);"> {*_blank global regeln*}
                  <option value="-1" data-skip="1">Lehrplan wählen...</option>
                  {section name=res loop=$courses}
                    <option value="{$courses[res]->id}" 
                    {if $courses[res]->id eq $selected_curriculum} selected {/if} 
                    data-icon="{$data_url}subjects/{$courses[res]->icon}" data-html-text="{$courses[res]->group} - {$courses[res]->curriculum}&lt;i&gt;
                    {$courses[res]->description}&lt;/i&gt;">{$courses[res]->group} - {$courses[res]->curriculum}</option>  
                  {/section} 
              </select>    </p>
        {else}<p><strong>Sie haben noch keine Lehrpläne angelegt bzw. noch keine Klassen eingeschrieben.</strong></p>{/if}
          <p>&nbsp;</p>
         
        {if isset($userPaginator)}   
        {* display pagination header *}
        <p>Datensätze {$userPaginator.first}-{$userPaginator.last} von {$userPaginator.total} werden angezeigt.</p>
    		<table id="contentsmalltable">
		<tr id="contenttablehead">
			<td></td>
                        <!--<td>Benutzername</td>-->
                        <td>Vorname</td>
                        <td>Nachname</td>
                        <td>erledigt</td>
		</tr>
                {* display results *}    
                {section name=res loop=$results}
                    <tr class="{if isset($selected_user_id) AND $selected_user_id eq $results[res]->id} activecontenttablerow {else}contenttablerow{/if}{if $results[res]->completed eq 100} completed{/if}" id="row{$smarty.section.res.index}" onclick="window.location.assign('index.php?action=objectives&course='+document.getElementById('course').value+'&userID='+document.getElementById('userID{$smarty.section.res.index}').value);">
                       <td><input class="invisible" type="checkbox" id="userID{$smarty.section.res.index}" name="userID" value={$results[res]->id} {if isset($selected_user_id) AND $selected_user_id eq $results[res]->id} checked{/if}/></td>
                       <!--<td>{$results[res]->username}</td>-->
                       <td>{$results[res]->firstname}</td>
                       <td>{$results[res]->lastname}</td>
                       <td>{$results[res]->completed}</td>
                    </tr>
                {/section}
		</table>
        {* display pagination info *}
        <p>{paginate_prev id="userPaginator"} {paginate_middle id="userPaginator"} {paginate_next id="userPaginator"}</p>       
       
         <!--Hack für problem, dass kein Array gepostet wird, wenn nichts angewählt wird-->
        <input class="invisible" type="checkbox" name="userID" value="none" checked />
        <!-- Ende Hack -->
      {elseif $showuser eq true} <p>Keine eingeschriebenen Benutzer</p><p>&nbsp;</p>{/if}
        {if $show_course != '' and $terminalObjectives != false or !isset($selected_user_id)} 
        {* course anzeigen *}
         <form method='post' action='index.php?action=objectives&next={$currentUrlId}'>
            <input type='hidden' name='sel_curriculum' value='{$sel_curriculum}'/>
            <input type='hidden' name='sel_user_id' value='{$selected_user_id}'/>
            <input type='hidden' name='sel_group_id' value='{$sel_group_id}'/>
            <p><input type='submit' name="printCertificate" value='Zertifikat erstellen' /> 
            <input type='submit' name="printAllCertificate" value='Alle Zertifikate erstellen' /></p>
         </form>
        <div id="printContent" class="scroll">
            <!--For printing only-->
            
            {*<div class="printOnly" >
                <div id="printHeader"><p>{$app_title}</p></div>
                <div id="printUser">
                    <!--<div class="printUserHeader">Benutzer</div>-->
                    <div><img id="printUserImg" src="{$avatar_url}{$user_avatar}"></div>
                    <div><p><strong>{$user->firstname} {$user->lastname}</strong></p>
                    <p>{$group[0]->group}</p>
                    <p>{$group[0]->semester}</p>
                    <p class="printUserLogin">Letzter Login: {$last_login}</p></div> 
                
              </div>*}
             <!-- end For printing only--> 
             <table> <!-- sollte per css noch unten einen abstand zum nächsthöheren div bekommen-->
                {foreach key=terid item=ter from=$terminalObjectives}
                    <tr><td class="boxleftpadding"><div class="box gray-gradient border-radius box-shadow gray-border ">
                                <div class="boxheader border-top-radius"></div>
                                <div class="boxwrap">
                                    <div class="boxscroll">
                                        <div class="boxcontent">
                                            {$ter->terminal_objective}<!--{$ter->description}-->
                                        </div>
                                    </div>
                                </div>
                                <div class="boxfooter border-bottom-radius"><!--Options...--></div> 
                            </div>
                        </td>
                        {foreach key=enaid item=ena from=$enabledObjectives}
                        {if $ena->terminal_objective_id eq $ter->id}
                        <td id="{$ter->id}&{$ena->id}">
                            <div style="display:none" id="{$ter->id}_{$ena->id}">{0+$ena->accomplished_status_id}</div><!--Container für Variable-->
                            <div id="{$ter->id}style{$ena->id}" class="box gray-gradient border-radius box-shadow gray-border {if $ena->accomplished_status_id eq 1} boxgreen {else} boxred{/if}">
                            <div class="boxheader border-top-radius "></div>
                        <div class="boxwrap">
                                    <div class="boxscroll" onclick="setAccomplishedObjectives({$my_id}, {$selected_user_id}, {$userPaginator.first}, {if isset($paginatorLimit)}{$paginatorLimit}{else}10{/if}, {$ter->id}, {$ena->id})">
                                    <div class="boxcontent" >
                                         {$ena->enabling_objective}
                            <!--<br>{$ena->description}-->
                                    </div>
                                    </div>
                                </div>
                           
                            <div class="boxfooter border-bottom-radius" onclick="">
                                
                               {*Abgaben zum jeweiligen Ziel in pulldownmenü - muss in einer nächsten Version gemacht werden*}
                               {if $addedSolutions != false} 
                                    {assign var="firstrun" value="true"} 

                                    {foreach key=solID item=sol from=$addedSolutions}
                                        {if $sol->enabling_objective_id eq $ena->id}
                                            {if $firstrun eq "true"}
                                                {if $page_browser != 'Safari'} {*For cross browser compatibility*}
                                                    <select class="selSolution" name="select_{$ena->id}" onchange="openLink(this.options[this.selectedIndex].value, '_blank');"> 
                                                {else}
                                                    <select class="selSolution" name="select_{$ena->id}" onclick="openLink(this.options[this.selectedIndex].value, '_blank');"> 
                                                {/if}
                                                <option value="">Abgaben...</option>
                                                {assign var="firstrun" value="false"}
                                            {/if}
                                            <option value="{$data_url}solutions/{$sol->path}{$sol->filename}">({$sol->lastname}, {$sol->firstname}) {$sol->filename}{$sol->filetype}</a></option>
                                        {/if}
                                    {/foreach}
                                    </select>
                                {/if} <!--Options...--></div> 
                        
                            
                        </td>
                        </div>{/if}
                        {/foreach} 
                </tr>
            {/foreach}		
        </table>
        <p>&nbsp;</p>
        </div>   
        <!--For printing only-->
        {*<div id="printFooter" >
            <table>
                <tr>
                    <td><p>Erklärungen:</p></td>
                    <td><div class="boxgreen boxlegende"></div></td>
                    <td><p>Ziel wurde erreicht</p>
                    <td><div class="boxred boxlegende"></div></td>
                    <td><p>Ziel wurde noch nicht erreicht</p></td>
                </tr>
            </table>  
        </div>
        <!--end For printing only--> *}
        {else}
            {if isset($selected_user_id) and $show_course != ''}
                <p>Es wurden noch keine Lernziele eingegeben.</p>
                <p>Dies können sie unter Lehrpläne --> Lernziele/Kompetenzen hinzufügen machen.</p>
                <p>&nbsp;</p>
            {else} 
                {if isset($curriculum_id)}<!--Wenn noch keine Lehrpläne angelegt wurden-->
                <p>Bitte wählen sie einen Benutzer aus.</p>
                {/if}
            <p>&nbsp;</p>
            {/if}
        {/if}
        {* Ende course anzeigen*}          
    </div>
</div>
{/block}

{block name=sidebar}{$smarty.block.parent}{/block}
{block name=footer}{$smarty.block.parent}{/block}

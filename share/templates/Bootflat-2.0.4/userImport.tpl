{extends file="base.tpl"}

{block name=title}{$page_title}{/block}
{block name=description}{$smarty.block.parent}{/block}
{block name=nav}{$smarty.block.parent}{/block}

{block name=additional_scripts}{$smarty.block.parent}{/block}
{block name=additional_stylesheets}{$smarty.block.parent}{/block}

{block name=content}  
<!-- Content Header (Page header) -->
{content_header p_title=$page_title pages=$breadcrumb help='http://docs.joachimdieterich.de/index.php?title=Benutzerliste_importieren'}  
<!-- Main content -->
    <div class="row">
        <div class="col-xs-12">
            <div class="panel ">
                <div class="panel-heading">
                    <h4>Benutzerdaten per CSV importieren </h4>
                </div><!-- /.box-header -->
                <form name="file" enctype="multipart/form-data" action="index.php?action=userImport" method="post">                 
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-7">
                                <p>Die CSV-Datei muss folgendes Format haben:<br/>
                                - Die ersten Zeile muss die Schlüsselwerte enthalten (z.B.:username, password, firstname, lastname, email, role_id, confirmed, postalcode, city, state, country)<br/>
                                - Die Schüsselwerte <strong>username, password, firstname, lastname </strong>und <strong>email</strong> müssen gesetzt werden. <br/>
                                - Zusätzlich kann das Feld <strong>group_id</strong> definiert werden. Mögliche ID-Werte für <strong>group_id</strong> und <strong>role_id</strong> finden Sie in den Tabellen.<br/>
                                - Die maximale Dateigröße liegt bei {$filesize}MB und kann im Adminstrationsbereich festgelegt werden.<br/>
                                - Die Datei muss im utf-8 Format gespeichert werden, sonst werden Umlaute und Sonderzeichen nicht korrekt importiert</p>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-5">
                                <h4>CSV-Vorlagen</h4>
                                <a class="btn btn-app" href="{$support_path}Vorlage-min.csv"><i class="fa fa-save"></i> Minimal - nur benötigte Felder</a>
                                <a class="btn btn-app" href="{$support_path}Vorlage-max.csv"><i class="fa fa-save"></i> Maximal - alle möglichen Felder</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class=" col-xs-12 col-sm-12 col-md-5 col-lg-5">
                                <p>{Form::input_select('institution_id', 'Institution', $my_institutions, 'institution', 'institution_id', $my_institution_id, null, 'getMultipleValues([\'group\', this.value, \'group_id\'], [\'group\', this.value, \'group_table\', \'table\']); ')}</p>
                                <p>{Form::input_select('role_id', 'Rolle', $roles, 'role', 'id', $role_id, null)}</p>
                                <p>{Form::input_select('group_id', 'Lerngruppe', $groups, 'group', 'id', null, null)}</p>
                                <p>{Form::input_text('delimiter', 'Trennzeichen', $delimiter, null)}</p>
                                <p>
                                  <label for="exampleInputFile">CSV-Datei hochladen</label>
                                  <input name="datei" type="file" value="">
                                </p> 
                                <button type="submit" class="btn btn-primary">Importieren</button>
                            </div>
                            <span id='group_table'>{Render::table($group_table_params)}</span> 
                            <span id='role_talbe'>{Render::table($role_table_params)}</span>
                        </div> 
                    </div>
                </form>
            </div>  
        </div>  
              
        {if isset($nusr_val)}
        <div class="col-xs-12">
            <div class="panel">
                <div class="panel-heading">
                  <h3 class="panel-title">Neue Nutzer</h3>
                </div><!-- /.box-header -->
                <div class="panel-body">
                    {html_paginator id='newUsersPaginator' title='Importiert'}     
                </div>
            </div>  
        </div> 
        {/if}        
    </div>
{/block}

{block name=sidebar}{$smarty.block.parent}{/block}
{block name=footer}{$smarty.block.parent}{/block}
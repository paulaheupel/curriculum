{extends file="base.tpl"}

{block name=title}{$page_title}{/block}
{block name=description}{$smarty.block.parent}{/block}
{block name=nav}{$smarty.block.parent}{/block}

{block name=additional_scripts}{$smarty.block.parent}{/block}
{block name=additional_stylesheets}{$smarty.block.parent}{/block}

{block name=content}
<!-- Content Header (Page header) -->
{content_header p_title=$page_title pages=$breadcrumb help='http://docs.joachimdieterich.de/index.php?title=Klassenstufen_anlegen'}  

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-body">  
                {if checkCapabilities('grade:add', $my_role_id, false)}
                <div class="btn-group" role="group" aria-label="...">
                    <button type="button" class="btn btn-default" onclick="formloader('grade','new')"><a href="#">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Klassenstufe hinzufügen</a>
                    </button>
                </div>
                {/if}
                {html_paginator id='gradeP'}
                </div>
            </div>
        </div>
    </div>
</section>    
{/block}

{block name=sidebar}{$smarty.block.parent}{/block}
{block name=footer}{$smarty.block.parent}{/block}
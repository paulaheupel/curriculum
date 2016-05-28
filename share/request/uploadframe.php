<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
 * 
 * @package core
 * @filename uploadframe.php
 * @copyright 2013 Joachim Dieterich
 * @author Joachim Dieterich
 * @date 2013.03.08 13:26
 * @license: 
 * 
* This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by  
* the Free Software Foundation; either version 3 of the License, or (at your option) any later version.                                   
*                                                                       
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of        
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details:                          
*                                                                       
* http://www.gnu.org/copyleft/gpl.html      
*/


include_once '../setup.php'; //Läd alle benötigten Dateien
global $CFG, $PAGE, $USER, $LOG;

if (!isset($_SESSION['USER'])){ die(); }    // logged in?
$USER = $_SESSION['USER'];                  // $USER not defined but required on 


/* set defaults */
$file       = new File();
$curID      = null;
$terID      = null; 
$enaID      = null;
$target     = null;         // id of target field
$format     = null;         // return format 0 == file_id; 1 == file_name; 2 == filePath / URL
$multiple   = null;         // upload multiple files // not used yet  false == returns one file, true = returns array of files_id/file_name/file_path (depends on $format)
$context    = null; 
$title      = null; 
$description= null; 
$author     = $USER->firstname.' '.$USER->lastname;
$license    = 2;
$url        = null; 
$action     = 'upload';


$error      = '';
$image      = '';
$copy_link  = '';

/* get url parameters */
foreach ($_GET  as $key => $value) { $$key = $value; } 
/* get form data */
foreach ($_POST as $key => $value) { $$key = $value; }
?>

<!-- HTML -->
<!--script type="text/javascript" src="../../public/assets/scripts/jquery-2.2.1.min.js"></script-->
<!--script type="text/javascript" src="../../public/assets/scripts/script.js"></script-->
<script type="text/javascript" src="../../public/assets/scripts/uploadframe.js"></script>


<div class="uploadframeClose" onclick="self.parent.tb_remove();"></div>    
<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close nyroModalClose" data-dismiss="modal" aria-label="Close" ><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Dateiauswahl</h4>
    </div>
    <div class="modal-body" style="min-height: 450px !important;"> <!-- to do recalc nyroModal on changes--> 
        <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar" style="padding-top:0px !important;">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
              <!--<li class="header">Menü</li>-->
                <?php 
                $values = array (0 => array('capabilities' =>  'file:upload',           'id' =>  'fileuplbtn',          'name' => 'Datei hochladen',      'class' => 'fa  fa-upload',    'action' => 'upload'), 
                                 1 => array('capabilities' =>  'file:uploadURL',        'id' =>  'fileURLbtn',          'name' => 'Datei-URL verknüpfen', 'class' => 'fa  fa-link',      'action' => 'url'), 
                                 2 => array('capabilities' =>  'file:lastFiles',        'id' =>  'filelastuploadbtn',   'name' => 'Letzte Dateien',       'class' => 'fa  fa-files-o',   'action' => 'lastFiles'), 
                                 3 => array('capabilities' =>  'file:curriculumFiles',  'id' =>  'curriculumfilesbtn',  'name' => 'Aktueller Lehrplan',   'class' => 'fa  fa fa-th',     'action' => 'curriculumFiles'), 
                                 4 => array('capabilities' =>  'file:solution',         'id' =>  'solutionfilesbtn',    'name' => 'Meine Abgaben',        'class' => 'fa  fa-clipboard', 'action' => 'mySolutions'), 
                                 5 => array('capabilities' =>  'file:myFiles',          'id' =>  'myfilesbtn',          'name' => 'Meine Dateien',        'class' => 'fa  fa-user',      'action' => 'myFiles'), 
                                 6 => array('capabilities' =>  'file:myAvatars',        'id' =>  'avatarfilesbtn',      'name' => 'Meine Profilbilder',   'class' => 'fa  fa-user',      'action' => 'myAvatars')
                );
                foreach($values as $value){
                    if (checkCapabilities($value['capabilities'], $USER->role_id, false)){ //don't throw exeption!?>
                        <li class="treeview <?php if ($action == $value['action']){echo 'active';}?>" >
                            <a id="<?php echo $value['id']?>" href="../share/request/uploadframe.php?action=<?php echo $value['action']?>" class="nyroModal">
                                <i class="<?php echo $value['class']?>"></i> <span><?php echo $value['name']?></span>
                            </a>
                        </li> <?php 
                    }
                } ?>
            
            <div id="div_FilePreview" style="display:none;">
                <img id="img_FilePreview" src="" alt="Vorschau">
            </div>
          </ul>
        </section>
      </aside>
      
      <div class="content-wrapper" >
        <?php if ($action == 'upload' OR $action == 'url'){ ?>
        <div class="box box-widget">
          <!--?php echo $action;  echo var_dump($action); ?-->
          <form id="uploadform" action="uploadframe.php" class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
            <p><input id="context" name="context" type="hidden" value="<?php echo $context; ?>" /></p> <!-- context = von wo wird das Uploadfenster aufgerufen-->
            <p><input id="curID"   name="curID" type="hidden" value="<?php   echo $curID; ?>" /></p>
            <p><input id="terID" name="terID" type="hidden" value="<?php   echo $terID; ?>" /></p>
            <p><input id="enaID" name="enaID" type="hidden" value="<?php   echo $enaID; ?>" /></p> <?php
            echo Form::input_text('title', 'Titel', $title, $error, 'z. B. Diagramm eLearning'); 
            echo Form::input_text('description', 'Beschreibung', $description, $error, 'Beschreibung'); 
            echo Form::input_text('author', 'Autor', $author, $error, 'Max Mustermann'); 
            $l = new License();
            echo Form::input_select('license', 'Lizenz', $l->get(), 'license', 'id', $license , $error);
            $c = new Context();
            echo Form::input_select('file_context', 'Freigabe-Level', $c->get(), 'description', 'id', $context , $error);
            if ($action == 'upload') { ?> 
            <span id="div_fileuplbtn">    <!-- Fileupload-->
                <?php echo Form::upload_form('uploadbtn', 'Datei hochladen', '', $error); ?>
                <p><input id="target" name="target" type="hidden" value="<?php  echo $target; ?>" /></p>
                <p><input id="format" name="format" type="hidden" value="<?php  echo $format; ?>" /></p>
                <p><input id="multiple" name="multiple" type="hidden" value="<?php echo $multiple; ?>" /></p>
                <!--p><input name="upload" type="file" size="15" /-->
                <!--input id="uploadbtn" type="submit" name="Submit" value="Datei hochladen" /-->
            </span><?php } 
            if ($action == 'url') { ?> 
            <span id="div_fileURLbtn" >     <!-- URLupload-->
                <p>URL:</p>
                <p><input type="input" class="inputlarge" name="fileURL"  value="<?php if (isset($_POST['fileURL'])){echo $_POST['fileURL'];}?>"/></p>
                <p><input type="submit" name="Submit" value="URL einfügen"  /></p>
            </span>
            <?php } ?>
            </form>    
            <p class="text ">&nbsp;<?php echo $error; ?></p>
            <div class="uploadframe_footer"><?php echo $copy_link; ?></div>
        </div><!-- /.tab-pane -->
        <?php 
        } 
        
        if ($action == 'lastFiles'){ ?>
        <div class="box box-widget">
          <?php renderList('uploadframe.php', 'user',       $CFG->access_file, '_filelastuploadbtn',   $target, $returnFormat, $multipleFiles, $USER->id);        //FileLastUpload div?>
        </div>
        <?php }
        
        if ($action == 'curriculumFiles'){ ?>
        <div class="box box-widget">
          <?php renderList('uploadframe.php', 'curriculum', $CFG->access_file, '_curriculumfilesbtn',  $target, $returnFormat, $multipleFiles, $curriculum_id);   //curriculumfiles?>
        </div>
        <?php }
        
        if ($action == 'mySolutions'){ ?>
        <div class="box box-widget">
          <?php  renderList('uploadframe.php', 'solution',   $CFG->access_file, '_solutionfilesbtn',   $target, $returnFormat, $multipleFiles, $curriculum_id);       //solutionfiles div?>
        </div>
        <?php }
        
        if ($action == 'myFiles'){ ?>
        <div class="box box-widget">
          <?php renderList('uploadframe.php', 'userfiles',  $CFG->access_file, '_myfilesbtn',          $target, $returnFormat, $multipleFiles, $USER->id);          //myfiles div?>
        </div>
        <?php }
        
        if ($action == 'myAvatars'){ ?>
        <div class="box box-widget">
          <?php renderList('uploadframe.php', 'avatar',     $CFG->access_file, '_avatarfilesbtn',      $target, $returnFormat, $multipleFiles, $USER->id);         //avatarfiles div?>
        </div>
        <?php } ?>
        </div><!--. content-wrapper -->          
    </div> <!-- .modal-body -->
</div>
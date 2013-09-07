<?php

/** This file is part of curriculum - http://www.joachimdieterich.de
 * 
 * @package core
 * @filename request.php - handels XML requests
 * @copyright 2013 Joachim Dieterich
 * @author Joachim Dieterich
 * @date 2013.03.08 13:26
 * @license: 
*
* This program is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by  
* the Free Software Foundation; either version 3 of the License, or     
* (at your option) any later version.                                   
*                                                                       
* This program is distributed in the hope that it will be useful,       
* but WITHOUT ANY WARRANTY; without even the implied warranty of        
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         
* GNU General Public License for more details:                          
*                                                                       
* http://www.gnu.org/copyleft/gpl.html      
*/



$configfile = dirname(__FILE__).'/../../../share/config.php'; //damit zugriff auf db funktioniert
$setupfile = dirname(__FILE__).'/../../../share/setup.php'; //damit zugriff auf db funktioniert
$functionfile = dirname(__FILE__).'/../../../share/function.php'; //damit zugriff auf die funktionen funktioniert
include($configfile); 
include($setupfile); //damit die *.classen.php funktionieren
include($functionfile); 

global $CFG;

// Database config
$conn = mysql_connect($CFG->db_host,$CFG->db_user,$CFG->db_password)
	or die('Error connecting to mysql');
    mysql_select_db($CFG->db_name);

// Configure Timezone $$$ You may want to change this otherwise php will complain
date_default_timezone_set('Europe/Berlin');

if (isset($_GET['function'])){
    switch ($_GET['function']) {
        case "showMaterial": 
                            if (isset($_GET['ajax'])) {
                                $file = new File(); 
                                $files = $file->getFiles('enabling_objective', $_GET['enablingObjectiveID']);
                                echo '<div class="border-top-radius contentheader">Material</div>
                                      <div id="popupcontent">';
                                if (!$files){
                                    echo 'Es gibt leider kein Material zum gewählten Lernziel.';
                                } else {
                                    for($i = 0; $i < count($files); $i++) {  
                                        echo '<input type="hidden" name="id" id="id" value='.$files[$i]->id.'/>
                                        <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$_GET['curriculumID'].'/>
                                        <input type="hidden" name="terminal_objective_id" id="terminal_objective_id" value='.$_GET['terminalObjectiveID'].'/> 
                                        <input type="hidden" name="enabling_objective_id" id="enabling_objective_id" value='.$_GET['enablingObjectiveID'].'/>
                                        <label class="'.ltrim ($files[$i]->filetype, '.').'_btn floatleft"> </label>';
                                        if ($files[$i]->filetype == '.url') {
                                                echo '<p class="materialtxt"> <a href='.$files[$i]->path.'>'.$files[$i]->title.'</a><br></p>';
                                            } else {
                                                echo '<p class="materialtxt"> <a href="../curriculumdata/curriculum/'.$_GET['curriculumID'].'/'.$_GET['terminalObjectiveID'].'/'.$_GET['enablingObjectiveID'].'/'. $files[$i]->filename .'">'.$files[$i]->title.'</a><br></p>';
                                                //echo '<p class="materialtxt"> <a href='. $val .'>'.substr(strstr(basename($val), '_'), 1).'</a><br></p>'; //Funktioniert zwar aber nur wenn der Dateiname entsprechen codiert ist. Besser dateinamen aus der db holen
                                            }
                                        echo '<lable></label><p class="materialtxt">'.$files[$i]->description .' &nbsp;</p>'; // Leerzeichen  &nbsp; wichtig bei fehlender Beschreibung sonst wird es falsch dargestellt
                                        echo '<div class="materialseperator"></div><div class="materialseperator2"></div>';
                                    }
                                }
                                echo '</div></div>';
                            }
                            break;  

        case "editMaterial": if (isset($_GET['ajax'])) {
                                $file = new File(); 
                                $files = $file->getFiles('enabling_objective', $_GET['enablingObjectiveID']);
                                echo '<div class="border-top-radius contentheader">Material bearbeiten</div>
                                      <div id="popupcontent">';
                            if (!$files){
                                echo 'Es gibt leider kein Material zum gewählten Lernziel.';
                            } else {
                                for($i = 0; $i < count($files); $i++) { 
                                        echo '<form method="post" action="index.php?action=view&function=addObjectives">
                                                <input type="hidden" name="id" id="id" value='.$files[$i]->id.'/>
                                                <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$_GET['curriculumID'].'/>
                                                <input type="hidden" name="terminal_objective_id" id="terminal_objective_id" value='.$_GET['terminalObjectiveID'].'/> 
                                                <input type="hidden" name="enabling_objective_id" id="enabling_objective_id" value='.$_GET['enablingObjectiveID'].'/>
                                                <label class="'.$files[$i]->type.'_btn floatleft"> </label>
                                                <p class="materialtxt">Titel der Datei:<br>
                                                <input class="inputformlong" type="text" id="linkMaterial" name="linkMaterial" value="'.$files[$i]->title.'"/></p>';
                                        echo '<p class="materialtxt"><input type="submit" name="update_material" value="Material aktualisieren" />
                                            <input type="submit" name="deleteMaterial" value="Material löschen" /></p>';
                                        echo '</form>';
                                        //end edit
                                        echo '<div class="materialseperatoredit"></div><div class="materialseperator2"></div>';
                                }
                            }
                                
                                echo '</div></div>';
                            }
                            break;                     

        case "addterminalObjective": 
                            if (isset($_GET['ajax'])) {
                                echo '<div class="border-top-radius contentheader">Thema hinzufügen</div>
                                <div id="popupcontent"><form method="post" action="index.php?action=view&function=addObjectives">
                                <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$_GET['curriculumID'].'> 
                                <p><label>Thema: </label><input class="inputformlong" type="text" name="terminal_objective" /></p>
                                <p><label>Beschreibung: </label><input class="inputformlong" type="description" name="description" /></p>
                                <p><label></label><input type="submit" name="add_terminal_objective" value="Thema hinzufügen" /></p>
                                </form></div></div>';     
                            }
                            break;
        case "editterminalObjective": 
                            if (isset($_GET['ajax'])) {
                                $terminal_objective = new TerminalObjective(); 
                                $terminal_objective->id = $_GET['terminalObjectiveID'];
                                $terminal_objective->load();                                 //Läd die bestehenden Daten aus der db
                                echo '<div class="border-top-radius contentheader">Thema bearbeiten</div>
                                <div id="popupcontent">
                                <form method="post" action="index.php?action=view&function=addObjectives">
                                <input type="hidden" name="id" id="id" value='.$terminal_objective->id.'/> 
                                <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$terminal_objective->curriculum_id.'> 
                                <p><label>Thema: </label><input class="inputformlong" type="text" name="terminal_objective" value="'.$terminal_objective->terminal_objective.'"/></p>
                                <p><label>Beschreibung: </label><input class="inputformlong" type="description" name="description" value="'.$terminal_objective->description.'"/></p>
                                <p><label></label><input type="submit" name="update_terminal_objective" value="Thema aktualisieren" /></p>
                                </form></div></div>';
                            }
                            break;                    
        case "addenablingObjective": 
                            if (isset($_GET['ajax'])) {
                                echo '<div class="border-top-radius contentheader">Ziel hinzufügen</div>
                                <div id="popupcontent"><form method="post" action="index.php?action=view&function=addObjectives">
                                <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$_GET['curriculumID'].'/> 
                                <input type="hidden" name="terminal_objective_id" id="terminal_objective_id" value='.$_GET['terminalObjectiveID'].'/> 
                                <p><label>Ziel: </label><input class="inputformlong" type="text" name="enabling_objective" /></p>
                                <p><label>Beschreibung: </label><input class="inputformlong" type="description" name="description" /></p>
                                <p><label >Wiederholung? </label><input class="centervertical" type="checkbox" name="repeat" onchange="checkbox_addForm(this.checked,';
                                echo "'block',";// Wiederholungen
                                echo "'interval'";// Wiederholungen
                                echo ');"/></p>
                                    <p id="interval" style="display:none;"><label>Interval: </label>
                                      <select id="rep_interval"  class="centervertical" name="rep_interval">
                                        <option value="1" data-skip="1">täglich</option>
                                        <option value="2" data-skip="1">wöchentlich</option>
                                        <option value="3" data-skip="1">jeden Monat</option>
                                        <option value="4" data-skip="1" selected>jedes Halbjahr</option>
                                        <option value="5" data-skip="1">jedes Jahr</option>';
                                        /* später implementieren: <option value="5" data-skip="1">anderes Interval</option>*/
                                echo '   </select></p>
                                    <p id="info" style="display:none;"><label>Information</label>Diese Funktion ist noch nicht verfügbar.</p>
                                    <p><label></label><input type="submit" name="add_enabling_objective" value="Ziel hinzufügen" /></p>
                                </form></div></div>';
                            }
                            break;  
                             
            case "editenablingObjective": 
                            if (isset($_GET['ajax'])) {
                                $enabling_objective = new EnablingObjective();
                                $enabling_objective->id = $_GET['enablingObjectiveID'];
                                $enabling_objective->load();    //Läd die bestehenden Daten aus der db
                                echo '<div class="border-top-radius contentheader">Ziel bearbeiten</div>
                                <div id="popupcontent"><form method="post" action="index.php?action=view&function=addObjectives">
                                <input type="hidden" name="id" id="id" value='.$enabling_objective->id.'/> 
                                <input type="hidden" name="curriculum_id" id="curriculum_id" value='.$enabling_objective->curriculum_id.'/> 
                                <input type="hidden" name="terminal_objective_id" id="terminal_objective_id" value='.$enabling_objective->terminal_objective_id.'/> 
                                <p><label>Ziel: </label><input class="inputformlong" type="text" name="enabling_objective" value="'.$enabling_objective->enabling_objective.'"/></p>
                                <p><label>Beschreibung: </label><input class="inputformlong" type="description" name="description" value="'.$enabling_objective->description.'"/></p>';
                                // Wiederholungen
                                echo '<p><label >Wiederholung? </label><input class="centervertical" type="checkbox" name="repeat" ';
                                if ($enabling_objective->repeat_interval != -1){echo'checked';}
                                echo ' onchange="checkbox_addForm(this.checked,';
                                echo "'interval'";
                                echo ');"/></p><p id="interval" ';
                                if ($enabling_objective->repeat_interval == -1){echo ' style="display:none;"';}
                                echo ' ><label>Interval: </label>
                                <select id="rep_interval"  class="centervertical" name="rep_interval" > 
                                <option value="1" data-skip="1"'; if ($enabling_objective->repeat_interval == 1){echo'selected';}   echo '>täglich</option>
                                <option value="2" data-skip="1"'; if ($enabling_objective->repeat_interval == 7){echo'selected';}   echo '>wöchentlich</option>
                                <option value="3" data-skip="1"'; if ($enabling_objective->repeat_interval == 30){echo'selected';}  echo '>jeden Monat</option>
                                <option value="4" data-skip="1"'; if ($enabling_objective->repeat_interval == 182){echo'selected';} echo '>jedes Halbjahr</option>
                                <option value="5" data-skip="1"'; if ($enabling_objective->repeat_interval == 365){echo'selected';} echo '>jedes Jahr</option>
                                </select></p>    
                                <p><label></label><input type="submit" name="update_enabling_objective" value="Ziel aktualisieren" /></p>
                                </form></div></div>';
                            }
                            break;                     
        case "deleteCurriculum": if (isset($_GET['ajax'])) {
                                    $curriculum = new Curriculum(); 
                                    $curriculum->id = $_GET['curriculumID'];
                                    //Überprüfen, ob terminalObjective existieren, falls ja kann Lehrplan nicht gelöscht werden
                                    $terminal_objective = new TerminalObjective();
                                    if ($terminal_objective->getObjectives('curriculum', $_GET['curriculumID'])){
                                        $terObjExists = true; 
                                    } else {$terObjExists = false;} 
                                    //Überprüfen, ob einschreibungen existieren, falls ja kann Lehrplan nicht gelöscht werden 
                                    if ($curriculum->getCurriculumEnrolments()){
                                        $curEnrExists = true; 
                                    } else {$curEnrExists = false;} 
                                    
                                    if ($terObjExists == false && $curEnrExists == false){ //nur löschen, wenn keine Ziele existieren
                                        $curriculum->delete();
                                        echo '<div class="border-top-radius contentheader">Information</div>
                                              <div id="popupcontent">
                                              <p>Lehrplan wurde erfolgreich gelöscht.</p>
                                              <p><label></label><input type="submit" value="OK" onclick="reloadPage()"></p>
                                              </div>';
                                    } else {
                                        echo '<div class="border-top-radius contentheader">Warnung</div>
                                              <div id="popupcontent">
                                              <p>Lehrplan kann nicht gelöscht werden. Es müssen zuerst alle Themen bzw. Einschreibungen im Lehrplan gelöscht werden.</p>
                                              </div>';
                                        }
                            }
                            break; 
                            
        case "deleteObjective": if (isset($_GET['ajax'])) {
                                $enabling_objective = new EnablingObjective();
                                if ($_GET['enablingObjectiveID'] == 'notset') {//Löschen eines terminalObjectives   
                                    if (!$enabling_objective->getObjectives('terminal_objective', $_GET['terminalObjectiveID'])){ // check if there are enabling objectives under this terminal objective
                                        $terminal_objective = new TerminalObjective();
                                        $terminal_objective->id = $_GET['terminalObjectiveID'];
                                        $result = $terminal_objective->delete();    
                                    } else {
                                        echo '<div class="border-top-radius contentheader">Warnung</div>
                                              <div id="popupcontent">
                                              <p>Thema kann nicht gelöscht werden. Es müssen zuerst alle Ziele des Themas gelöscht werden.</p>
                                              </div>';
                                        }                                   
                                } else { // delete enabling objective
                                    $file = new File(); 
                                    if ($file->getFiles('enabling_objective', $_GET['enablingObjectiveID']) == false){ //checks if there are files for this enabling objective 
                                    $enabling_objective->id = $_GET['enablingObjectiveID'];
                                    $result = $enabling_objective->delete();
                                    } else {
                                        echo '<div class="border-top-radius contentheader">Warnung</div>
                                              <div id="popupcontent">
                                              <p>Ziel kann nicht gelöscht werden. Es müssen zuerst die verknüpften Materialien und Abgaben gelöscht werden</p>      
                                              </div>';
                                         }
                                }
                            }
                            break;                       

        case "setAccomplishedObjectives": if (isset($_GET['ajax'])) {
                                $enabling_objectives = new EnablingObjective();
                                $enabling_objective->setAccomplishedStatus('teacher', $_GET["userID"], $_GET["creatorID"], $_GET["statusID"]);
                            }
                            break;   
                            
        case "delete_semester": if (isset($_GET['ajax'])) {
                                    $semester = new Semester();
                                    $semester->id = $_GET['semester_id'];
                                    if ($semester->delete()){ //nur löschen, wenn keine Einschreibungen existieren
                                        renderDeleteMessage('Lernzeitraum wurde erfolgreich gelöscht.'); //Rendert das Popupfenster
                                    } else {
                                        renderDeleteMessage('Lernzeitraum kann nicht gelöscht werden solange Klassen mit dem Lernzeitraum verknüpft sind.'); //Rendert das Popupfenster
                                    }
                            }
                            break; 
       
        case "delete_subject": if (isset($_GET['ajax'])) {
                                    $subject = new Subject();
                                    $subject->id = $_GET['subject_id'];
                                    if ($subject->delete()){
                                        renderDeleteMessage('Fach wurde erfolgreich gelöscht.'); //Rendert das Popupfenster
                                    } else {
                                        renderDeleteMessage('Fach kann nicht gelöscht werden solange Lehrpläne mit dem Fach verknüpft sind.'); //Rendert das Popupfenster
                                    }
                            }
                            break;                    
         
        case "deleteGroup": if (isset($_GET['ajax'])) {
                                    //Überprüfen, ob Schüler in die Lerngruppe eingeschrieben sind.
                                    $group = new Group();
                                    $group->id = $_GET['group_id'];
                                    $group->delete();
                                    if ($group->delete()){ //nur löschen, wenn keine Schüler eingeschrieben
                                        renderDeleteMessage('Lerngruppe wurde erfolgreich gelöscht.'); //Rendert das Popupfenster
                                    } else {
                                        renderDeleteMessage('Lerngruppe kann nicht gelöscht werden. Es müssen zuerst alle Schüler aus der Lerngruppe ausgeschrieben werden.'); //Rendert das Popupfenster
                                    }
                            }
                            break;
        case "delete_grade": if (isset($_GET['ajax'])) {
                                    $grade = new Grade();
                                    $grade->id = $_GET['grade_id'];
                                    if ($grade->delete()) {   
                                        renderDeleteMessage('Klassenstufe wurde erfolgreich gelöscht.'); //Rendert das Popupfenster
                                    } else {
                                        renderDeleteMessage('Klassenstufe kann nicht gelöscht werden. Es müssen zuerst alle verknüpften Lehrpläne gelöscht werden.'); //Rendert das Popupfenster
                                    }
                            }
                            break;
        case "deleteFile": if (isset($_GET['ajax'])) {
                                    //Überprüfen, ob Datei verwendet wird sind.
                                    $file = new File();
                                    $file->id = $_GET['fileID'];
                                    if ($file->delete()){
                                        echo 'Datei wurde erfolgreich gelöscht.'; 
                                    } else { 
                                        echo 'Datei konnte nicht gelöscht werden.';
                                    }
                            }
                            break;
                            
        case "deleteUser": if (isset($_GET['ajax'])) {
                                    $user = new User(); 
                                    $user->id = $_GET['userID'];
                                    if ($user->delete()){
                                        renderDeleteMessage('Benutzer wurde erfolgreich gelöscht.'); //Rendert das Popupfenster
                                    } else { 
                                       renderDeleteMessage('Benutzer konnte nicht gelöscht werden.'); //Rendert das Popupfenster
                                    }           
                            }
                            break;                     
        case "expelUser": if (isset($_GET['ajax'])) {
                                    $current_user = new User();
                                    $current_user->id = $_GET['userID'];
                                    if ($current_user->expelFromGroup($_GET['groupsID'])){
                                        renderDeleteMessage('Benutzer wurde erfolgreich ausgeschrieben.'); //Rendert das Popupfenster
                                    } else { 
                                       renderDeleteMessage('Datensatz konnte nicht gefunden werden.'); //Rendert das Popupfenster
                                    }
                            }
                            break;
        case "loadMail": if (isset($_GET['ajax'])) {
                                $mail = new Mail();
                                $mail->id = $_GET['mailID'];
                                $mail->setStatus(true);
                                $mail->loadMail($mail->id);

                                // wenn Absender = -1 dann Systemmeldung
                                if ($mail->sender_id == -1){
                                   $sender_id = 'Curriculum-Nachrichtensystem'; 
                                } else {
                                    $sender = new User();
                                    $sender->load('id', $mail->sender_id);
                                }
                                $receiver = new User();
                                $receiver->load('id', $mail->receiver_id);
                                 
                                echo '<p class="mailheader"><label class="mailheader">Von:</label>';
                                echo $sender->firstname.' '.$sender->lastname.' ('.$sender->username.')</p>';
                                echo '<p class="mailheader"><label class="mailheader">An:</label>';
                                echo $receiver->firstname.' '.$receiver->lastname.' ('.$receiver->username.')</p>';
                                echo '<p class="mailheader"><label class="mailheader">Datum:</label>';
                                echo $mail->creation_time.'</p>';
                                echo '<p class="mailheader"><label class="mailheader">Betreff:</label>';
                                echo $mail->subject.'</p>';
                                echo '<h3>&nbsp;</h3><br>';
                                echo $mail->message;
                                
                            }
                            break;                 
        case "loadStates": if (isset($_GET['ajax'])) {
            
                                $state  = new State($_GET['country_id']);
                                $states = $state->getStates();
                                
                                if (isset($_GET['name'])){
                                    echo '<label>Bundesland: </label><select name="'.$_GET['name'].'">';
                                } else {
                                    echo '<label>Bundesland: </label><select name="state">';
                                }
                                for($i = 0; $i < count($states); $i++) {  
                                  echo  '<option label="'.$states[$i]->state.'" value="'.$states[$i]->id.'"';
                                  if (isset($_GET['name'])){
                                     if ($states[$i]->id == $_GET['state_id']) {
                                        echo ' selected="selected"'; 
                                     }
                                  }
                                  echo '>'.$states[$i]->state.'</option>';
                                }
                                echo '</select>';
                            }
                            break;             
        default:
            break;
    }   
}
?>
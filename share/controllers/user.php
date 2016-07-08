<?php
/**
*  This file is part of curriculum - http://www.joachimdieterich.de
* 
* @package core
* @filename user.php
* @copyright 2013 Joachim Dieterich
* @author Joachim Dieterich
* @date 2013.03.08 13:26
* @license: 
*
* The MIT License (MIT)
* Copyright (c) 2012 Joachim Dieterich http://www.curriculumonline.de
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
* to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
* and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
* DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
* THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
global $USER, $PAGE, $TEMPLATE;

$groups = new Group();
$institution = new Institution();

$TEMPLATE->assign('showFunctions', true);

if (isset($_GET['function'])) {
    $TEMPLATE->assign('showFunctions', false);  
    $current_user = new User();
    $current_user->id = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);
     switch ($_GET['function']) {
        case "showCurriculum": 
                $TEMPLATE->assign('showenroledCurriculum', true); 
                $p_config  = array('id'         => 'checkbox',
                                    'curriculum'   => 'Lehrplan', 
                                    'description' => 'Beschreibung', 
                                    'subject'     => 'Fach', 
                                    'grade'       => 'Klasse', 
                                    'schooltype'  => 'Schultyp', 
                                    'state'       => 'Bundesland/Region', 
                                    'de'          => 'Land', 
                                    'p_options'   => array());
                setPaginator('curriculumList', $TEMPLATE, $current_user->getCurricula('curriculumList'), 'cu_val', 'index.php?action=user&function=showCurriculum&userID='.$current_user->id, $p_config);
                break;
        case "showInstitution": 
                $TEMPLATE->assign('showenroledInstitution', true); 
                $TEMPLATE->assign('selectedUserID', $current_user->id);
                $institution = new Institution();
                $p_options = array('delete'         => array('onclick' => "expelFromInstituion(__id__, $current_user->id);", 
                                 'capability'       => checkCapabilities('user:expelFromInstitution', $USER->role_id, false)));
                $p_config =   array('id'         => 'checkbox',
                                    'role'   => 'Rolle', 
                                    'institution'   => 'Institution', 
                                    'description'   => 'Beschreibung', 
                                    'schooltype_id' => 'Schultyp', 
                                    /*'state_id'      => 'Bundesland/Region', */
                                    'creation_time' => 'Erstelltungsdatum', 
                                    'creator_id'    => 'Ersteller', 
                                    'p_options'     => $p_options);
                setPaginator('institutionList', $TEMPLATE, $institution->getInstitutions('user', 'institutionList', $current_user->id), 'ins_val', 'index.php?action=user&function=showinstitution&userID='.$current_user->id, $p_config);
                break;
       case "showGroups": 
                $TEMPLATE->assign('showenroledGroups',  true); 
                $TEMPLATE->assign('selectedUserID',     $current_user->id);
                $p_options = array('delete'         => array('onclick' => "expelUser(__id__, $current_user->id);", 
                                   'capability'     => checkCapabilities('user:expelFromGroup', $USER->role_id, false)));
                $p_config =   array('id'         => 'checkbox',
                                    'groups'        => 'Gruppen', 
                                    'grade'         => '(Klassen)stufe', 
                                    'description'   => 'Beschreibung', 
                                    'semester'      => 'Lernzeitraum', 
                                    'institution'   => 'Institution', 
                                    'creation_time' => 'Erstellungsdatum', 
                                    'creator'       => 'Ersteller', 
                                    'p_options'     => $p_options);
                setPaginator('groupsPaginator', $TEMPLATE, $current_user->getGroups('groupsPaginator'), 'gp_val', 'index.php?action=user&function=showGroups&userID='.$current_user->id, $p_config);
                break;
            default: break;
     }     
}
        
if (isset($_POST) ){
    $edit_user = new User(); 
    if (isset($_POST['id'])){
    foreach ($_POST['id'] as $edit_user->id ) { //Array per schleife abarbeiten
        if($edit_user->id  == "none") {
            if (count($_POST['id']) == 1){
                $PAGE->message[] = array('message' => 'Es muss mindestens ein Nutzer ausgewählt werden!', 'icon' => 'fa-user text-warning');
            }	
        } else { 	
            $edit_user->load('id',$edit_user->id);      // load current user 
            switch ($_POST) {
                case isset($_POST['resetPassword']):
                                    if (isset($_POST['confirmed'])) {
                                        $edit_user->confirmed = 3; // User have to change password after login
                                    } else {
                                        $edit_user->confirmed = 1; 
                                    }
                                    $edit_user->password = $_POST['password'];
                                    $validated_data = $edit_user->validate(true);
                                    if($validated_data === true) {/* validation successful */
                                        if ($edit_user->resetPassword()) {
                                                $PAGE->message[] = array('message' => 'Passwort des Nutzers '.$edit_user->firstname.' '.$edit_user->lastname.' ('.$edit_user->username.') wurde zurückgesetzt.', 'icon' => 'fa-key text-success');
                                            } else {
                                                $PAGE->message[] = array('message' => 'Password konnte nicht zurückgesetzt werden.', 'icon' => 'fa-key text-warning');
                                            }  
                                    } else {
                                        $new_subject($edit_user); 
                                        $TEMPLATE->assign('v_error', $validated_data);     
                                    }        
                    break;
                case isset($_POST['deleteUser']):
                                    if ($edit_user->id != $USER->id) {
                                        if ($edit_user->delete()){
                                            $PAGE->message[] = array('message' => 'Benutzerkonten wurden erfolgreich gelöscht!', 'icon' => 'fa-user text-success');
                                        }
                                    } else {
                                        $PAGE->message[] = array('message' => 'Man kann sich nicht selbst löschen!', 'icon' => 'fa-user text-warning');
                                    }
                    break; 
                case isset($_POST['enroleGroups']):
                                    if ($edit_user->enroleToGroup($_POST['groups'], $USER->id)){
                                        $groups->id = $_POST['groups']; 
                                        $groups->load();
                                        $PAGE->message[] = array('message' => 'Nutzer <strong>'.$edit_user->username.'</strong> erfolgreich in <strong>'.$groups->group.'</strong> eingeschrieben.', 'icon' => 'fa-user text-success');
                                    }
                    break; 
                case isset($_POST['expelGroups']):
                                    if ($edit_user->expelFromGroup($_POST['groups'])){
                                        $groups->id = $_POST['groups']; 
                                        $groups->load();
                                        $PAGE->message[] = array('message' => 'Nutzer <strong>'.$edit_user->username.'</strong> erfolgreich aus <strong>'.$groups->group.'</strong> ausgeschrieben.', 'icon' => 'fa-user text-success');
                                    }
                    break;     
                case isset($_POST['enroleInstitution']): 
                                    if (isset($_POST['institution'])){
                                        $institution->id = $_POST['institution'];
                                    } else {
                                        $institution->id = $USER->institution_id;
                                    }
                                    $edit_user->role_id = $_POST['roles'];
                                    if ($edit_user->enroleToInstitution($institution->id)){
                                        $institution->load();
                                        $PAGE->message[] = array('message' => 'Nutzer <strong>'.$edit_user->username.'</strong> erfolgreich in die Institution <strong>'.$institution->institution.'</strong> eingeschrieben.', 'icon' => 'fa-user text-success');
                                    }
                    break; 
                case isset($_POST['expelInstitution']):
                                    if ($edit_user->expelFromInstitution($_POST['institution'])){
                                        $institution->id = $_POST['institution']; 
                                        $institution->load();
                                        $PAGE->message[] = array('message' => 'Nutzer <strong>'.$edit_user->username.'</strong> erfolgreich aus der Institution<strong>'.$institution->institution.'</strong> ausgeschrieben.', 'icon' => 'fa-user text-success');
                                    }
                    break;     
                default:
                    break;
            }      
        session_reload_user(); // --> get the changes immediately 
        }
    }
}

}
/*******************************************************************************
 * END POST / GET
 */

$TEMPLATE->assign('page_title', 'Benutzerverwaltung');
$TEMPLATE->assign('breadcrumb',  array('Benutzerverwaltung' => 'index.php?action=user'));

$roles = new Roles(); 
$TEMPLATE->assign('roles', $roles->get());                                 //getRoles

// Load groups
$group_list = $groups->getGroups('group', $USER->id);
$TEMPLATE->assign('groups_array', $group_list);          // Lerngruppen  Laden
$TEMPLATE->assign('myInstitutions', $institution->getInstitutions('user', null, $USER->id));

$users = new USER();
$p_options = array('delete' => array('onclick'      => "del('user',__id__, $USER->id);", 
                                     'capability'   => checkCapabilities('user:delete', $USER->role_id, false),
                                     'icon'         => 'fa fa-minus',
                                     'tooltip'      => 'löschen'),
                    'edit'  => array('onclick'      => "formloader('profile','editUser',__id__);", 
                                     'capability'   => checkCapabilities('user:updateUser', $USER->role_id, false),
                                     'icon'         => 'fa fa-edit',
                                     'tooltip'      => 'bearbeiten'),
                    'group'  => array('href'        => 'index.php?action=user&function=showGroups&userID=__id__',
                                     'capability'   => checkCapabilities('user:getGroups', $USER->role_id, false),
                                     'icon'         => 'fa fa-group'),
                    /*'list'  => array('href'         => 'index.php?action=user&function=showCurriculum&userID=__id__',
                                     'capability'   => checkCapabilities('user:getCurricula', $USER->role_id, false),
                                     'icon'         => 'fa fa-list'),
                    'institution'  => array('href'  => 'index.php?action=user&function=showInstitution&userID=__id__',
                                     'capability'   => checkCapabilities('user:getInstitution', $USER->role_id, false),
                                     'icon'         => 'fa fa-university'),*/
                    'profile'  => array('onclick'   => "formloader('overview','full',__id__);", 
                                     'capability'   => checkCapabilities('user:getGroups', $USER->role_id, false),  //todo: use extra capability?
                                     'icon'         => 'fa fa-user'));
$p_config =   array('id'         => 'checkbox',
                    'username'   => 'Benutzername', 
                    'firstname'  => 'Vorname', 
                    'lastname'   => 'Nachname', 
                    'email'      => 'Email', 
                    'postalcode' => 'PLZ', 
                    'city'       => 'Ort', 
                    /*'state'   => 'Bundesland', 
                    'country' => 'Land', */
                    ''    => 'Rolle', 
                    'p_options'    => $p_options);
setPaginator('userP', $TEMPLATE, $users->userList('institution', 'userP'), 'us_val', 'index.php?action=user', $p_config); //set Paginator    

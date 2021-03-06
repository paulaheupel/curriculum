<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
 * 
 * @package core
 * @filename install.php
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

global $TEMPLATE, $CFG, $PAGE, $USER;
global $USER;
$USER = new User();
$USER->role_id = 1;
$USER->id = 0; 
if (!isset($_POST['step_5'])){ //Solves warning in Step 5
    $_SESSION['USER'] = new stdClass();
    $_SESSION['USER']->id = $USER->id; 
}

$TEMPLATE->assign('db_host', '127.0.0.1');
$TEMPLATE->assign('page_title', 'Curriculum installieren');
$TEMPLATE->assign('my_username', '');
$TEMPLATE->assign('my_role_id', $USER->role_id);
$TEMPLATE->assign('step', 0);
$TEMPLATE->assign('countries', '');
$TEMPLATE->assign('error', '');
$PAGE->message = '';
$cfg_file = dirname(__FILE__).'/../config.php';

if (isset($_GET)){ 
    switch ($_GET) {
        case isset($_GET['step']): load_Countries();
            $TEMPLATE->assign('step', $_GET['step']);
            break;

        default:
            break;
    }
}

if ($_POST){
    switch ($_POST) {
        case isset($_POST['step_0']):
            if (isset($_POST['license']) AND $_POST['license'] != '') {
                $TEMPLATE->assign('db_host', '127.0.0.1');
                $TEMPLATE->assign('db_name', '');
                $TEMPLATE->assign('db_user', '');
                $TEMPLATE->assign('db_password', '');
                $TEMPLATE->assign('server_name', filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW));
                $TEMPLATE->assign('app_url', implode('/', array_slice(explode('/',filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_UNSAFE_RAW)), 1, 1)).'/');
                $TEMPLATE->assign('data_root', null);
                $TEMPLATE->assign('step', 1);
            }
            break;
        case isset($_POST['step_1']):
                    if (isset($_SESSION['DOWNLOAD'])){ //go to page 2 - 
                        unset($_SESSION['DOWNLOAD']); 
                        $TEMPLATE->assign('step', 2);
                    } else {
                        $CFG->db_host       = $_POST['db_host'];
                        $CFG->db_name       = $_POST['db_name'];
                        $CFG->db_user       = $_POST['db_user'];
                        $CFG->db_password   = $_POST['db_password'];
                        try{
                            $db = new pdo('mysql:host='.$CFG->db_host.';dbname='.$CFG->db_name.';'/*'.charset=utf8.'*/, $CFG->db_user, $CFG->db_password ,
                                            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                            $gump = new Gump();
                            $gump->validation_rules(array(
                                            'db_host'     => 'required',
                                            'db_user'     => 'required',
                                            'db_password' => 'required',
                                            'db_name'     => 'required', 
                                            'data_root'    => 'required'
                                            ));
                            $validated_data = $gump->run($_POST);
                            if($validated_data === false) {/* validation failed */
                                    foreach($_POST as $key => $value){
                                    $TEMPLATE->assign($key, $value);
                                    } 
                                    $TEMPLATE->assign('error', $gump->get_readable_errors());     
                                    $TEMPLATE->assign('step', 1); 
                                } else {/* validation successful */
                                    writeConfigFile($cfg_file, '$CFG->db_host', $_POST["db_host"]);
                                    writeConfigFile($cfg_file, '$CFG->db_user', $_POST["db_user"]);
                                    writeConfigFile($cfg_file, '$CFG->db_password ', $_POST["db_password"]);
                                    writeConfigFile($cfg_file, '$CFG->db_name', $_POST["db_name"]);
                                    writeConfigFile($cfg_file, '$CFG->ip', $_POST["server_name"]);
                                    if (substr($_POST["app_url"], -1) == "/"){
                                        $postfix = '';
                                    } else {
                                        $postfix = '/';
                                    }
                                    writeConfigFile($cfg_file, '$CFG->base_folder', $_POST["app_url"].$postfix);
                                    if (substr($_POST["data_root"], -1) == "/"){
                                        $postfix = '';
                                    } else {
                                        $postfix = '/';
                                    }
                                    writeConfigFile($cfg_file, '$CFG->curriculumdata_root', $_POST["data_root"].$postfix.'curriculumdata/');
                                    $TEMPLATE->assign('success','Datenbankzugriff funktioniert!');
                                    $TEMPLATE->assign('step', 2);
                                }  
                        }
                        catch(PDOException $ex){
                            $TEMPLATE->assign('step', 1);
                            $TEMPLATE->assign('db_host', $_POST['db_host']);
                            $TEMPLATE->assign('db_name', $_POST['db_name']);
                            $TEMPLATE->assign('db_user', $_POST['db_user']);
                            $TEMPLATE->assign('db_password', $_POST['db_password']);
                            $TEMPLATE->assign('alert','Datenbankzugriff fehlgeschlagen. Bitte überprüfen Sie die eingegebenen Daten');
                            //die(json_encode(array('outcome' => false, 'message' => 'Unable to connect')));
                        }
                        
                        $zip    = new ZipArchive();
                        $exists = $zip->open($CFG->share_root.'/../curriculumdata.zip');
                        if ($exists === TRUE) {
                            $zip->extractTo($_POST['data_root']);
                            $zip->close();
                            unlink($CFG->share_root.'/../curriculumdata.zip'); //deactivated 
                        } else {
                            $TEMPLATE->assign('alert','Datenverzeichnis konnte nicht entpackt werden');
                        }
                        $fp = import_SQL($CFG->share_root.'/../install.sql'); // install demo data
                        
                        if (!$fp) {
                            unlink($CFG->share_root.'/../install.sql'); 
                            $TEMPLATE->assign('success','Datenbank erfolgreich eingerichtet');
                        } else {
                            $TEMPLATE->assign('alert','Bei der Einrichtung der Datenbank ist ein Fehler aufgetreten. Bitte überprüfen Sie die Ausgabe in der php_error.log');
                            $TEMPLATE->assign('step', 2);
                        }
                        $TEMPLATE->assign('app_url', 'curriculum'); //todo get url from path
                    }
            break;
        case isset($_POST['step_2']):
                        $gump = new Gump();
                        $gump->validation_rules(array(
                            'app_title'     => 'required'
                        ));
                        $validated_data = $gump->run($_POST);
                        if($validated_data === false) {/* validation failed */
                            foreach($_POST as $key => $value){
                            $TEMPLATE->assign($key, $value);
                            } 
                            $TEMPLATE->assign('error', $gump->get_readable_errors());     
                            $TEMPLATE->assign('step', 2); 
                        } else {/* validation successful */
                            writeConfigFile($cfg_file, '$CFG->app_title', $_POST["app_title"]);
                            $CFG->app_title = $_POST["app_title"]; 
                            $TEMPLATE->assign('institution', '');
                            $TEMPLATE->assign('description', '');
                            $TEMPLATE->assign('schooltype_id', false);
                            $TEMPLATE->assign('btn_newSchooltype', false);
                            $TEMPLATE->assign('new_schooltype', false);
                            $TEMPLATE->assign('schooltype_description', null);
                            $sch     = new Schooltype();
                            $TEMPLATE->assign('schooltypes', $sch->getSchooltypes());
                            $countries = new State($CFG->settings->standard_country);
                            $TEMPLATE->assign('state_id', $CFG->settings->standard_state);
                            $TEMPLATE->assign('states', $countries->getStates());
                            $TEMPLATE->assign('country_id', $CFG->settings->standard_country);
                            $TEMPLATE->assign('countries', $countries->getCountries());
                            $TEMPLATE->assign('step', 3);                            
                        }
            break;
        case isset($_POST['step_3']):
                        $gump = new Gump();
                        $gump->validation_rules(array(
                                        'institution'                 => 'required',
                                        'description'     => 'required'
                                        ));
                        $validated_data = $gump->run($_POST);
                                        if (!isset($_POST['state_id'])){
                                            $_POST['state_id'] = 1;
                                        }
                                        if($validated_data === false) {/* validation failed */
                                                foreach($_POST as $key => $value){
                                                $TEMPLATE->assign($key, $value);
                                                } 
                                                $TEMPLATE->assign('v_error', $gump->get_readable_errors());   
                                                load_Countries();
                                                $TEMPLATE->assign('step', 3); 
                                            } else {
                                                /*if (isset($_POST['btn_newSchooltype'])){ 
                                                    $new_schooltype = new Schooltype();
                                                    $new_schooltype->schooltype  = $_POST['new_schooltype'];
                                                    $new_schooltype->description = $_POST['schooltype_description'];
                                                    $new_schooltype->country_id  = $_POST['country_id'];
                                                    $new_schooltype->state_id    = $_POST['state_id'];
                                                    $new_schooltype->creator_id = 0; 
                                                    $_POST['schooltype_id'] = $new_schooltype->add(); 
                                                }*/
                                                $new_institution = new Institution(); 
                                                $new_institution->institution   = $_POST['institution'];
                                                $new_institution->description   = $_POST['description'];
                                                $new_institution->schooltype_id = $_POST['schooltype_id'];
                                                $new_institution->country_id    = $_POST['country_id'];
                                                $new_institution->state_id      = $_POST['state_id'];
                                                $new_institution->creator_id    = 0; // system user
                                                $new_institution->confirmed     = 1;  // institution is confirmed
                                                
                                                $institution_id = $new_institution->update(TRUE);
                                                $TEMPLATE->assign('institution_id', $institution_id);  
                                                $TEMPLATE->assign('username', null);  
                                                $TEMPLATE->assign('firstname', null);  
                                                $TEMPLATE->assign('lastname', null);  
                                                $TEMPLATE->assign('email', null);  
                                                $TEMPLATE->assign('city', null);  
                                                $TEMPLATE->assign('postalcode', null);  
                                                $TEMPLATE->assign('password', null);  
                                                $TEMPLATE->assign('show_pw', false);  
                                                
                                                $countries = new State($CFG->settings->standard_country);
                                                $TEMPLATE->assign('state_id', $CFG->settings->standard_state);
                                                $TEMPLATE->assign('states', $countries->getStates());
                                                $TEMPLATE->assign('country_id', $CFG->settings->standard_country);
                                                $TEMPLATE->assign('countries', $countries->getCountries());
                                                $TEMPLATE->assign('step', 4);
                                            }  
                //Admin in alle Lehrpläne und in institution einschreiben
            break;     
        case isset($_POST['step_4']):
                    $gump = new Gump();
                         $institution = new Institution(); 
                        if (!isset($_POST['state_id'])){
                            $_POST['state_id'] = 1; // eq not set
                        }
                        $gump->validation_rules(array(
                            'username'      => 'required',
                            'firstname'     => 'required',
                            'lastname'      => 'required',
                            'email'         => 'required',
                            'country_id'    => 'required',
                            'state_id'      => 'required',
                            'pw'            => 'required', 
                            'institution_id'=> 'required'
                        ));
                        $validated_data = $gump->run($_POST);
                        if($validated_data === false) {/* validation failed */
                                foreach($_POST as $key => $value){
                                $TEMPLATE->assign($key, $value);
                                } 
                                $TEMPLATE->assign('v_error', $gump->get_readable_errors());     
                                load_Countries();
                                $TEMPLATE->assign('step', 4); 
                            } else {
                                $new_user = new User(); 
                                $new_user->username   = $_POST['username'];
                                $new_user->firstname  = $_POST['firstname'];
                                $new_user->lastname   = $_POST['lastname'];
                                $new_user->email      = $_POST['email'];
                                $new_user->postalcode = $_POST['postalcode'];
                                $new_user->city       = $_POST['city'];
                                $new_user->state_id   = $_POST['state_id'];
                                $new_user->country_id = $_POST['country_id'];
                                $new_user->password   = $_POST['pw'];
                                $new_user->role_id    = 1; //Superadmin
                                $new_user->confirmed  = 1;
                                $new_user->creator_id = 0;
                                //$USER = $new_user;          //important! $USER is required in user.class.php
                                $user_id = $new_user->add($_POST['institution_id']);
                                $new_user->creator_id       = $user_id;
                                $new_user->id               = $user_id;
                                $new_user->dedicate();       
                                $new_user->enroleToGroup(array(1)); // enrol to demo group
                                $db = DB::prepare('UPDATE users SET password = ?'); // set password of all demo users to new admins password
                                $db->execute(array(md5($new_user->password)));

                                $terminal_objective = new TerminalObjective();
                                $terminal_objective->creator_id = $user_id; 
                                $terminal_objective->dedicate();
                                
                                $institution->id            = $_POST['institution_id']; 
                                $institution->creator_id    = $user_id;
                                $institution->dedicate();
                                
                                $subjects = new Subject(); 
                                $subjects->institution_id   = $_POST['institution_id'];
                                $subjects->creator_id       = $user_id;
                                $subjects->dedicate();

                                $grade = new Grade();       //Set institution_id in grade_db
                                $grade->institution_id      = $_POST['institution_id'];
                                $grade->creator_id          = $user_id;
                                $grade->dedicate();

                                $semester = new Semester(); //Set institution_id in semester db
                                $semester->institution_id      = $_POST['institution_id'];
                                $semester->creator_id          = $user_id;
                                $semester->dedicate();

                                $fi = new File();        //Set creator_id in files_db
                                $fi->creator_id     = $user_id;
                                $fi->dedicate();
                                
                                $ct                 = new Certificate();        //Set creator_id in files_db
                                $ct->creator_id     = $user_id;
                                $ct->dedicate();

                                $schooltypes = new Schooltype();//Set creator_id in schooltypes
                                $schooltypes->creator_id          = $user_id;
                                $schooltypes->dedicate();

                                $enabling_objective = new EnablingObjective();
                                $enabling_objective->creator_id = $user_id;
                                $enabling_objective->dedicate();

                                $group = new Group();
                                $group->creator_id = $user_id;
                                $group->dedicate();

                                $curriculum = new Curriculum();
                                $curriculum->creator_id = $user_id;
                                $curriculum->dedicate();
   
                                $roles = new Roles();       //Set creator_id in user_roles db
                                $roles->creator_id          = $user_id;
                                $roles->dedicate();         //it also deletes the -1 role
                                
                                $content = new Content();       
                                $content->creator_id          = $user_id;
                                $content->dedicate();         

                                $TEMPLATE->assign('step', 5);

                            }
            break;
        case isset($_POST['step_5']):
                        session_destroy(); //important! reset $USER
                        header('Location:index.php?action=login&install=true');
            break;
        
        default:
            break;
    }
}
                
/**
 * write config file
 * @param file $file
 * @param string $pattern
 * @param string $replace 
 */                
function writeConfigFile($file, $pattern, $replace){
    $lines = file($file);
    for ($i= 0; $i < count($lines); $i++){    
        if(preg_match(sprintf('#\%s*.*=#',$pattern), $lines[$i])){
            $lines[$i] = $pattern."='".$replace."';\n"; 
            break;
        }
    }
    file_put_contents($file,$lines);
}

/**
 * load countries
 * @global object $TEMPLATE 
 */
function load_Countries(){
    global $TEMPLATE; 
    $country = new State(); 
    $countries = $country->getCountries();
    $TEMPLATE->assign('countries', $countries);
    $schooltype = new Schooltype();
    $schooltypes = $schooltype->getSchooltypes();
    $TEMPLATE->assign('schooltype', $schooltypes);
}

function import_SQL($filename){ 
    $sql = file_get_contents($filename);       // Read sql file
    try {
        return DB::exec($sql);
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
        die();
    }
}

$TEMPLATE->assign('page_message', $PAGE->message);
?>
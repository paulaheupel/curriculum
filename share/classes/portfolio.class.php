<?php
/**
* enabling objective class can add, update, delete and get data from curriculum db
* 
* @abstract This file is part of curriculum - http://www.joachimdieterich.de
* @package core
* @filename portfolio.class.php
* @copyright 2013 Joachim Dieterich
* @author Joachim Dieterich
* @date 2013.06.11 21:00
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
class Portfolio {
    /**
     * ID of enabling objective
     * @var int
     */
    public $id;
    /**
     * enabling Objective
     * @var string 
     */
    public $title;
    /**
     * Description of enabling objective
     * @var string
     */
    public $description; 
    /**
     * filename
     * @var string 
     */
    public $filename;
    public $thumb_filename;
    /**
     * type
     * @var string 
     */
    public $type; 
    /**
     * filepath
     * @var string 
     */
    public $path; 
    /**
     * id of curriculum
     * @var int 
     */
    public $curriculum_id;
    /**
     * curriculum name - used for accomplished objectives on dashboard
     * @var string 
     */
    public $curriculum; 
    /**
     * id of terminal objective
     * @var int
     */
    public $terminal_objective_id; 
    /**
     * name of terminal objective
     * @var string 
     */
    public $terminal_objective; 
    /**
     * Timestamp when Grade was created
     * @var timestamp
     */
    public $creation_time; 
    
    /**
     * ID of User who created this Grade
     * @var int
     */
    public $creator_id; 
    /**
     * license
     * @since 0.9
     * @var string
     */
    public $license;
    /**
     * name of creator
     * @var string
     */
    public $creator; 
    /**
     * repeat interval
     * @var int 
     */
    public $repeat_interval;
    /**
     * Position of enabling_objective  within terminal_objective
     * @var type 
     */
    public $order_id; 
    /**
     * id of current accomplish status
     * @var int
     */
    public $accomplished_status_id; 
    /**
     * timestamp of last accomplish status change
     * @var timestamp
     */
    public $accomplished_time; 
    /**
     * id of teacher who set last accomplished status 
     * @var type 
     */
    public $accomplished_teacher_id; 
    /**
     * name of teacher who set accomplished status
     * @var string
     */
    public $accomplished_teacher; 
    /**
     * number of enroled users
     * @var int
     */
    public $enroled_users;
    /**
     * number of users who accomplished objective
     * @var int
     */
    public $accomplished_users; 
    /**
     * percent value - number of  users who accomplished objective
     * @var int 
     */
    public $accomplished_percent; 
    /**
     * array of files of current enabling objective
     * @var array of file object
     */
    public $files; 
    
    public $artefact_type; 
    
   
    public function getArtefacts (){
        global $USER;
        $artefacts   = array();
        $artefacts[] = $this->getLastEnablingObjectives($USER);
        $artefacts[] = $this->getFiles($USER->id);
        $artefacts[] = $this->getMail($USER->id);
        
        $result = array();
        
        foreach($artefacts as $arr) {
            if(is_array($arr)) {
                $result = array_merge($result, $arr);
            }
        }
        return PHPArrayObjectSorter($result, 'accomplished_time', 'desc');
    }
    
    /**
     * get last enabling objectives depending on users accomplished days
     * @global int $USER
     * @return mixed 
     */
    public function getLastEnablingObjectives($user){
        
        $db = DB::prepare('SELECT ena.*, cur.curriculum, usa.status_id as status_id, 
                            usa.accomplished_time as accomplished_time, usa.creator_id as teacher_id, us.firstname, us.lastname
                        FROM enablingObjectives AS ena, user_accomplished AS usa, curriculum AS cur, users AS us
                        WHERE ena.id = usa.reference_id
                        AND usa.context_id
                        AND us.id = usa.user_id
                        AND ena.curriculum_id = cur.id AND usa.user_id = ? AND (usa.status_id = 1 OR usa.status_id = 11 OR usa.status_id = 21 OR usa.status_id = 31) 
                        ');
        $db->execute(array($user->id));
        while($result = $db->fetchObject()) { 
            $this->artefact_type           = 'enablingObjective'; 
            $this->id                      = $result->id;
            $this->title                   = $result->enabling_objective;
            $this->description             = $result->description;
            $this->curriculum              = $result->curriculum;
            $this->creation_time           = $result->creation_time;
            $this->creator_id              = $result->creator_id;   
            $this->accomplished_status_id  = $result->status_id;   
            $this->accomplished_time       = $result->accomplished_time;   
            $this->accomplished_teacher_id = $result->teacher_id;   
            $db_1 = DB::prepare('SELECT firstname, lastname FROM users WHERE id = ?');
            $db_1->execute(array($this->accomplished_teacher_id));
            $teacher = $db_1->fetchObject();
            $this->accomplished_teacher = $teacher->firstname.' '.$teacher->lastname;   
            $artefacts[]                =  clone $this; 
        }
        
        if (isset($artefacts)) {    
            return $artefacts;
        } else {return NULL;}  
    }
    
    public function getMail($id){
        
        $mails =  new Mailbox();
        $mails->loadInbox($id);
        if (isset($mails->inbox)){
            foreach ($mails->inbox as $key => $value) {
                $this->artefact_type           = 'mail_inbox'; 
                $this->id                      = $value->id;
                $this->title                   = $value->subject;
                $this->description             = $value->message;
                $this->curriculum              = '';
                $this->creation_time           = $value->creation_time;
                $this->creator_id              = $value->sender_id;   
                $this->accomplished_status_id  = '';   
                $this->accomplished_time       = '';   
                $this->accomplished_teacher_id = $value->sender_id;   
                $db_1 = DB::prepare('SELECT firstname, lastname FROM users WHERE id = ?');
                $db_1->execute(array($this->accomplished_teacher_id));
                $teacher = $db_1->fetchObject();
                $this->accomplished_teacher   = $teacher->firstname.' '.$teacher->lastname;   
                $artefacts[]                  =  clone $this; 
            }
        }

        $mails->loadOutbox($id);
        if (isset($mails->outbox)){
            foreach ($mails->outbox as $key => $value) {
                $this->artefact_type           = 'mail_outbox'; 
                $this->id                      = $value->id;
                $this->title                   = $value->subject;
                $this->description             = $value->message;
                $this->curriculum              = '';
                $this->creation_time           = $value->creation_time;
                $this->creator_id              = $value->sender_id;   
                $this->accomplished_status_id  = '';   
                $this->accomplished_time       = '';   
                $this->accomplished_teacher_id = $value->receiver_id;   
                $db_1 = DB::prepare('SELECT firstname, lastname FROM users WHERE id = ?');
                $db_1->execute(array($this->accomplished_teacher_id));
                $teacher = $db_1->fetchObject();
                $this->accomplished_teacher   = $teacher->firstname.' '.$teacher->lastname;   
                $artefacts[]                  =  clone $this; 
            }
        }
        if (isset($artefacts)) {    
            return $artefacts;
        } else {return NULL;}  
    }
    
    
    
    public function getFiles($user_id){
        GLOBAL $CFG;
       $db = DB::prepare('SELECT fl.*, ct.context, ct.path AS context_path, us.firstname, us.lastname FROM files AS fl, users AS us, context AS ct
                                    WHERE fl.creator_id IN ('.$user_id.')
                                    AND fl.creator_id = us.id
                                    AND fl.context_id = ct.context_id');
        $db->execute();  
        while($result = $db->fetchObject()) { 
            $this->artefact_type         = $result->context; // 
            $this->id                    = $result->id;
            $this->title                 = $result->title;
            $this->filename              = rawurlencode($result->filename);
              
            $extension_pos = strrpos($this->filename, '.'); // find position of the last dot, so where the extension starts
            $this->thumb_filename        = substr($this->filename, 0, $extension_pos) . '_t.png';
            $this->description           = $result->description;
            $this->author                = $result->author;
            switch ($result->license) {
                    case 1: $this->license = 'Sonstige'; break;
                    case 2: $this->license = 'Alle Rechte vorbehalten'; break;
                    case 3: $this->license = 'Public Domain'; break;
                    case 4: $this->license = 'CC'; break;
                    case 5: $this->license = 'CC - keine Bearbeitung'; break;
                    case 6: $this->license = 'CC - keine kommerzielle Nutzung - keine Bearbeitung'; break;
                    case 7: $this->license = 'CC - keine kommerzielle Nutzung'; break;
                    case 8: $this->license = 'CC - keine kommerzielle Nutzung - Weitergabe unter gleichen Bedingungen'; break;
                    case 9: $this->license = 'CC - Weitergabe unter gleichen Bedingungen'; break;
                    default:
                        break;
                    
            }
            $this->path                  = $CFG->access_file.$result->context_path.$result->path;
            $this->type                  = $result->type;
            $this->curriculum_id         = $result->cur_id;
            $this->terminal_objective_id = $result->ter_id;
            $this->enabling_objective_id = $result->ena_id;
            $this->creation_time         = $result->creation_time;
            $this->accomplished_time     = $result->creation_time; //damit richtig sortiert werden kann 
            $this->creator_id            = $result->creator_id;
            $this->creator               = $result->firstname.' '.$result->lastname;
            
            $artefacts[] = clone $this;        //it has to be clone, to get the object and not the reference
        } 
        if (isset($artefacts)) {    
            return $artefacts;
        } else {return NULL;}  
    }
   
}
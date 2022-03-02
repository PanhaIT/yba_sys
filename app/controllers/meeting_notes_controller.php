<?php
class MeetingNotesController extends AppController {
    var $name = 'MeetingNotes';
    var $components = array('Helper', 'Inventory');
    var $uses = array("MeetingNote");

    function viewByUser(){
        $this->layout = 'MeetingNote';
        $user  = $this->getCurrentUser();
    }

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Dashborad');
    }

    function ajax() {
        $this->layout = 'ajax';
    }

    function view($id = null) {
        $this->layout = 'ajax';
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'View', $id);
        $this->data = $this->MeetingNote->read(null, $id);
    }

    function add($cloneId=null) {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->loadModel('MeetingNote');
        if (!empty($this->data)) {
            $result  = array();
            if ($this->Helper->checkDouplicate('code', 'meeting_notes',$this->data['MeetingNote']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Add New (Code has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->MeetingNote->create();
                $this->data['MeetingNote']['sys_code']   = md5(rand().strtotime(date("Y-m-d H:i:s")).$user['User']['id']);
                $this->data['MeetingNote']['created']    = $dateNow;
                $this->data['MeetingNote']['created_by'] = $user['User']['id'];
                $this->data['MeetingNote']['is_active']  = 1;
                $this->data['MeetingNote']['code']    = '';
                if ($this->MeetingNote->save($this->data)) {
                    $MeetingNoteId = $this->MeetingNote->id;
                    $modCode = $this->Helper->getAutoGenerateMeetingNote();
                    mysql_query("UPDATE meeting_notes SET code = '".$modCode."' WHERE id = ".$MeetingNoteId);
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Add New', $MeetingNoteId);
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Add New (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        // User Activity
        if ($cloneId!='') {
            // User Activity
            $this->data = $this->MeetingNote->read(null, $cloneId);
            $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Clone Add New');
        }else{
            $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Add New');
        }
        $egroups  = ClassRegistry::init('Egroup')->find('list', array('fields' => array('Egroup.id', 'Egroup.name'),'conditions' => array('Egroup.is_active = 1'), 'order' => 'Egroup.name'));
        $this->set(compact('egroups','cloneId'));
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $this->loadModel('MeetingNote');
        if (!empty($this->data)) {
            $result = array();
            if ($this->Helper->checkDouplicateEdit('code', 'meeting_notes', $this->data['MeetingNote']['id'], $this->data['MeetingNote']['code'],'is_active = 1')) {
                // User Activity
                $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Edit (Code has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['MeetingNote']['modified'] = $dateNow;
                $this->data['MeetingNote']['modified_by'] = $user['User']['id'];
                if ($this->MeetingNote->save($this->data)) {
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Edit', $id);
                    $result['error'] = 0;
                    echo json_encode($result);
                    exit;
                }else{
                    $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Save Edit (Error)');
                    $result['error'] = 1;
                    echo json_encode($result);
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            // User Activity
            $this->data = $this->MeetingNote->read(null, $id);
            $egroups  = ClassRegistry::init('Egroup')->find('list', array('fields' => array('Egroup.id', 'Egroup.name'),'conditions' => array('Egroup.is_active = 1'), 'order' => 'Egroup.name'));
            $this->set(compact('egroups'));
        }
    }

    function delete($id = null) {
        $dateNow  = date("Y-m-d H:i:s");
        $user = $this->getCurrentUser();
        $this->data = $this->MeetingNote->read(null, $id);
        // User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'MeetingNote', 'Delete', $id);
        mysql_query("UPDATE `meeting_notes` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
}
?>
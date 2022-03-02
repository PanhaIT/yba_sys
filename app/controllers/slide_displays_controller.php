<?php

class SlideDisplaysController extends AppController {

    var $name = 'SlideDisplays';
    var $components = array('Helper');

    function index() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Dashboard');
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
        $this->data = $this->SlideDisplay->read(null, $id);
        $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'View', $id);
    }

    function add() {
        $this->layout = 'ajax';
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicate('name', 'slide_displays', $this->data['SlideDisplay']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Add New (Name has existed)');
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->SlideDisplay->create();
                $this->data['SlideDisplay']['created']    = $dateNow;
                $this->data['SlideDisplay']['created_by'] = $user['User']['id'];
                $this->data['SlideDisplay']['is_active'] = 1;
                if ($this->SlideDisplay->save($this->data)) {
                    $lastInsertId = $this->SlideDisplay->getLastInsertId();
                    // photo
                    if ($this->data['SlideDisplay']['photo'] != '') {
                        $photoName = md5($lastInsertId . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/slide_show/tmp/' . $this->data['SlideDisplay']['photo']);
                        rename('public/slide_show/tmp/thumbnail/' . $this->data['SlideDisplay']['photo'], 'public/slide_show/' . $photoName);
                        mysql_query("UPDATE slide_displays SET photo='" . $photoName . "' WHERE id=" . $lastInsertId);
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Add New', $this->SlideDisplay->id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Add New (Error)');
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Add New');
    }

    function edit($id = null) {
        $this->layout = 'ajax';
        if (!$id && empty($this->data)) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        if (!empty($this->data)) {
            if ($this->Helper->checkDouplicateEdit('name', 'slide_displays', $id, $this->data['SlideDisplay']['name'])) {
                $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Edit (Name has existed)', $id);
                echo MESSAGE_DATA_ALREADY_EXISTS_IN_THE_SYSTEM;
                exit;
            } else {
                $dateNow  = date("Y-m-d H:i:s");
                $this->data['SlideDisplay']['modified']    = $dateNow;
                $this->data['SlideDisplay']['modified_by'] = $user['User']['id'];
                if ($this->SlideDisplay->save($this->data)) {
                    // photo
                    if ($this->data['SlideDisplay']['new_photo'] != '') {
                        $photoName = md5($this->data['SlideDisplay']['id'] . '_' . date("Y-m-d H:i:s")).".jpg";;
                        @unlink('public/slide_show/tmp/' . $this->data['SlideDisplay']['new_photo']);
                        rename('public/slide_show/tmp/thumbnail/' . $this->data['SlideDisplay']['new_photo'], 'public/slide_show/' . $photoName);
                        @unlink('public/slide_show/' . $this->data['SlideDisplay']['old_photo']);
                        mysql_query("UPDATE slide_displays SET photo='" . $photoName . "' WHERE id=" . $this->data['SlideDisplay']['id']);
                    }
                    // Save User Activity
                    $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Edit', $id);
                    echo MESSAGE_DATA_HAS_BEEN_SAVED;
                    exit;
                } else {
                    $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Save Edit (Error)', $id);
                    echo MESSAGE_DATA_COULD_NOT_BE_SAVED;
                    exit;
                }
            }
        }
        if (empty($this->data)) {
            $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Edit', $id);
            $this->data = $this->SlideDisplay->read(null, $id);
        }
    }

    function delete($id = null) {
        if (!$id) {
            echo MESSAGE_DATA_INVALID;
            exit;
        }
        $user = $this->getCurrentUser();
        $dateNow  = date("Y-m-d H:i:s");
        $this->data = $this->SlideDisplay->read(null, $id);
        mysql_query("UPDATE `slide_displays` SET `is_active`=2, `modified`='".$dateNow."', `modified_by`=".$user['User']['id']." WHERE `id`=".$id.";");
        // Save User Activity
        $this->Helper->saveUserActivity($user['User']['id'], 'Slide Display', 'Delete', $id);
        echo MESSAGE_DATA_HAS_BEEN_DELETED;
        exit;
    }
    
    function upload() {
        $this->layout = 'ajax';
        if ($_FILES['photo']['name'] != '') {
            $target_folder = 'public/slide_show/tmp/';
            $ext = explode(".", $_FILES['photo']['name']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_folder . $target_name);
            if (isset($_SESSION['pos_photo']) && $_SESSION['pos_photo'] != '') {
                @unlink($target_folder . $_SESSION['pos_photo']);
            }
            echo $_SESSION['pos_photo'] = $target_name;
            exit();
        }
    }

    function cropPhoto() {
        $this->layout = 'ajax';

        // Function
        include('includes/function.php');

        $_POST['photoFolder'] = str_replace("|||", "/", $_POST['photoFolder']);
        list($ImageWidth, $ImageHeight, $TypeCode) = getimagesize($_POST['photoFolder'] . $_POST['photoName']);
        $ImageType = ($TypeCode == 1 ? "gif" : ($TypeCode == 2 ? "jpeg" : ($TypeCode == 3 ? "png" : ($TypeCode == 6 ? "bmp" : FALSE))));
        $CreateFunction = "imagecreatefrom" . $ImageType;
        $OutputFunction = "image" . $ImageType;
        if ($ImageType) {
            $ImageSource = $CreateFunction($_POST['photoFolder'] . $_POST['photoName']);
            $ResizedImage = imagecreatetruecolor($_POST['w'], $_POST['h']);
            imagecopyresampled($ResizedImage, $ImageSource, 0, 0, $_POST['x'], $_POST['y'], $ImageWidth, $ImageHeight, $ImageWidth, $ImageHeight);
            imagejpeg($ResizedImage, $_POST['photoFolder'] . $_POST['photoName'], 100);
            // Rename
            $target_folder = 'public/slide_show/tmp/';
            $target_thumbnail = 'public/slide_show/tmp/thumbnail/';
            $ext = explode(".", $_POST['photoName']);
            $target_name = rand() . '.' . $ext[sizeof($ext) - 1];
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_folder, $target_name, $_POST['w'], $_POST['h'], 100, true);
            Resize($_POST['photoFolder'], $_POST['photoName'], $target_thumbnail, $target_name, $_POST['w'], $_POST['h'], 100, true);
            @unlink($target_folder . $_POST['photoName']);
        }
        echo $target_name;
        exit();
    }

}

?>
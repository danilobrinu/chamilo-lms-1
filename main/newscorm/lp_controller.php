<?php
/* For licensing terms, see /license.txt */

/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action
 * @package chamilo.learnpath
 * @author Yannick Warnier <ywarnier@beeznest.org>
 */
use ChamiloSession as Session;

// Flag to allow for anonymous user - needs to be set before global.inc.php.
$use_anonymous = true;

$debug = 0;
if ($debug > 0) error_log('New LP -+- Entered lp_controller.php -+- (action: '.$_REQUEST['action'].')', 0);

$current_course_tool = TOOL_LEARNPATH;
$_course = api_get_course_info();

$glossaryExtraTools = api_get_setting('glossary.show_glossary_in_extra_tools');
$showGlossary = in_array($glossaryExtraTools, array('true', 'lp', 'exercise_and_lp'));

if ($showGlossary) {
    if (api_get_setting('document.show_glossary_in_documents') == 'ismanual' ||
        api_get_setting('document.show_glossary_in_documents') == 'isautomatic'
    ) {
        $htmlHeadXtra[] = '<script>
    <!--
        var jQueryFrameReadyConfigPath = \'' . api_get_jquery_web_path() . '\';
    -->
    </script>';
        $htmlHeadXtra[] = '<script src="'.api_get_path(
                WEB_LIBRARY_JS_PATH
            ).'jquery.frameready.js" type="text/javascript" language="javascript"></script>';
        $htmlHeadXtra[] = '<script src="'.api_get_path(
                WEB_LIBRARY_JS_PATH
            ).'jquery.highlight.js" type="text/javascript" language="javascript"></script>';
    }
}

$htmlHeadXtra[] = '<script>
function setFocus(){
    $("#idTitle").focus();
}
$(window).load(function () {
    setFocus();
});
</script>
<style>
form .label {
    padding: 1px 3px 2px;
    font-size: 100%;
    font-weight: normal;
    color: #ffffff;
    text-transform: none;
    background: none;
    border-radius: unset;
    color: #404040;
    float: left;
    line-height: 18px;
    padding-top: 6px;
    text-align: right;
    width: 150px;
    text-shadow:none;
}
</style>';
$ajax_url = api_get_path(WEB_AJAX_PATH).'lp.ajax.php?'.api_get_cidreq();
$htmlHeadXtra[] = '
<script>
    /*
    Script to manipuplate Learning Path items with Drag and drop
     */
    var newOrderData = "";
    var lptree_debug = "";  // for debug
    var lp_id_list = "";    // for debug

    // uncomment for some debug display utility
    /*
    $(document).ready(function() {
        buildLPtree_debug($("#lp_item_list"), 0, 0);
        alert(lp_id_list+"\n\n"+lptree_debug);
    });
    */

    function buildLPtree(in_elem, in_parent_id) {
        var item_tag = in_elem.get(0).tagName;
        var item_id =  in_elem.attr("id");
        var parent_id = item_id;

        if (item_tag == "LI" && item_id != undefined) {
            // in_parent_id de la forme UL_x
            newOrderData += item_id+"|"+get_UL_integer_id(in_parent_id)+"^";
        }

        in_elem.children().each(function () {
            buildLPtree($(this), parent_id);
        });
    }

    // same than buildLPtree with some text display for debug in string lptree_debug
    function buildLPtree_debug(in_elem, in_lvl, in_parent_id) {
        var item_tag = in_elem.get(0).tagName;
        var item_id =  in_elem.attr("id");
        var parent_id = item_id;

        if (item_tag == "LI" && item_id != undefined) {
            for (i=0; i < 4 * in_lvl; i++) {
                lptree_debug += " ";
            }
            lptree_debug += " Lvl="+(in_lvl - 1)/2+" ("+item_tag+" "+item_id+" Fils de="+in_parent_id+") \n";
            // in_parent_id de la forme UL_x
            lp_id_list += item_id+"|"+get_UL_integer_id(in_parent_id)+"^";
        }

        in_elem.children().each(function () {
            buildLPtree_debug($(this), in_lvl + 1, parent_id);
        });
    }

    // return the interge part of an UL id
    // (0 for lp_item_list)
    function get_UL_integer_id(in_ul_id) {
        in_parent_integer_id = in_ul_id;
        in_parent_integer_id = in_parent_integer_id.replace("lp_item_list", "0");
        in_parent_integer_id = in_parent_integer_id.replace("UL_", "");
        return in_parent_integer_id;
    }

    $(function() {
        $(".lp_resource").sortable({
            items: ".lp_resource_element ",
            handle: ".moved", //only the class "moved"
            cursor: "move",
            connectWith: "#lp_item_list",
            placeholder: "ui-state-highlight", //defines the yellow highlight

            start: function(event, ui) {
                $(ui.item).css("width", "160px");
                $(ui.item).find(".item_data").attr("style", "");
            },
            stop: function(event, ui) {
                $(ui.item).css("width", "100%");
            }
        });

        $("#lp_item_list").sortable({
            items: "li",
            handle: ".moved", //only the class "moved"
            cursor: "move",
            placeholder: "ui-state-highlight", //defines the yellow highlight
            update: function(event, ui) {
                buildLPtree($("#lp_item_list"), 0);
                var order = "new_order="+ newOrderData + "&a=update_lp_item_order";

                $.post(
                    "'.$ajax_url.'",
                    order,
                    function(reponse){
                        $("#message").html(reponse);
                        order = "";
                        newOrderData = "";
                    }
                );
            },

            receive: function(event, ui) {
                var id = $(ui.item).attr("data_id");
                var type = $(ui.item).attr("data_type");
                var title = $(ui.item).attr("title");
                processReceive = true;

                if (ui.item.parent()[0]) {
                    var parent_id = $(ui.item.parent()[0]).attr("id");
                    var previous_id = $(ui.item.prev()).attr("id");

                    if (parent_id) {
                        parent_id = parent_id.split("_")[1];
                        var params = {
                            "a": "add_lp_item",
                            "id": id,
                            "parent_id": parent_id,
                            "previous_id": previous_id,
                            "type": type,
                            "title" : title
                        };
                        $.ajax({
                            type: "GET",
                            url: "'.$ajax_url.'",
                            data: params,
                            async: false,
                            success: function(data) {
                                if (data == -1) {
                                } else {
                                    $(".normal-message").hide();
                                    $(ui.item).attr("id", data);
                                    $(ui.item).addClass("lp_resource_element_new");
                                    $(ui.item).find(".item_data").attr("style", "");
                                    $(ui.item).addClass("record li_container");
                                    $(ui.item).removeClass("lp_resource_element");
                                    $(ui.item).removeClass("doc_resource");
                                }
                            }
                        });
                    }
                }//
            }//end receive
        });
        processReceive = false;
    });
</script>
';

$session_id = api_get_session_id();

api_protect_course_script(true);

$lpfound = false;

$myrefresh = 0;
$myrefresh_id = 0;

$refreshFromSession = Session::read('refresh');

if ($refreshFromSession == 1) {
    // Check if we should do a refresh of the oLP object (for example after editing the LP).
    // If refresh is set, we regenerate the oLP object from the database (kind of flush).
    Session::erase('refresh');
    $myrefresh = 1;
    if ($debug > 0) error_log('New LP - Refresh asked', 0);
}

if ($debug > 0) error_log('New LP - Passed refresh check', 0);

$lp_controller_touched = 1;
$lp_found = false;
$lpId = isset($_REQUEST['lp_id']) ? $_REQUEST['lp_id'] : '';
$course_id = api_get_course_int_id();

if ($debug>0) error_log('New LP - Passed data remains check', 0);

$learnPath = learnpath::getCurrentLpFromSession();

if (!$lp_found || (!empty($_REQUEST['lp_id']) && !empty($learnPath) && $learnPath->lp_id != $_REQUEST['lp_id'])) {
    if ($debug > 0) error_log('New LP - oLP is not object, has changed or refresh been asked, getting new', 0);
    // Regenerate a new lp object? Not always as some pages don't need the object (like upload?)
    if (!empty($_REQUEST['lp_id']) || !empty($myrefresh_id)) {
        if ($debug > 0) error_log('New LP - lp_id is defined', 0);
        // Select the lp in the database and check which type it is (scorm/dokeos/aicc) to generate the
        // right object.
        if (!empty($_REQUEST['lp_id'])) {
            $lp_id = intval($_REQUEST['lp_id']);
        } else {
            $lp_id = intval($myrefresh_id);
        }

        $lp_table = Database::get_course_table(TABLE_LP_MAIN);
        if (is_numeric($lp_id)) {
            $sel = "SELECT lp_type FROM $lp_table
                    WHERE c_id = $course_id AND id = $lp_id";
            if ($debug > 0) error_log('New LP - querying '.$sel, 0);
            $res = Database::query($sel);

            if (Database::num_rows($res)) {
                $row = Database::fetch_array($res);
                $type = $row['lp_type'];
                if ($debug > 0) error_log('New LP - found row - type '.$type. ' - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(), 0);
                switch ($type) {
                    case 1:
                        if ($debug > 0) error_log('New LP - found row - type dokeos - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(), 0);

                        $oLP = new learnpath(api_get_course_id(), $lp_id, api_get_user_id());
                        if ($oLP !== false) { $lp_found = true; } else { error_log($oLP->error, 0); }
                        break;
                    case 2:
                        if ($debug > 0) error_log('New LP - found row - type scorm - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(), 0);
                        $oLP = new scorm(api_get_course_id(), $lp_id, api_get_user_id());
                        if ($oLP !== false) { $lp_found = true; } else { error_log($oLP->error, 0); }
                        break;
                    case 3:
                        if ($debug > 0) error_log('New LP - found row - type aicc - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(), 0);
                        $oLP = new aicc(api_get_course_id(), $lp_id, api_get_user_id());
                        if ($oLP !== false) { $lp_found = true; } else { error_log($oLP->error, 0); }
                        break;
                    default:
                        if ($debug > 0) error_log('New LP - found row - type other - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(), 0);
                        $oLP = new learnpath(api_get_course_id(), $lp_id, api_get_user_id());
                        if ($oLP !== false) { $lp_found = true; } else { error_log($oLP->error, 0); }
                        break;
                }
            }
        } else {
            if ($debug > 0) error_log('New LP - Request[lp_id] is not numeric', 0);
        }
    } else {
        if ($debug > 0) error_log('New LP - Request[lp_id] and refresh_id were empty', 0);
    }
    if ($lp_found) {
        Session::write('oLP', $oLP);
    }
}

if ($debug > 0) error_log('New LP - Passed oLP creation check', 0);

$is_allowed_to_edit = api_is_allowed_to_edit(false, true, false, false);

if (isset($learnPath)) {
    $learnPath->update_queue = array(); // Reinitialises array used by javascript to update items in the TOC.
}

if (isset($_GET['isStudentView']) && $_GET['isStudentView'] == 'true') {
    if ($_REQUEST['action'] != 'list' AND $_REQUEST['action'] != 'view') {
        if (!empty($_REQUEST['lp_id'])) {
            $_REQUEST['action'] = 'view';
        } else {
            $_REQUEST['action'] = 'list';
        }
    }
} else {
    if ($is_allowed_to_edit) {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view' && !isset($_REQUEST['exeId'])) {
            $_REQUEST['action'] = 'build';
        }
        //$_SESSION['studentview'] = null;
    }
}

$action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : '');

// format title to be displayed correctly if QUIZ
$post_title = "";
if (isset($_POST['title'])) {
    $post_title = Security::remove_XSS($_POST['title']);
    if (isset($_POST['type']) && isset($_POST['title']) && $_POST['type'] == TOOL_QUIZ && !empty($_POST['title'])) {
        $post_title = Exercise::format_title_variable($_POST['title']);
    }
}

$redirectTo = null;

switch ($action) {
    case 'add_item':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - add item action triggered', 0);

        if (!$lp_found) {
            //check if the learnpath ID was defined, otherwise send back to list
            if ($debug > 0) error_log('New LP - No learnpath given for add item', 0);
            require 'lp_list.php';
        } else {
            Session::write('refresh', 1);

            if (isset($_POST['submit_button']) && !empty($post_title)) {
                // If a title was sumbitted:

                //Updating the lp.modified_on
                $learnPath->set_modified_on();
                $postTimeFromSession = Session::read('post_time');               
          
                if (isset($postTimeFromSession) && $postTimeFromSession == $_POST['post_time']) {
                    // Check post_time to ensure ??? (counter-hacking measure?)
                    require 'lp_add_item.php';
                } else {
                    Session::write('post_time', $_POST['post_time']);

                    $directoryParentId = isset($_POST['directory_parent_id']) ? $_POST['directory_parent_id'] : 0;

                    if (empty($directoryParentId)) {
                        $learnPath->generate_lp_folder($courseInfo);
                    }

                    $parent = isset($_POST['parent']) ? $_POST['parent'] : '';
                    $previous = isset($_POST['previous']) ? $_POST['previous'] : '';
                    $type = isset($_POST['type']) ? $_POST['type'] : '';
                    $path = isset($_POST['path']) ? $_POST['path'] : '';
                    $description = isset($_POST['description']) ? $_POST['description'] : '';
                    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : '';
                    $maxTimeAllowed = isset($_POST['maxTimeAllowed']) ? $_POST['maxTimeAllowed'] : '';

                    if ($_POST['type'] == TOOL_DOCUMENT) {
                        if (isset($_POST['path']) && $_GET['edit'] != 'true') {
                            $document_id = $_POST['path'];
                        } else {
                            $document_id = $learnPath->create_document(
                                $_course,
                                $_POST['content_lp'],
                                $_POST['title'],
                                'html',
                                $directoryParentId
                            );
                        }
                        $new_item_id = $learnPath->add_item(
                            $parent,
                            $previous,
                            $type,
                            $document_id,
                            $post_title,
                            $description,
                            $prerequisites
                        );
                    } else {
                        // For all other item types than documents, load the item using the item type and path rather than its ID.
                        $new_item_id = $learnPath->add_item(
                            $parent,
                            $previous,
                            $type,
                            $path,
                            $post_title,
                            $description,
                            $prerequisites,
                            $maxTimeAllowed
                        );
                    }
                    $url = api_get_self().'?action=add_item&type=step&lp_id='.intval($learnPath->lp_id).'&'.api_get_cidreq();
                    header('Location: '.$url);
                    exit;
                }
            } else {
                require 'lp_add_item.php';
            }
        }
        break;
    case 'add_users_to_category':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }

        require 'lp_subscribe_users_to_category.php';
        break;
    case 'add_audio':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }

        if ($debug > 0) error_log('New LP - add audio action triggered', 0);

        if (!$lp_found) {
            //check if the learnpath ID was defined, otherwise send back to list
            if ($debug > 0) error_log('New LP - No learnpath given for add audio', 0);
            require 'lp_list.php';
        } else {
            Session::write('refresh', 1);

            if (isset($_REQUEST['id'])) {
                $lp_item_obj = new learnpathItem($_REQUEST['id']);

                // Remove audio
                if (isset($_GET['delete_file']) && $_GET['delete_file'] == 1) {
                    $lp_item_obj->remove_audio();

                    $url = api_get_self().'?action=add_audio&lp_id='.intval(
                            $learnPath->lp_id
                        ).'&id='.$lp_item_obj->get_id().'&'.api_get_cidreq();
                    header('Location: '.$url);
                    exit;
                }

                // Upload audio
                if (isset($_FILES['file']) && !empty($_FILES['file'])) {
                    // Updating the lp.modified_on
                    $learnPath->set_modified_on();
                    $lp_item_obj->add_audio();
                }

                //Add audio file from documents
                if (isset($_REQUEST['document_id']) && !empty($_REQUEST['document_id'])) {
                    $learnPath->set_modified_on();
                    $lp_item_obj->add_audio_from_documents($_REQUEST['document_id']);
                }

                $learnPath->updateCurrentLpFromSession();

                // Display.
                require 'lp_add_audio.php';
            } else {
                require 'lp_add_audio.php';
            }
        }
        break;
    case 'add_lp_category':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        require 'lp_add_category.php';
        break;
    case 'move_up_category':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if (isset($_REQUEST['id'])) {
            learnpath::moveUpCategory($_REQUEST['id']);
        }
        require 'lp_list.php';
        break;
    case 'move_down_category':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if (isset($_REQUEST['id'])) {
            learnpath::moveDownCategory($_REQUEST['id']);
        }
        require 'lp_list.php';
        break;
    case 'delete_lp_category':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if (isset($_REQUEST['id'])) {
            learnpath::deleteCategory($_REQUEST['id']);
        }
        require 'lp_list.php';
        break;
    case 'add_lp':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - add_lp action triggered', 0);
        if (isset($_REQUEST['lp_name']) && !empty($_REQUEST['lp_name'])) {
            $_REQUEST['lp_name'] = trim($_REQUEST['lp_name']);
            Session::write('refresh', 1);
            $postTimeFromSession = Session::read('post_time');

            if (isset($postTimeFromSession) && $postTimeFromSession == $_REQUEST['post_time']) {
                require 'lp_add.php';
            } else {
                Session::write('post_time', $_REQUEST['post_time']);

                if (isset($_REQUEST['activate_start_date_check']) &&
                    $_REQUEST['activate_start_date_check'] == 1
                ) {
                	$publicated_on = $_REQUEST['publicated_on'];
                } else {
                	$publicated_on = null;
                }

                if (isset($_REQUEST['activate_end_date_check']) &&
                    $_REQUEST['activate_end_date_check'] == 1
                ) {
                	$expired_on = $_REQUEST['expired_on'];
                } else {
                	$expired_on = null;
                }

                $new_lp_id = learnpath::add_lp(
                    api_get_course_id(),
                    Security::remove_XSS($_REQUEST['lp_name']),
                    '',
                    'chamilo',
                    'manual',
                    '',
                    $publicated_on,
                    $expired_on,
                    $_REQUEST['category_id']
                );

                if (is_numeric($new_lp_id)) {
                    // TODO: Maybe create a first module directly to avoid bugging the user with useless queries
                    $learnPath = new learnpath(
                        api_get_course_id(),
                        $new_lp_id,
                        api_get_user_id()
                    );
                    $learnPath->updateCurrentLpFromSession();
                    $url = api_get_self().'?action=add_item&type=step&lp_id='.intval($new_lp_id).'&'.api_get_cidreq();
                    header("Location: $url&isStudentView=false");
                    exit;
                }
            }
        } else {
            require 'lp_add.php';
        }
        break;
    case 'admin_view':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - admin_view action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for admin_view', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            require 'lp_admin_view.php';
        }
        break;
    case 'auto_launch':
        if (api_get_course_setting('enable_lp_auto_launch') == 1) { //Redirect to a specific LP
            if (!$is_allowed_to_edit) {
                api_not_allowed(true);
            }
            if ($debug > 0) error_log('New LP - auto_launch action triggered', 0);
            if (!$lp_found) { error_log('New LP - No learnpath given for set_autolaunch', 0); require 'lp_list.php'; }
            else {
                $learnPath->set_autolaunch($_GET['lp_id'], $_GET['status']);
                require 'lp_list.php';
                exit;
            }
        }
        break;
    case 'build':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - build action triggered', 0);

        if (!$lp_found) { error_log('New LP - No learnpath given for build', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            //require 'lp_build.php';
            $url = api_get_self().'?action=add_item&type=step&lp_id='.intval(
                    $learnPath->lp_id
                ).'&'.api_get_cidreq();
            header('Location: '.$url);
            exit;
        }
        break;
    case 'edit_item':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - edit item action triggered', 0);

        if (!$lp_found) { error_log('New LP - No learnpath given for edit item', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            if (isset($_POST['submit_button']) && !empty($post_title)) {

                // Updating the lp.modified_on
                $learnPath->set_modified_on();

                // TODO: mp3 edit
                $audio = array();
                if (isset($_FILES['mp3'])) {
                    $audio = $_FILES['mp3'];
                }

                $description = isset($_POST['description']) ? $_POST['description'] : '';
                $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : '';
                $maxTimeAllowed = isset($_POST['maxTimeAllowed']) ? $_POST['maxTimeAllowed'] : '';
                $url = isset($_POST['url']) ? $_POST['url'] : '';

                $learnPath->edit_item(
                    $_REQUEST['id'],
                    $_POST['parent'],
                    $_POST['previous'],
                    $post_title,
                    $description,
                    $prerequisites,
                    $audio,
                    $maxTimeAllowed,
                    $url
                );

                if (isset($_POST['content_lp'])) {
                    $learnPath->edit_document($_course);
                }
                $is_success = true;
                $learnPath->updateCurrentLpFromSession();
                $url = api_get_self().'?action=add_item&type=step&lp_id='.intval($learnPath->lp_id).'&'.api_get_cidReq();
                header('Location: '.$url);
                exit;
            }
            if (isset($_GET['view']) && $_GET['view'] == 'build') {
                require 'lp_edit_item.php';
            } else {
                require 'lp_admin_view.php';
            }
        }
        break;
    case 'edit_item_prereq':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - edit item prereq action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for edit item prereq', 0); require 'lp_list.php'; }
        else {
            if (isset($_POST['submit_button'])) {
                //Updating the lp.modified_on
                $learnPath->set_modified_on();
                Session::write('refresh', 1);
                $editPrerequisite = $learnPath->edit_item_prereq(
                    $_GET['id'],
                    $_POST['prerequisites'],
                    $_POST['min_' . $_POST['prerequisites']],
                    $_POST['max_' . $_POST['prerequisites']]
                );

                if ($editPrerequisite) {
                    $is_success = true;
                }

                $learnPath->updateCurrentLpFromSession();

                $url = api_get_self(
                    ).'?action=add_item&type=step&lp_id='.intval(
                        $learnPath->lp_id
                    );
                header('Location: '.$url);
                exit;
            } else {
                require 'lp_edit_item_prereq.php';
            }
        }
        break;
    case 'move_item':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - move item action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for move item', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            if (isset($_POST['submit_button'])) {
                //Updating the lp.modified_on
                $learnPath->set_modified_on();
                $learnPath->edit_item(
                    $_GET['id'],
                    $_POST['parent'],
                    $_POST['previous'],
                    $post_title,
                    $_POST['description']
                );
                $learnPath->updateCurrentLpFromSession();
                $is_success = true;
                $url = api_get_self(
                    ).'?action=add_item&type=step&lp_id='.intval(
                        $learnPath->lp_id
                    ).'&'.api_get_cidreq();
                header('Location: '.$url);
            }
            if (isset($_GET['view']) && $_GET['view'] == 'build') {
                require 'lp_move_item.php';
            } else {
                // Avoids weird behaviours see CT#967.
                $check = Security::check_token('get');
                if ($check) {
                    $learnPath->move_item($_GET['id'], $_GET['direction']);
                }
                Security::clear_token();
                require 'lp_admin_view.php';
            }
        }
        break;
    case 'view_item':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - view_item action triggered', 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for view item', 0); require 'lp_list.php';
        } else {

            require 'lp_view_item.php';
        }
        break;
    case 'upload':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - upload action triggered', 0);
        $cwdir = getcwd();
        require 'lp_upload.php';
        // Reinit current working directory as many functions in upload change it.
        chdir($cwdir);
        require 'lp_list.php';
        break;
    case 'copy':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }

        $hideScormCopyLink = api_get_setting('course.hide_scorm_copy_link');
        if ($hideScormCopyLink === 'true') {
            api_not_allowed(true);
        }

        if ($debug > 0) error_log('New LP - export action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for copy', 0); require 'lp_list.php'; }
        else {
            $learnPath->copy();
        }
        require 'lp_list.php';
        break;
    case 'export':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        $hideScormExportLink = api_get_setting('course.hide_scorm_export_link');
        if ($hideScormExportLink === 'true') {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - export action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for export', 0); require 'lp_list.php'; }
        else {
            $learnPath->scorm_export();
            exit();
        }
        break;
    case 'export_to_pdf':
        if (!learnpath::is_lp_visible_for_student(
            $learnPath->lp_id,
            api_get_user_id()
        )
        ) {
            api_not_allowed();
        }
        $hideScormPdfLink = api_get_setting('course.hide_scorm_pdf_link');
        if ($hideScormPdfLink === 'true') {
            api_not_allowed(true);
        }

        if ($debug > 0) error_log('New LP - export action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for export_to_pdf', 0); require 'lp_list.php';
        } else {
            $result = $learnPath->scorm_export_to_pdf($_GET['lp_id']);
            if (!$result) {
                require 'lp_list.php';
            }
            exit;
        }
        break;
    case 'delete':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - delete action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for delete', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            $learnPath->delete(null, $_GET['lp_id'], 'remove');
            Session::erase('oLP');
            require 'lp_list.php';
        }
        break;
    case 'toggle_visible':
        // Change lp visibility (inside lp tool).
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - visibility action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for visibility', 0); require 'lp_list.php'; }
        else {
            learnpath::toggle_visibility($_REQUEST['lp_id'], $_REQUEST['new_status']);
            require 'lp_list.php';
        }
        break;
    case 'toggle_publish':
        // Change lp published status (visibility on homepage).
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - publish action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for publish', 0); require 'lp_list.php'; }
        else {
            learnpath::toggle_publish($_REQUEST['lp_id'], $_REQUEST['new_status']);
            require 'lp_list.php';
        }
        break;
    case 'move_lp_up':
        // Change lp published status (visibility on homepage)
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - publish action triggered', 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for publish', 0);
            require 'lp_list.php';
        } else {
            learnpath::move_up($_REQUEST['lp_id']);
            require 'lp_list.php';
        }
        break;
    case 'move_lp_down':
        // Change lp published status (visibility on homepage)
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - publish action triggered', 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for publish', 0);
            require 'lp_list.php';
        } else {
            learnpath::move_down($_REQUEST['lp_id']);
            require 'lp_list.php';
        }
        break;
    case 'edit':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - edit action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for edit', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            require 'lp_edit.php';
        }
        break;
    case 'update_lp':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - update_lp action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for edit', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            $lp_name = Security::remove_XSS($_REQUEST['lp_name']);
            $learnPath->set_name($lp_name);
            $author = $_REQUEST['lp_author'];
            // Fixing the author name (no body or html tags).
            $auth_init = stripos($author, '<p>');
            if ($auth_init === false) {
                $auth_init = stripos($author, '<body>');
                $auth_end = $auth_init + stripos(substr($author, $auth_init + 6), '</body>') + 7;
                $len = $auth_end - $auth_init + 6;
            } else {
                $auth_end = strripos($author, '</p>');
                $len = $auth_end - $auth_init + 4;
            }

            $author_fixed = substr($author, $auth_init, $len);
            //$author_fixed = $author;

            $learnPath->set_author($author_fixed);
            // TODO (as of Chamilo 1.8.8): Check in the future whether this field is needed.
            $learnPath->set_encoding($_REQUEST['lp_encoding']);

            if (isset($_REQUEST['lp_maker'])) {
                $learnPath->set_maker($_REQUEST['lp_maker']);
            }
            if (isset($_REQUEST['lp_proximity'])) {
                $learnPath->set_proximity($_REQUEST['lp_proximity']);
            }
            $learnPath->set_theme($_REQUEST['lp_theme']);

            if (isset($_REQUEST['hide_toc_frame']) && $_REQUEST['hide_toc_frame'] == 1) {
                $hide_toc_frame = $_REQUEST['hide_toc_frame'];
            } else {
                $hide_toc_frame = null;
            }
            $learnPath->set_hide_toc_frame($hide_toc_frame);
            $learnPath->set_prerequisite($_REQUEST['prerequisites']);
            $learnPath->set_use_max_score($_REQUEST['use_max_score']);
            $subscribers = isset($_REQUEST['subscribe_users']) ? $_REQUEST['subscribe_users'] : '';
            $learnPath->setSubscribeUsers($subscribers);

            if (isset($_REQUEST['activate_start_date_check']) && $_REQUEST['activate_start_date_check'] == 1) {
            	$publicated_on  = $_REQUEST['publicated_on'];
            } else {
            	$publicated_on = null;
            }

            if (isset($_REQUEST['activate_end_date_check']) && $_REQUEST['activate_end_date_check'] == 1) {
                $expired_on = $_REQUEST['expired_on'];
            } else {
                $expired_on = null;
            }
            $learnPath->setCategoryId($_REQUEST['category_id']);
            $learnPath->set_modified_on();
            $learnPath->set_publicated_on($publicated_on);
            $learnPath->set_expired_on($expired_on);

            if (isset($_REQUEST['remove_picture']) && $_REQUEST['remove_picture']) {
                $learnPath->delete_lp_image();
            }

            $extraFieldValue = new ExtraFieldValue('lp');
            $params = array(
                'lp_id' => $learnPath->id,
            );
            $extraFieldValue->saveFieldValues($_REQUEST);

            if ($_FILES['lp_preview_image']['size'] > 0)
                $learnPath->upload_image($_FILES['lp_preview_image']);

            if (api_get_setting('search.search_enabled') === 'true') {
                require_once api_get_path(LIBRARY_PATH).'specific_fields_manager.lib.php';
                $specific_fields = get_specific_field_list();
                foreach ($specific_fields as $specific_field) {
                    $learnPath->set_terms_by_prefix(
                        $_REQUEST[$specific_field['code']],
                        $specific_field['code']
                    );
                    $new_values = explode(',', trim($_REQUEST[$specific_field['code']]));
                    if (!empty($new_values)) {
                        array_walk($new_values, 'trim');
                        delete_all_specific_field_value(
                            api_get_course_id(),
                            $specific_field['id'],
                            TOOL_LEARNPATH,
                            $learnPath->lp_id
                        );

                        foreach ($new_values as $value) {
                            if (!empty($value)) {
                                add_specific_field_value(
                                    $specific_field['id'],
                                    api_get_course_id(),
                                    TOOL_LEARNPATH,
                                    $learnPath->lp_id,
                                    $value
                                );
                            }
                        }
                    }
                }
            }
            $learnPath->updateCurrentLpFromSession();
            $url = api_get_self().'?action=add_item&type=step&lp_id='.intval(
                    $learnPath->lp_id
                ).'&'.api_get_cidreq();
            header('Location: '.$url);
            exit;
        }
        break;
    case 'add_sub_item': // Add an item inside a chapter.
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - add sub item action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for add sub item', 0); require 'lp_list.php'; }
        else {
            Session::write('refresh', 1);
            if (!empty($_REQUEST['parent_item_id'])) {
                Session::write('from_learnpath', 'yes');
                Session::write(
                    'origintoolurl',
                    'lp_controller.php?action=admin_view&lp_id='.intval(
                        $_REQUEST['lp_id']
                    )
                );
                require 'resourcelinker.php';
            } else {
                require 'lp_admin_view.php';
            }
        }
        break;
    case 'deleteitem':
    case 'delete_item':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - delete item action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for delete item', 0); require 'lp_list.php'; }
        else {
            //Session::write('refresh', 1);
            if (!empty($_REQUEST['id'])) {
                $learnPath->delete_item($_REQUEST['id']);
                $learnPath->updateCurrentLpFromSession();
            }
            $url = api_get_self().'?action=add_item&type=step&lp_id='.intval($_REQUEST['lp_id']).'&'.api_get_cidreq();
            header('Location: '.$url);
            exit;
        }
        break;
    case 'edititemprereq':
    case 'edit_item_prereq':
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }
        if ($debug > 0) error_log('New LP - edit item prereq action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for edit item prereq', 0); require 'lp_list.php'; }
        else {
            if (!empty($_REQUEST['id']) && !empty($_REQUEST['submit_item'])) {
                Session::write('refresh', 1);
                $learnPath->edit_item_prereq(
                    $_REQUEST['id'],
                    $_REQUEST['prereq']
                );
                $learnPath->updateCurrentLpFromSession();
            }
            require 'lp_admin_view.php';
        }
        break;
    case 'restart':
        if ($debug > 0) error_log('New LP - restart action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for restart', 0); require 'lp_list.php'; }
        else {
            $learnPath->restart();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_view.php';
        }
        break;
    case 'last':
        if ($debug > 0) error_log('New LP - last action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for last', 0); require 'lp_list.php'; }
        else {
            $learnPath->last();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_view.php';
        }
        break;
    case 'first':
        if ($debug > 0) error_log('New LP - first action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for first', 0); require 'lp_list.php'; }
        else {
            $learnPath->first();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_view.php';
        }
        break;
    case 'next':
        if ($debug > 0) error_log('New LP - next action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for next', 0); require 'lp_list.php'; }
        else {
            $learnPath->next();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_view.php';
        }
        break;
    case 'previous':
        if ($debug > 0) error_log('New LP - previous action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for previous', 0); require 'lp_list.php'; }
        else {
            $learnPath->previous();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_view.php';
        }
        break;
    case 'content':
        if ($debug > 0) error_log('New LP - content action triggered', 0);
        if ($debug > 0) error_log('New LP - Item id is '.intval($_GET['item_id']), 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for content', 0);
            require 'lp_list.php';
        } else {
            $learnPath->save_last();
            $learnPath->set_current_item($_GET['item_id']);
            $learnPath->start_current_item();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_content.php';
        }
        break;
    case 'view':
        if ($debug > 0)
            error_log('New LP - view action triggered', 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for view', 0);
            require 'lp_list.php';
        } else {
            if ($debug > 0) {error_log('New LP - Trying to set current item to ' . $_REQUEST['item_id'], 0); }
            if ( !empty($_REQUEST['item_id']) ) {
                $learnPath->set_current_item($_REQUEST['item_id']);
                $learnPath->updateCurrentLpFromSession();
            }
            require 'lp_view.php';
        }
        break;
    case 'save':
        if ($debug > 0) error_log('New LP - save action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for save', 0); require 'lp_list.php'; }
        else {
            $learnPath->save_item();
            $learnPath->updateCurrentLpFromSession();
            require 'lp_save.php';
        }
        break;
    case 'stats':
        if ($debug > 0) error_log('New LP - stats action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for stats', 0); require 'lp_list.php'; }
        else {
            $learnPath->save_current();
            $learnPath->save_last();
            $learnPath->updateCurrentLpFromSession();
            $output = require 'lp_stats.php';
            echo $output;
        }
        break;
    case 'list':
        if ($debug > 0) error_log('New LP - list action triggered', 0);
        if ($lp_found) {
            Session::write('refresh', 1);
            $learnPath->save_last();
            $learnPath->updateCurrentLpFromSession();
        }
        require 'lp_list.php';
        break;
    case 'mode':
        // Switch between fullscreen and embedded mode.
        if ($debug > 0) error_log('New LP - mode change triggered', 0);
        $mode = $_REQUEST['mode'];
        if ($mode == 'fullscreen') {
            $learnPath->mode = 'fullscreen';
        } elseif ($mode == 'embedded') {
            $learnPath->mode = 'embedded';
        } elseif ($mode == 'embedframe') {
            $learnPath->mode = 'embedframe';
        } elseif ($mode == 'impress') {
            $learnPath->mode = 'impress';
        }
        $learnPath->updateCurrentLpFromSession();
        require 'lp_view.php';
        break;
    case 'switch_view_mode':
        if ($debug > 0) error_log('New LP - switch_view_mode action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for switch', 0); require 'lp_list.php'; }
        if (Security::check_token('get')) {
            Session::write('refresh', 1);
            $learnPath->update_default_view_mode();
            $learnPath->updateCurrentLpFromSession();
        }
        require 'lp_list.php';
        break;
    case 'switch_force_commit':
        if ($debug > 0) error_log('New LP - switch_force_commit action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for switch', 0); require 'lp_list.php'; }
        Session::write('refresh', 1);
        $learnPath->update_default_scorm_commit();
        $learnPath->updateCurrentLpFromSession();
        require 'lp_list.php';
        break;
    /* Those 2 switches have been replaced by switc_attempt_mode switch
    case 'switch_reinit':
        if ($debug > 0) error_log('New LP - switch_reinit action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for switch', 0); require 'lp_list.php'; }
        Session::write('refresh', 1);
        $learnPath->update_reinit();
		require 'lp_list.php';
		break;
	case 'switch_seriousgame_mode':
		if($debug>0) error_log('New LP - switch_seriousgame_mode action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require 'lp_list.php'; }
		Session::write('refresh', 1);
		$learnPath->set_seriousgame_mode();
		require 'lp_list.php';
		break;
     */
	case 'switch_attempt_mode':
		if($debug>0) error_log('New LP - switch_reinit action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require 'lp_list.php'; }
        Session::write('refresh', 1);
        $learnPath->switch_attempt_mode();
        $learnPath->updateCurrentLpFromSession();
        require 'lp_list.php';
        break;
    case 'switch_scorm_debug':
        if ($debug > 0) error_log('New LP - switch_scorm_debug action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for switch', 0); require 'lp_list.php'; }
        Session::write('refresh', 1);
        $learnPath->update_scorm_debug();
        $learnPath->updateCurrentLpFromSession();
        require 'lp_list.php';
        break;
    case 'intro_cmdAdd':
        if ($debug > 0) error_log('New LP - intro_cmdAdd action triggered', 0);
        // Add introduction section page.
        break;
    case 'js_api_refresh':
        if ($debug > 0) error_log('New LP - js_api_refresh action triggered', 0);
        if (!$lp_found) { error_log('New LP - No learnpath given for js_api_refresh', 0); require 'lp_message.php'; }
        if (isset($_REQUEST['item_id'])) {
            $htmlHeadXtra[] = $learnPath->get_js_info($_REQUEST['item_id']);
        }
        require 'lp_message.php';
        break;
    case 'return_to_course_homepage':
        if (!$lp_found) { error_log('New LP - No learnpath given for stats', 0); require 'lp_list.php'; }
        else {
            $learnPath->save_current();
            $learnPath->save_last();
            $learnPath->updateCurrentLpFromSession();
            $url = api_get_path(WEB_COURSE_PATH).api_get_course_path().'/index.php?id_session='.api_get_session_id();
            if (isset($_GET['redirectTo']) && $_GET['redirectTo'] == 'lp_list') {
                $url = 'lp_controller.php?'.api_get_cidreq();
            }
            header('location: '.$url);
            exit;
        }
        break;
    case 'search':
        /* Include the search script, it's smart enough to know when we are
         * searching or not.
         */
        require 'lp_list_search.php';
        break;
    case 'impress':
        if ($debug > 0)
            error_log('New LP - view action triggered', 0);
        if (!$lp_found) {
            error_log('New LP - No learnpath given for view', 0);
            require 'lp_list.php';
        } else {
            if ($debug > 0) {error_log('New LP - Trying to impress this LP item to ' . $_REQUEST['item_id'], 0); }
            if (!empty($_REQUEST['item_id']) ) {
                $learnPath->set_current_item($_REQUEST['item_id']);
            }
            require 'lp_impress.php';
        }
        break;
    case 'set_previous_step_as_prerequisite':
        $learnPath->set_previous_step_as_prerequisite_for_all_items();
        $learnPath->updateCurrentLpFromSession();
        $url = api_get_self().'?action=add_item&type=step&lp_id='.intval(
                $learnPath->lp_id
            )."&".api_get_cidReq();
        Display::addFlash(Display::return_message(get_lang('ItemUpdated')));
        header('Location: '.$url);
        break;
    case 'clear_prerequisites':
        $learnPath->clear_prerequisites();
        $learnPath->updateCurrentLpFromSession();
        $url = api_get_self().'?action=add_item&type=step&lp_id='.intval(
                $learnPath->lp_id
            )."&".api_get_cidReq();
        Display::addFlash(Display::return_message(get_lang('ItemUpdated')));
        header('Location: '.$url);
        break;
    case 'toggle_seriousgame': //activate/deactive seriousgame_mode
        if (!$is_allowed_to_edit) {
            api_not_allowed(true);
        }

        if ($debug > 0) {
            error_log('New LP - seriousgame_mode action triggered');
        }

        if (!$lp_found) {
            error_log('New LP - No learnpath given for visibility');

            require 'lp_list.php';
        }

        Session::write('refresh', 1);
        $learnPath->set_seriousgame_mode();
        $learnPath->updateCurrentLpFromSession();
        require 'lp_list.php';
        break;
    case 'create_forum':
        if (!isset($_GET['id'])) {
            break;
        }

        $selectedItem = null;
        $lp = learnpath::getCurrentLpFromSession();

        foreach ($lp->items as $item) {
            if ($item->db_id == $_GET['id']) {
                $selectedItem = $item;
            }
        }

        if (!empty($selectedItem)) {
            $forumThread = $selectedItem->getForumThread(
                $lp->course_int_id,
                $lp->lp_session_id
            );

            if (empty($forumThread)) {
                require api_get_path(SYS_CODE_PATH) . 'forum/forumfunction.inc.php';

                $forumCategory = getForumCategoryByTitle(
                    get_lang('LearningPaths'),
                    $lp->course_int_id,
                    $lp->lp_session_id
                );

                $forumCategoryId = !empty($forumCategory) ? $forumCategory['cat_id']: 0;

                if (empty($forumCategoryId)) {
                    $forumCategoryId = store_forumcategory(
                        [
                            'lp_id' => 0,
                            'forum_category_title' => get_lang('LearningPaths'),
                            'forum_category_comment' => null
                        ],
                        [],
                        false
                    );
                }

                if (!empty($forumCategoryId)) {
                    $forum = $lp->getForum(
                        $lp->lp_session_id
                    );

                    $forumId = !empty($forum) ? $forum['forum_id'] : 0;

                    if (empty($forumId)) {
                        $forumId = $lp->createForum($forumCategoryId);
                    }

                    if (!empty($forumId)) {
                        $selectedItem->createForumTthread($forumId);
                    }
                }
            }
        }

        $learnPath->updateCurrentLpFromSession();
        Session::write('lpobject', serialize($learnPath));

        header('Location:' . api_get_self() . '?' . http_build_query([
            'action' => 'add_item',
            'type' => 'step',
            'lp_id' => $lp->lp_id
        ]));

        exit;
    case 'report':
        require 'lp_report.php';
        break;
    case 'dissociate_forum':
        if (!isset($_GET['id'])) {
            break;
        }

        $selectedItem = null;

        foreach ($_SESSION['oLP']->items as $item) {
            if ($item->db_id != $_GET['id']) {
                continue;
            }

            $selectedItem = $item;
        }

        if (!empty($selectedItem)) {
            $forumThread = $selectedItem->getForumThread(
                $_SESSION['oLP']->course_int_id,
                $_SESSION['oLP']->lp_session_id
            );

            if (!empty($forumThread)) {
                $dissoaciated = $selectedItem->dissociateForumThread($forumThread['iid']);

                if ($dissoaciated) {
                    Display::addFlash(
                        Display::return_message(get_lang('ForumDissociate'), 'success')
                    );
                }
            }
        }

        $learnPath->updateCurrentLpFromSession();
        Session::write('lpobject', serialize($learnPath));

        header('Location:' . api_get_self() . '?' . http_build_query([
            'action' => 'add_item',
            'type' => 'step',
            'lp_id' => $_SESSION['oLP']->lp_id
        ]));
        exit;
        break;
    case 'add_final_item':
        if (!$lp_found) {
            Display::addFlash(
                Display::return_message(get_lang('NoLPFound'), 'error')
            );
            break;
        }

        Session::write('refresh', 1);

        if (!isset($_POST['submit']) || empty($post_title)) {
            break;
        }

        $learnPath->getFinalItemForm();

        $redirectTo = api_get_self() . '?' . http_build_query([
            'action' => 'add_item',
            'type' => 'step',
            'lp_id' => intval$learnPath->lp_id)
        ]);
        break;
    default:
        if ($debug > 0) error_log('New LP - default action triggered', 0);
        require 'lp_list.php';
        break;
}

if (!empty($learnPath)) {
    $learnPath->updateCurrentLpFromSession();
    Session::write('lpobject', serialize($learnPath));
    if ($debug > 0) error_log('New LP - lpobject is serialized in session', 0);
}

if (!empty($redirectTo)) {
    header("Location: $redirectTo");
    exit;
}

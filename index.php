<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>fileBeetle</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
    </head>
    <?php
    // base directory
    $BASE_DIR                   = "./CABINET";
    // carriage return
    $CR                         = "\r\n";
    // root title
    $ROOTDIR_TEXT               = "root";
    $ALERT_SUCCESS              = "alert alert-success";
    $ALERT_ERROR                = "alert alert-error";
    // message
    $MESSAGE_MAKE_DIR_SUCCESS   = "Create directory Success!";
    $MESSAGE_MAKE_DIR_FAILED    = "Create directory Faild!";
    $MESSAGE_UPLOAD_SUCCESS     = "Upload file Success!";
    $MESSAGE_UPLOAD_FAILED      = "Upload file Failed!";
    $MESSAGE_UPLOAD_CHOOSE_FILE = "Choose a file.";
    $MESSAGE_UPLOAD_FILE_EXISTS = "A file already exists.";
    $MESSAGE_DELETE_SUCCESS     = "Delete Success!";
    $MESSAGE_DELETE_FALSE       = "Delete False!";
    $MESSAGE_CANT_READ          = "Directory cannot be read.";
    $MESSAGE_EMPTY              = "Directory is empty.";
    $MESSAGE_INFO_TITLE         = "If you get a file...";
    $MESSAGE_INFOMATION         = "Click on a link with your right mouse button.<br />".
                        "Select or [Save Target As...] (Internet Explorer)".
                        "or [Save Link As] (Firefox) from the pop-up menu.<br />".
                        "Select a folder to save the file and click [Save].";
    
    $message        = "";
    $array_dir      = array();
    $array_file     = array();
    $dir            = $BASE_DIR;
    $param_d        = "";
    $path           = "";
    $topic_path     = '<li><a href="?d=">'.$ROOTDIR_TEXT.'</a></li>';
    $alert_style    = "";
    $info_style     = "";
    
    $proc_message   = "";
    $proc_style     = "style='display:none'";
    $proc_class     = "";
    
    // delete directory and files
    function remove_directory($dir) {
        if ($handle = opendir("$dir")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dir/$item")) {
                        remove_directory("$dir/$item");
                    } else {
                        unlink("$dir/$item");
                    }
                }
            }
            closedir($handle);
            rmdir($dir);
        }
    }
    
    if (isset($_GET["d"])) {
        $param_d = htmlspecialchars($_GET["d"]);
        $dir .= $param_d;
    }
    
    if (isset($_POST['proc'])) {
        $proc_style = "";
        if ($_POST['proc']=="0") {
            // make directory
            if (mkdir($dir."/".htmlspecialchars($_POST['txt_dir']))) {
                $proc_message   = $MESSAGE_MAKE_DIR_SUCCESS;
                $proc_class     = $ALERT_SUCCESS;
            } else {
                $proc_message   = $MESSAGE_MAKE_DIR_FAILED;
                $proc_class     = $ALERT_ERROR;
            }
        }
        if ($_POST['proc']=="1") {
            // upload file
            if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
                if (isset($_POST['check_overwrite']) || !file_exists($dir."/".$_FILES["file"]["name"])) {
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $dir."/".$_FILES["file"]["name"])) {
                        chmod($dir."/".$_FILES["file"]["name"], 0644);
                        $proc_message = $_FILES["file"]["name"]." is uploaded.";
                        $proc_class     = $ALERT_SUCCESS;
                    } else {
                        $proc_message = $MESSAGE_UPLOAD_FAILED;
                        $proc_class   = $ALERT_ERROR;
                    }
                } else {
                    $proc_message = $MESSAGE_UPLOAD_FILE_EXISTS;
                    $proc_class   = $ALERT_ERROR;
                }
            } else {
                $proc_message = $MESSAGE_UPLOAD_CHOOSE_FILE;
                $proc_class   = $ALERT_ERROR;
            }
        }
        if ($_POST['proc']=="2") {
            // delete
            if ($_POST['deltype']=="D") {
                // delete director
                remove_directory($dir.'/'.$_POST['name']);
                $proc_message = $MESSAGE_DELETE_SUCCESS;
                $proc_class     = $ALERT_SUCCESS;
            } else if ($_POST['deltype']=="F") {
                // delete file
                if (unlink($dir.'/'.$_POST['name'])) {
                    $proc_message = $MESSAGE_DELETE_SUCCESS;
                    $proc_class     = $ALERT_SUCCESS;
                } else {
                    $proc_message = $MESSAGE_DELETE_FALSE;
                    $proc_class   = $ALERT_ERROR;
                }
            } else {
                $proc_message = $MESSAGE_DELETE_FALSE;
                $proc_class   = $ALERT_ERROR;
            }
        }
    }
    ?>
    <body>
        <span class="row">
            <span class="span12">
                <h1>fileBeetle</h1>
                <br />
                <?php
                // topic path
                $array_path = preg_split("/\//", $param_d);
                array_shift($array_path);
                foreach ($array_path as $value) {
                    $path       .= "/".$value;
                    $topic_path .= "<span class='divider'>/</span><li><a href='?d=$path'>".$value."</a></li>";
                }
                print '<ul class="breadcrumb">'.$CR;
                print $topic_path.$CR;
                print '</ul>'.$CR;
                // store data
                if ($handle = opendir($dir)) {
                    while ($entry = readdir($handle)) {
                        if (is_dir($dir."/".$entry)) {
                            // directory
                            if ($entry != "." && $entry != "..") {
                                array_push($array_dir,
                                        array(
                                            $param_d."/".$entry
                                            ,$entry
                                            ,"-"
                                            ,"-"
                                        ));
                            }
                        } else {
                            // file
                            if ("." != substr($entry, 0, 1)) {
                                $file_stat = stat($dir."/".$entry);
                                // file size
                                $file_size = 0;
                                if ($file_stat['size']<1024) {
                                    $file_size = $file_stat['size']." B";
                                } else if ($file_stat['size']<1024*1024) {
                                    $file_size = round($file_stat['size']/1024)." kB";
                                } else if ($file_stat['size']<1024*1024*1024) {
                                    $file_size = round($file_stat['size']/(1024*1024))." MB";
                                } else {
                                    $file_size = round($file_stat['size']/(1024*1024*1024))." GB";
                                }
                                
                                array_push($array_file,
                                        array(
                                            $dir
                                            ,$entry
                                            ,date("Y-m-d H:i", $file_stat['mtime'])
                                            ,$file_size
                                        ));
                            }
                        }
                    }
                    closedir($handle);
                } else {
                    $message = $MESSAGE_CANT_READ;
                }
                ?>
            </span>
        </span>
        
        <span class="row">
            <span class="span12">
                <a class="btn pull-right" data-toggle="modal" href="#uploadModal" >
                    <i class="icon-upload"></i>
                    Upload file
                </a>
                <span class="pull-right">&nbsp;</span>
                <a class="btn pull-right" data-toggle="modal" href="#mkdirModal" >
                    <i class="icon-folder-close"></i>
                    Create directory
                </a>
                <br />&nbsp;
                
                <div class="modal hide fade" id="uploadModal">
                    <form class="form-inline" name="form_upload" id="form_upload" method="post" enctype="multipart/form-data" action="" >
                        <div class="modal-header">
                            <a class="close" data-dismiss="modal">×</a>
                            <h3>Upload a file here.</h3>
                        </div>
                        <div class="modal-body">
                            <input type="file" class="btn" name="file" id="file" />
                            <label class="checkbox">
                                <input type="checkbox" class="" name="check_overwrite" id="check_overwrite" /> Overwrite
                            </label>
                            <input type="hidden" name="proc" id="hd_upload" value="1" />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="btn_upload">
                                <i class="icon-ok icon-white"></i>
                                Upload
                            </button>
                            <a href="" class="btn" data-dismiss="modal">
                                <i class="icon-remove"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="modal hide fade" id="mkdirModal">
                    <form class="form-inline" name="form_mkdir" id="form_mkdir" method="post" action="" >
                        <div class="modal-header">
                            <a class="close" data-dismiss="modal">×</a>
                            <h3>Create a directory here.</h3>
                        </div>
                        <div class="modal-body">                        
                            <input type="text" class="span5" name="txt_dir" id="txt_dir" placeholder="type directory name…" />
                            <input type="hidden" name="proc" id="hd_mkdir" value="0" />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="btn_dir">
                                <i class="icon-ok icon-white"></i>Create
                            </button>
                            <a href="" class="btn" data-dismiss="modal">
                                <i class="icon-remove"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="modal hide fade" id="delModal">
                    <form class="form-inline" name="form_delete" id="form_del" method="post" action="" >
                        <div class="modal-header">
                            <a class="close" data-dismiss="modal">×</a>
                            <h3>Delete.</h3>
                        </div>
                        <div class="modal-body">  
                            <p class="delete" id="txt_del">Delete</p>
                            <p class="warntext" id="txt_warn">&nbsp;</p>
                            <input type="hidden" name="deltype" id="hd_deltype" value="0" />
                            <input type="hidden" name="name" id="hd_name" value="" />
                            <input type="hidden" name="proc" id="hd_delete" value="2" />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" id="btn_del">
                                <i class="icon-trash icon-white"></i>
                                Delete
                            </button>
                            <a href="" class="btn btn-primary" data-dismiss="modal">
                                <i class="icon-remove icon-white"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
            </span>
        </span>
        
        <span class="row" <?=$proc_style?>>
            <span class="span12">
                <div class="<?=$proc_class?>">
                    <a class="close" data-dismiss="alert">×</a>
                    <p><?=$proc_message?></p>
                </div>
            </span>
        </span>
        
        <span class="row">
            <span class="span12">
                <?php
                // print data
                if (count($array_dir)>0 || count($array_file)) {
                    print "<table class='table'>".$CR;
                    print "<thead>".$CR;
                    print "<tr>".$CR;
                    print "<th class='span7'>name</th>".$CR;
                    print "<th class='span2'>date</th>".$CR;
                    print "<th class='span2'>size</th>".$CR;
                    print "<th class='span1'>function</th>".$CR;
                    print "</tr>".$CR;
                    print "</thead>".$CR;
                    print "<tbody>".$CR;
                    
                    foreach ($array_dir as $item) {
                        print "<tr>".$CR;
                        print "<td><a href='?d=".$item[0]."'><i class='icon-folder-open'></i>&nbsp;".$item[1]."</a></td>";
                        print "<td>".$item[2]."</td>";
                        print "<td>".$item[3]."</td>";
                        print "<td>".
                            '<button class="btn pull-right trashbtn" name="'.$item[1].'" id="D'.$item[1].'">'.
                            "<i class='icon-trash pull-right'></i></button></td>";
                        print "</tr>".$CR;
                    }
                    
                    foreach ($array_file as $item) {
                        print "<tr>".$CR;
                        print "<td><a href='".$item[0]."/".$item[1]."'><i class='icon-file'></i>&nbsp;".$item[1]."</a></td>";
                        print "<td>".$item[2]."</td>";
                        print "<td>".$item[3]."</td>";
                        print "<td>".
                            '<button class="btn pull-right trashbtn" name="'.$item[1].'" id="F'.$item[1].'">'.
                            "<i class='icon-trash pull-right'></i></button></td>";
                        print "</tr>".$CR;
                    }
                    
                    print "</tbodt>".$CR;
                    print "</table>".$CR;
                } else {
                    $message = $MESSAGE_EMPTY;
                }
                // message style
                if ($message=="") {
                    $alert_style = "style='display:none'";
                } else {
                    $info_style  = "style='display:none'";
                }
                ?>
                <div class="alert" <?=$alert_style?>>
                    <p><?=$message?></p>
                </div>
                <div class="alert alert-info" <?=$info_style?>>
                    <h4 class="alert-heading"><?=$MESSAGE_INFO_TITLE?></h4>
                    <p><?=$MESSAGE_INFOMATION?></p>
                </div>
            </span>
        </span>
        
        <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript">
            $(function(){
                var datatype = "";
                $(".trashbtn").live("click", function(){
                    if ($(this).attr("id").slice(0, 1)=="D") {
                        datatype = "ディレクトリ ";
                        $("#hd_deltype").val("D");
                        $("#txt_warn").html("ディレクトリ内のすべてのディレクトリ、ファイルも削除されます。");
                    } else {
                        datatype = "ファイル ";
                        $("#hd_deltype").val("F");
                    }
                    $("#hd_name").val($(this).attr("name"));
                    $("#txt_del").html(datatype + $(this).attr("name") + "を削除します。よろしいですか？");
                    $('#delModal').modal('toggle')
                })
            })
        </script>
    </body>
</html>

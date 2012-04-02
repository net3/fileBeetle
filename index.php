<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>fileBeetle</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
    </head>
    <?php include 'conf.php'; ?>
    <?php
    $message        = "";
    $array_dir      = array();
    $array_file     = array();
    $array_data     = array();
    $dir            = $base_dir;
    $param_d        = "";
    $path           = "";
    $topic_path     = '<li><a href="?d=">'.$ROOTDIR_TEXT.'</a></li>';
    $alert_style    = "";
    $info_style     = "";
    
    $proc_message   = "";
    $proc_style     = "style='display:none'";
    $proc_class     = "";
    
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
                print '<ul class="breadcrumb">'.$cr;
                print $topic_path.$cr;
                print '</ul>'.$cr;
                // store data
                if ($handle = opendir($dir)) {
                    while ($entry = readdir($handle)) {
                        if (is_dir($dir."/".$entry)) {
                            // directory
                            if ($entry != "." && $entry != "..") {
                                array_push($array_dir,
                                        array(
                                            "<a href='?d=".$param_d."/".$entry."'><i class='icon-folder-open'></i>&nbsp;".$entry."</a>"
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
                                            "<a href='".$dir."/".$entry."'><i class='icon-file'></i>&nbsp;".$entry."</a>"
                                            ,date("Y-m-d H:i", $file_stat['mtime'])
                                            ,'<span class="pull-right">'.$file_size.'</span>'
                                        ));
                            }
                        }
                    }
                    $array_data = array_merge($array_dir, $array_file);
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
                                <i class="icon-ok icon-white"></i>
                                Create
                            </button>
                            <a href="" class="btn" data-dismiss="modal">
                                <i class="icon-remove"></i>Cancel
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
                if (count($array_data)>0) {
                    print "<table class='table'>".$cr;
                    print "<thead>".$cr;
                    print "<tr>".$cr;
                    print "<th class='span7'>name</th>".$cr;
                    print "<th class='span2'>date</th>".$cr;
                    print "<th class='span2'>size</th>".$cr;
                    print "<th class='span1'>function</th>".$cr;
                    print "</tr>".$cr;
                    print "</thead>".$cr;
                    print "<tbody>".$cr;
                    foreach ($array_data as $item) {
                        print "<tr>".$cr;
                        foreach ($item as $value) {
                            print "<td>";
                            print $value;
                            print "</td>".$cr;
                        }
                        print "<td><i class='icon-trash pull-right'></i></td>";
                        print "</tr>".$cr;
                    }
                    print "</tbodt>".$cr;
                    print "</table>".$cr;
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
    </body>
</html>

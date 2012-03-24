<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>fileBeetle</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <?php include 'conf.php'; ?>
    <?php
    $message        = "";
    $array_dir      = array();
    $array_file     = array();
    $array_data     = array();
    $dir            = $base_dir;
    $path           = "";
    $topic_path     = '<li><a href="?d=">'.$ROOTDIR_TEXT.'</a></li>';
    $alart_style    = "";
    $info_style     = "";
    ?>
    <body>
        <span class="row">
            <span class="span12">
                <h1>fileBeetle</h1>
                <br />
                <?php
                if (isset($_GET["d"])) {
                    $dir .= $_GET["d"];
                }
                // topic path
                $array_path = preg_split("/\//", $_GET["d"]);
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
                                            "<a href='?d=".$_GET["d"]."/".$entry."'><i class='icon-folder-open'></i>&nbsp;".$entry."</a>"
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
                <?php
                // print data
                if (count($array_data)>0) {
                    print "<table class='table'>".$cr;
                    print "<thead>".$cr;
                    print "<tr>".$cr;
                    print "<th class='span6'>name</th>".$cr;
                    print "<th class='span2'>date</th>".$cr;
                    print "<th class='span2'>size</th>".$cr;
                    print "<th class='span2'>function</th>".$cr;
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
                
                if ($message=="") {
                    $alart_style = "style='visibility:hidden'";
                } else {
                    $info_style  = "style='visibility:hidden'";
                }
                ?>
                <div class="alert" <?=$alart_style?>>
                    <p><?=$message?></p>
                </div>
                <div class="alert alert-info" <?=$info_style?>>
                    <h4 class="alert-heading"><?=$MESSAGE_INFO_TITLE?></h4>
                    <p><?=$MESSAGE_INFOMATION?></p>
                </div>
            </span>
        </span>
    </body>
</html>

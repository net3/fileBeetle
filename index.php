<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>fileBeetle</title>
    </head>
    <?php include 'conf.php'; ?>
    <?php
    $message    = "";
    $array_dir  = array();
    $array_file = array();
    $array_data = array();
    ?>
    <body>
        <h1>fileBeetle</h1>
        <?php
        // store data
        if ($handle = opendir($base_dir)) {
            while ($entry = readdir($handle)) {
                if (is_dir($base_dir."/".$entry)) {
                    // directory
                    if ($entry != "." && $entry != "..") {
                        array_push($array_dir,
                                array(
                                    $entry
                                    ,"-"
                                    ,"-"
                                ));
                    }
                } else {
                    // file
                    if ("." != substr($entry, 0, 1)) {
                        $file_stat = stat($base_dir."/".$entry);
                        array_push($array_file,
                                array(
                                    $entry
                                    ,date("Y-m-d H:i", $file_stat['mtime'])
                                    ,$file_stat['size']
                                ));
                    }
                }
            }
            $array_data = array_merge($array_dir, $array_file);
            closedir($handle);
        } else {
            $message = $MESSAGE_CANT_READ;
        }
        // print data
        if (count($array_data)>0) {
            print "<table border='1'>".$cr;
            print "<thead>".$cr;
            print "<tr>".$cr;
            print "<th>name</th>".$cr;
            print "<th>date</th>".$cr;
            print "<th>size</th>".$cr;
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
                print "</tr>".$cr;
            }
            print "</tbodt>".$cr;
            print "</table>".$cr;
        } else {
            $message = $MESSAGE_EMPTY;
        }
        ?>
        <p><?=$message?></p>
    </body>
</html>

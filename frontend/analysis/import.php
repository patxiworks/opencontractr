<?php
    //print_r($_FILES);
    if ( 0 < $_FILES['file']['error'] ) {
        
        echo 'error|Error: ' . $_FILES['file']['error'] . '<br>';
        
    } else {
        
        if ($_FILES['file']['type'] == 'application/json') {
            
            echo file_get_contents($_FILES['file']['tmp_name']);
        
        } elseif ($_FILES['file']['type'] == 'application/zip' ) {
            
            $zip = new ZipArchive();
            $filename = $_FILES['file']['tmp_name'];
            if ($zip->open($filename))
            $releases['releases'] = array();
            $first_release = json_decode(file_get_contents('zip://'.$filename.'#'.$zip->statIndex(0)['name']), true);
            foreach ($first_release['releases'] as $item) {
                array_push($releases['releases'], $item);
            }
            for ($i=1; $i < $zip->numFiles; $i++) {
                //echo $zip->statIndex($i)['name'];
                $release = json_decode(file_get_contents('zip://'.$filename.'#'.$zip->statIndex($i)['name']), true);
                foreach ($release['releases'] as $item) {
                    array_push($releases['releases'], $item);
                }
            }
            echo 'success|'.json_encode($releases);
            //print_r($releases);
        } else {
        
            echo 'error|Expecting JSON file. Got '.$_FILES['file']['type'] . ' instead';
        }
    }
?>

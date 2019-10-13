<?php
    if (count($_FILES) > 0) {

        if ( 0 < $_FILES['file']['error'] ) {
            
            echo 'error|||Error: ' . $_FILES['file']['error'] . '<br>';
            
        } else {
            
            if ($_FILES['file']['type'] == 'application/json') {
                
                echo 'success_new|||'.file_get_contents($_FILES['file']['tmp_name']);
            
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
                echo 'success_new|||'.json_encode($releases);
                
            } elseif ($_FILES['file']['type'] == 'text/csv' ) {
                
                $releases['releases'] = array();
                $file = $_FILES['file']['tmp_name'];
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $csvs = [];
                    /*while (($csvs[] = fgetcsv($handle)) !== FALSE) {
                        // Do Nothing
                    }*/
                    while(! feof($handle)) {
                        $csvs[] = fgetcsv($handle);
                    }
                    $data = [];
                    $column_names = [];
                    foreach ($csvs[0] as $single_csv) {
                        $column_names[] = $single_csv;
                    }
                    foreach ($csvs as $key => $csv) {
                        if ($key === 0) {
                            continue;
                        }
                        foreach ($column_names as $column_key => $column_name) {
                            if ($csv[$column_key] != null && $csv[$column_key] != '') {
                                $data[$key-1][$column_name] = $csv[$column_key];
                            }
                        }
                    }
                    //$json = json_encode($data);
                    fclose($handle);
                }
                foreach ($data as $key => $value) {
                    array_push($releases['releases'], unflatten($value));
                }
                //print_r($releases);
                echo 'success_existing|||'.json_encode($releases);
                
            } else {
            
                echo 'error|||Expecting a JSON or CSV file. Got '.$_FILES['file']['type'] . ' instead';
            }
        }

    } else {

        $url = $_POST['url'];
        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        if (curl_errno($ch)) {
            //throw new Exception('There was a problem connecting to the server. Please try again.');
            echo 'error|There was a problem retrieving the output. Please try again or try a different URL.';
        } else {
            // grab URL and pass it to the browser
            $output = curl_exec($ch);
            echo 'success_new|||'.$output;
        }

        // close cURL resource, and free up system resources
        curl_close($ch);

    }
    
function unflatten($array,$prefix = '')
{
    $result = array();
    foreach($array as $key=>$value)
    {
        if(!empty($prefix))
        {
            $key = preg_replace('#^'.preg_quote($prefix).'#','',$key);
        }
        if(strpos($key,'/') !== false)
        {
            parse_str('result['.str_replace('/','][',$key)."]=".$value);
        }
        else $result[$key] = $value;
    }
	
    return $result;
}
?>

<?php
    if (isset($_GET['ocds_data'])) {
        header("Content-type: application/json; charset=utf-8");
        
        if ($_GET['ocds_data'] == 'selected-fields') {
            echo stripslashes(get_option('_opencontractr_user_selected_fields'));
            
        } elseif ($_GET['ocds_data'] == 'field-scheme') {
            readfile(plugin_dir_url(__FILE__) . '../schema/fieldscheme.json');
            
        } elseif ($_GET['ocds_data'] == 'schema') {
            //$ch =  curl_init('http://standard.open-contracting.org/latest/en/release-schema.json');
            $ch =  curl_init('http://localhost:8888/release-schema.json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
                curl_setopt($ch, CURLOPT_TIMEOUT, 13);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
            $result = curl_exec($ch);
            echo $result;
        }
        exit;
    }
?>
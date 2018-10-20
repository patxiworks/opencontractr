<?php

function stripvar($string) {
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}

function multi_nested_values($array) {
    $output = array();
    foreach($array as $key => $value) {
        $output[$key] = nested_values($value);
    }
    return $output;
}

function nested_values($array, $path=""){
    $output = array();
    foreach($array as $key => $value) {
        if(is_array($value)) {
            $output = array_merge($output, nested_values($value, (!empty($path)) ? $path.$key."/" : $key."/"));
        }
        else $output[$path.$key] = $value;
    }
    return $output;
}

/**
 * Convert a multi-dimensional, associative array to CSV data
 * @param  array $data the array of data
 * @return string       CSV text
 * @src: https://coderwall.com/p/zvzwwa/array-to-comma-separated-string-in-php
 */
function str_putcsv($data) {
    # Generate CSV data from array
    $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
                                     # to use memory instead
    
    # write out the headers
    fputcsv($fh, array_keys($data));
    
    # write out the data
    fputcsv($fh, $data);
    
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);

    return $csv;
}

function multi_putcsv($data) {
    # Generate CSV data from array
    $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
                                     # to use memory instead
    
    $header = array();
    foreach ($data as $key=>$value) {
        $header = array_unique(array_merge($header, array_keys($value)));
    }
    # write out the headers
    fputcsv($fh, $header);
    
    foreach ($data as $key=>$value) {
        $row = array();
        foreach ($header as $k=>$v) {
            if (!empty($value[$v])) {
                $row[$v] = $value[$v];
            } else {
                $row[$v] = "";
            }
        }
        # write out the data
        fputcsv($fh, $row);
    }
    
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);

    return $csv;
        
}


// from https://stackoverflow.com/a/11518954/5757040

function flatten($array, $prefix = '') {
    $result = array();
    foreach($array as $key=>$value) {
        if(is_array($value)) {
            $result = $result + flatten($value, $prefix . $key . '.');
        }
        else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
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
        if(strpos($key,'.') !== false)
        {
            parse_str('result['.str_replace('.','][',$key)."]=".$value);
        }
        else $result[$key] = $value;
    }
	
    return $result;
}

?>
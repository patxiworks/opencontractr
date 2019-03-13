<?php
/*Template Name: OpenContractr Exporter
 */

switch ($_REQUEST['action']) {
    case 'download':
        $hasformat = isset($_REQUEST['format']);
        if ($hasformat && $_REQUEST['format'] == 'releases') {
            $json = get_releases( array($post) );
        } else {
            $json = get_current_record($post);
        }
        if ($_REQUEST['type']) {
            $type = ($_REQUEST['type']) ? $_REQUEST['type'] : 'txt';
            switch ($type) {
                case 'json':
                case 'raw':
                    $mime = 'application/json';
                    $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
                    break;
                case 'csv':
                    $mime = 'text/csv';
                    $flatjson = nested_values($json['records'][0]['compiledRelease']);
                    $data = str_putcsv($flatjson);
                    break;
                default:
                    $mime = 'text/plain';
                    $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
            }
        } else {
            $type = 'txt';
            $mime = 'text/plain';
            $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
        }
        $ocid = get_post_meta( $post->ID, 'ocid', true );
        $filename = $ocid ? $ocid : 'contract';
        
        if (!$_REQUEST['display'] && $_REQUEST['display'] != 'raw') {
            header('Content-Disposition: attachment; filename="'.$filename.($hasformat ? '-'.$_REQUEST['format'] : '').'.'.$type.'"');
        }
        header('Content-type: '.$mime);
        
    break;
    
    case 'downloadall':
        // get all releases
        $count = isset($_REQUEST['count']) ? $_REQUEST['count'] : -1;
        $posts = get_posts( array( 'numberposts' => $count, 'post_type'   => 'open_contract') );
        $json = get_releases($posts);
        if ($_REQUEST['type']) {
            $type = ($_REQUEST['type']) ? $_REQUEST['type'] : 'txt';
            switch ($type) {
                case 'json':
                case 'raw':
                    $mime = 'application/json';
                    $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
                    break;
                case 'csv':
                    $mime = 'text/csv';
                    $flatjson = multi_nested_values($json['releases']);
                    $data = multi_putcsv($flatjson);
                    break;
                default:
                    $mime = 'text/plain';
                    $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
            }
        } else {
            $type = 'txt';
            $mime = 'text/plain';
            $data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
        }
        
        if (!$_REQUEST['display'] && $_REQUEST['display'] != 'raw') {
            header('Content-Disposition: attachment; filename="releases.'.$type.'"');
        }
        header('Content-type: '.$mime);
        
    break;
    
    case 'validate':
        $url = get_permalink($post->ID) . '?action=download&type=json&display=raw';
        $ch =  curl_init('http://127.0.0.1:8000/validator/?raw=true&source_url='.$url);
        //$ch =  curl_init('http://localhost:8888/release-schema.json');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 13);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $data = curl_exec($ch);
        
    break;

    case 'search':
        $meta_query = array();
        $releaselist = array();
        $allreleases = array();
        foreach ($meta_search_fields as $metakey=>$value) {
            // add meta_query elements
            if( !empty( get_query_var( $metakey ) ) ){
                $meta_query[] = array( 'key' => $metakey, 'value' => get_query_var( $metakey ), 'compare' => 'LIKE' );
            }
        }
        if( count( $meta_query ) > 1 ){
            $meta_query['relation'] = 'OR';
        }
        $args = array('post_type' => 'open_contract', 'posts_per_page'=> 1000, 'meta_query'=>$meta_query);
        $loop = new WP_Query($args);
        if ( $loop->have_posts() ) : while ($loop->have_posts() ) : $loop->the_post();
            $compiledrelease = get_compiled_release($post);
            $compiledrelease['post-id'] = $post->ID;
            $compiledrelease['post-title'] = $post->post_title;
            $compiledrelease['post-url'] = get_permalink($post->ID);
            array_push($releaselist, $compiledrelease);
        endwhile; endif;

        $allreleases['releases'] = $releaselist;
        $data = json_encode($allreleases, true);
        
    break;
        
}

echo $data;
exit;
    
?>
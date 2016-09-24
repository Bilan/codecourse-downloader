<?php
define('COOKIE_PATH', 'cookies/cookie');

function jb_curl_init($page_url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $page_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_PATH);
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_PATH);
    
    return $ch;
}

function jb_get_page($page_url, $post_array = NULL)
{
    $ch = jb_curl_init($page_url);
    
    if($post_array)
    {
        $postdata = '';
        
        foreach ($post_array as $post_key => $post_val)
        {
            $postdata .= "$post_key=$post_val&";
        }
        
        $postdata = substr($postdata, 0, -1);
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
    
    ob_start();      // prevent any output
    $output = curl_exec($ch); 
    ob_end_clean();  // stop preventing output
    
    curl_close($ch); 
    
    return $output;
}

function jb_get_file($source_url, $path)
{
    //This is the file where we save the    information
    $fp = fopen ($path, 'w+');
    
    //Here is the file we are downloading, replace spaces with %20
    $ch = jb_curl_init(str_replace(" ","%20",$source_url));
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    
    // write curl response to file
    curl_setopt($ch, CURLOPT_FILE, $fp); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // get curl response
    curl_exec($ch); 
    curl_close($ch);
    fclose($fp);
}

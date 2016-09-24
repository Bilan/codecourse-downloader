<?php

function dd($dump)
{
    echo "<pre>";
    var_dump($dump);
    echo "</pre>";
    exit();
}

function debug($dump)
{
    if(DEBUG)
    {
        if(is_array($dump))
        {
            var_dump($dump);
        }
        else
        {
            echo $dump;
        }
    }
}

function extract_token($output)
{
    preg_match('/(?<=(name="_token" value=")).*(?=(">))/', $output, $matches);
    
    return $matches[0];
}

function extract_lessons_links($output)
{
    preg_match_all('/(https:\/\/www.codecourse.com\/library\/lessons\/.*)(?=(">))/', $output, $matches);
    
    return $matches[0];
}

function extract_lesson_slug($lesson_path)
{
    preg_match('/(?<=(lessons\/)).*/', $lesson_path, $matches);
    
    return $matches[0];
}

function create_dir_or_fail($dir_path)
{
    if( ! file_exists( $dir_path ))
    {
        if( mkdir($dir_path, 0777) )
        {
            debug("Dir $dir_path created... \n");
        }
        else
        {
            die('Unable to create dir '.$dir_path);
        }
        
        return true;
    }
    
    return false;
}

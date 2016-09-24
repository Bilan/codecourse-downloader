<?php
set_time_limit(0);
error_reporting(E_ALL);

include('functions.php');
include('curl.php');

define('DEBUG', 1);
define('SERIES_PATH', 'series');

/* CONFIG */
/*******************************/
$email = 'youremail@example.com';
$pass = 'yourpassword';
$library_pages = 24;
/*******************************/

$login_url = 'https://www.codecourse.com/auth/signin?';
$library_url = 'https://www.codecourse.com/library?page=';

/* step 1 - downloading page */

debug("Opening login page... \n");

$output = jb_get_page($login_url); 

/* step 2 - logging in if not logged in */

if( ! preg_match('/(Sign out)/', $output))
{
    debug("Not logged in \n");
    
    debug("Extracting token... \n");
    
    $token = extract_token($output);

    debug("Extracted token: $token \n");
    
    debug("Logging in... \n");
    
    $logged = jb_get_page($login_url, ['email' => $email, 'password' => $pass, 
                            '_token' => $token, 'remember' => 'on']); 
    
    dd($logged);
    debug("Logged in \n");
}

/* step 3 - downloading list of series */

$lessons = [];

for($page = 1; $page <= $library_pages; $page++)
{
    debug("Extracting lessons links on page $page... \n");
    
    $output = jb_get_page($library_url . $page);
    
    $lessons_part = extract_lessons_links($output);
    $lessons = array_merge($lessons, $lessons_part);
    
    debug($lessons_part);
}

/* step 4 - downloading series */

foreach($lessons as $lesson)
{
    $slug = extract_lesson_slug($lesson);
    
    debug("Extracting links from series $slug... \n");
    
    $series_data = file_get_contents("https://www.codecourse.com/api/lesson/".$slug);
    $series_data = json_decode($series_data);
    
    $series_path = SERIES_PATH . '/' .  $slug;
    
    create_dir_or_fail($series_path);
    
    
    $section_number = 1;
    
    /* downloading sections of series */
    
    foreach($series_data->data->sections->data as $series_section)
    {
        
        $section_path = $series_path . '/' . $section_number . ' - ' . 
                preg_replace('/[^a-zA-Z0-9\-\._]/','', 
                        preg_replace('/(\s)/', '-', $series_section->title));
        
        create_dir_or_fail($section_path);
        
        $lesson_number = 1;
        
        /* downloading lessons of sections of series */
        
        foreach($series_section->parts->data as $lesson)
        {
            $lesson_link = "https://www.codecourse.com/video/download/$lesson->id/hd";
            $lesson_path = $section_path . '/' 
                    . $lesson_number . ' - '. $lesson->slug . '.mp4';
            
            if( ! file_exists( $lesson_path ))
            {
                debug("Downloading lesson ".$lesson->slug." \n");
                
                jb_get_file($lesson_link, $lesson_path);
                
                debug("Done! \n");
            }
            else
            {
                debug("Skipping lesson ".$lesson->slug.". Already exists. \n");
            }
            
            $lesson_number++;
        }
        
        $section_number++;
        
        debug("Section ".$series_section->title . " done! \n");
    }
    
    debug("Series $slug done! \n");
}   

debug("Success! Downloaded all lessons! \n");


<?php
namespace Kaleyra\Route;

class Scrub
{
    public static $series;
      

    public static function index($cid, $number)
    {
        $folder = dirname(__FILE__).DIRECTORY_SEPARATOR.'country-file'.DIRECTORY_SEPARATOR;
        
        $name = "country-".$cid.".txt";
       
        $file = file_get_contents($folder.$name, true);

        if ($file != '') {
            $data = json_decode($file, true);
            
            $match = $data['match'];

            if ($data['srs'] && !empty($data['srs'])) {
                self::$series = $data['srs'];
                return self::findMatch($number, $match);
            }
        }

        return false;
    }

    public static function findMatch($number, $match)
    {
        if ($match == 0) {
            return false;
        }
        
        $pattern = substr($number, 0, $match);
        
        if (array_key_exists($pattern, self::$series)) {
            return self::$series[$pattern];
        } else {
            $match = $match-1;
            return self::findMatch($number, $match);
        }
    }
}

<?php

include 'config.php';




// Create connection
$conn = new mysqli($servername, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// create file for country
function createFile($conn)
{
    $folder = './country-file/';
    $countries = getCountry($conn);

    foreach ($countries as $code => $cid) {
        $name = "country-".$code.".txt";
        //$myfile = fopen($folder.$name, "w");

        $series = getSeries($conn, $cid);
       
        $data = [
                'cid' => $cid,
                'code' => $code,
        ];
       
        if (!empty($series)) {
            $match = strlen(max(array_keys($series)));
            $data['match'] = $match;
            $data['srs'] = $series;
        }
        
        fwrite($myfile, json_encode($data));
    }
}

createFile($conn);


// get all country with status 1
function getCountry($conn)
{
    $countries = [];
    $sql = "SELECT distinct `country_id`, `code` FROM ".$GLOBALS['table']['country']." WHERE status = 1 ";
  
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $countries[$row["code"]] = $row["country_id"];
        }
    }

    return $countries;
}


// get all network  with status 1
function getSeries($conn, $country)
{
    $series = [];
    $sql = "SELECT s.series, n.network_id, (SELECT GROUP_CONCAT(fk_channel_id SEPARATOR ',') FROM ".$GLOBALS['table']['channel_price']." WHERE fk_network_id = n.network_id) as cid FROM ".$GLOBALS['table']['networks']." as n join ".$GLOBALS['table']['network_series']." as s on (n.network_id = s.fk_network_id )   WHERE n.`fk_country_id` = $country order by n.network_id and n.status = 1";

    

    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        // output data of each row

        while ($row = $result->fetch_assoc()) {
            if (strpos($row['series'], '[') !== false) {
                $range = getBetween($row['series'], '[', ']');
               
                $number = range($range[0], $range[1]);
                $c = '['.$range[0].'-'. $range[1].']';
                foreach ($number as $r) {
                    $k = str_replace($c, $r, $row['series']);
                    $s[$k] = $k;
                }
            } elseif (strpos($row['series'], 'U') !== false) {
                $s = series('X');
            } else {
                $s = series($row['series']);
            }

            foreach ($s as $ser) {
                $series[$ser]['nid'] = $row['network_id'];
                $location_id = '';
                $sql = "SELECT `location_id` FROM ".$GLOBALS['table']['location']." WHERE `series`  = ".$ser." limit 1";
                $loc_result = $conn->query($sql);
                if ($loc_result->num_rows > 0) {
                    while ($loc = $loc_result->fetch_assoc()) {
                        $location_id = $loc['location_id'];
                    }
                }
                $series[$ser]['lid'] = $location_id;
            }
        }
    }

    //sort seires based on length in decreasing order
    uksort($series, function ($a, $b) {
        return strlen($b) <=> strlen($a);
    });
    
    return $series;
}


// used for range like [2-3]
// return array of range

function getBetween($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return explode('-', $r[0]);
    }
    return '';
}


// return series by converting x
function series($series)
{
    $html = $series;
    $needle = "X";
    $lastPos = 0;
    $positions = array();

    while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
        $positions[] = $lastPos;
        $lastPos    = $lastPos + strlen($needle);
    }

    // find no. of x in series

    if (!count($positions)) {
        $s[$series] = $series;
    } else {
        $last_element = end($positions);
    }
    

    if (strpos($series, 'X') !== false) {
        $s = str_replace_first($series);
       
        if (count($positions) > 1) {
            for ($i = 1; $i < count($positions); $i++) {
                $s = create_array($s);
            }
        }
    } else {
        $s[$series] = $series;
    }
    
    return $s;
}

// merge associative array in to single array
function create_array($s)
{
    $array2 = $s;
    $return_array = array();
    foreach ($array2 as $array1) {
        foreach ($array1 as $key => $value) {
            if (isset($return_array[$key])) {
                $return_array[$key].=',' . $value;
            } else {
                $return_array[$key] = $value;
            }
        }
    }
    return $return_array;
}

// replace first x in series
function str_replace_first($series)
{
    $from = '/'.preg_quote('X', '/').'/';

    for ($i=0; $i <= 9; $i++) {
        $v = preg_replace($from, $i, $series, 1);
        $s[$v] = $v;
        if (strpos($v, 'X') !== false) {
            $z = str_replace_first($v);
            $s[$v] = $z;
        }
    }
    return $s;
}



function printr($data)
{
    return '<pre>'.print_r($data).'</pre>';
}

<?php
    error_reporting(E_ALL | E_STRICT);
    header("Content-Type: text/html;charset=utf-8");
    libxml_use_internal_errors(true);
    ini_set("memory_limit","521M");
    $start_url = "http://db.sgamer.com/fifa/index/player/id/";
    $conn = mysql_connect("localhost", " ", " ");//创建sql连接
    if(! $conn )
    {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("sgamer");    
    function curl_get_contents($url,$timeout=5,$method='get',$post_fields=array(),$reRequest=0,$referer="") { //封装 curl
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false );
       curl_setopt($ch, CURLOPT_REFERER, $referer);
       if (strpos($method,'post')>-1) {
           curl_setopt($ch, CURLOPT_POST, true);
           curl_setopt($ch, CURLOPT_POSTFIELDS,$post_fields);
       }
       if (strpos($method,'WithHeader')>-1) {
           curl_setopt($ch, CURLOPT_HEADER, true);
           curl_setopt($ch, CURLOPT_NOBODY, false);
       }
       $output = curl_exec($ch);
       if (curl_errno($ch)==0) {
           if (strpos($method,'WithHeader')>-1) {
               $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
               $header = substr($output, 0, $headerSize);
               $body = substr($output, $headerSize);
               return array($header,$body,$output);
           } else {
               return $output;
           }
       } else {
           if ($reRequest) {
               $reRequest--;
               return curl_get_contents($url,$timeout,$method,$post_fields,$reRequest);
           } else {
               return false;
           }
       }
    }    
    function nodelist2string($nodelist) {//把xpath获得的nodelist全部输出为string，仅在只有一个元素的时候有效
        foreach($nodelist as $node) {
            $a_node = $node->nodeValue;
            if($a_node) {
                return $a_node;
            }
            else {
                return 0;
            }
        }
    }
    function dom_parser($html, $mypath) {//构建xpath并查找
        $doc = new DomDocument;
        $doc->loadHTML($html);
        $xpath = new DOMXpath($doc);
        $href = $xpath->query($mypath);
        return nodelist2string($href);
    }      
    for ($counter = 1; $counter < 500000; $counter ++) {
        $soccer_update = "INSERT INTO soccer (soccer_name, st, cf ,lw, rw, cam, cdm ,cm , rb, lb, lwb, rwb, cb, height, weight) VALUES ('%s', %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u);";
        $url = $start_url . (string)$counter;
        $html_source = curl_get_contents($url);  
        $soccer_name = dom_parser($html_source, "//div[@class='fifaonline3DbPlayerMain_inner cf']/dl[@class='playername']/dd");
        if (str_replace(" ","",$soccer_name) == ""){
            continue;
        }
        else {
            echo (string)$counter . " " . $soccer_name . " 获得！\n";
        }        
        $p_st = dom_parser($html_source, "//span[@class='p_st']");
        $p_cf = dom_parser($html_source, "//span[@class='p_cf']");
        $p_lw = dom_parser($html_source, "//span[@class='p_lw']");
        $p_rw = dom_parser($html_source, "//span[@class='p_rw']");
        $p_cam = dom_parser($html_source, "//span[@class='p_cam']");
        $p_cdm = dom_parser($html_source, "//span[@class='p_cdm']");
        $p_lwb = dom_parser($html_source, "//span[@class='p_lwb']");
        $p_rwb = dom_parser($html_source, "//span[@class='p_rwb']");
        $p_cb = dom_parser($html_source, "//span[@class='p_cb']");
        $p_cm = dom_parser($html_source, "//span[@class='p_cm']");
        $p_lb = dom_parser($html_source, "//span[@class='p_lb']");
        $p_rb = dom_parser($html_source, "//span[@class='p_rb']");
        $soccer_weight = dom_parser($html_source, "//dl[@class='height']/dd");
        $soccer_height = dom_parser($html_source, "//dl[@class='height']/dd");
        preg_match("/\d{3}/", $soccer_height,$match);
        preg_match("/[0-9]{2,3}\skg$/",$soccer_height,$matches);
        $soccer_weight = str_replace(" kg", "", $matches[0]);
        $soccer_height = $match[0];
        $soccer_update = sprintf($soccer_update, $soccer_name, $p_st, $p_cf, $p_lw, $p_rw, $p_cam, $p_cdm, $p_cm ,$p_rb, $p_lb, $p_lwb, $p_rwb, $p_cb, $soccer_height, $soccer_weight);
        mysql_unbuffered_query($soccer_update);
        unset($soccer_update, $soccer_name, $p_st, $p_cf, $p_lw, $p_rw, $p_cam, $p_cdm, $p_cm ,$p_rb, $p_lb, $p_lwb, $p_rwb, $p_cb, $soccer_height, $soccer_weight, $url, $html_source);
    }
?>

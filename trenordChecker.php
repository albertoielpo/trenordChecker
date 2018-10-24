<?php
/**
 * @author Alberto Ielpo
 * @version 0.3
 * Check link and send email
 */
$config = require 'config.php';
$i18n = require 'i18n.php';
/* const */
$url = $config['all_dir'];
$dirToCheck = $config['dir_to_check'];
$urls = $config['url_to_check'];

$send_mail = $config['send_mail'];
$br="<br/>";

/**
 * get page from url
 */
function getPage ($url, $useragent) {    
    $timeout= 120;
    $dir            = dirname(__FILE__);
    $cookie_file    = $dir . '/cookies/' . md5("127.0.0.1") . '.txt';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt($ch, CURLOPT_ENCODING, "" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_AUTOREFERER, true );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
    $content = curl_exec($ch);
    if(curl_errno($ch)){
        echo 'error:' . curl_error($ch);
    } else {
        return $content;        
    }
    curl_close($ch);
}
/** ***************************************************** */
/** main */
echo "Trenord checker v." . $config['version'] . $br;
echo "Start " . date(DATE_RFC2822) . $br;

/* mobile api */
$textMail = "mobile api \n\n";
$json = getPage($url, $config['ua']);
$parsed = json_decode($json, true);
foreach ($parsed as $par) {
    if(in_array($par['descrizione'], $dirToCheck)){        
        //var_dump($par);       //debug 
        $textMail .= "Direttrice: " . $i18n[$par['descrizione']] . "\n";
        foreach ($par['news'] as $news) {
            $datetime = $news['date'];
            $datetime = str_replace("T"," ",$datetime);
            $datetime = str_replace("Z","",$datetime);

            $textMail .= "Data: " . $datetime . "\n";
            $textMail .= "Severity: " . $news['severity_description'] . "\n";
            $textMail .=  $news['description'] . "\n\n";
        }
        $textMail.="=====================\n";
    }
}

/* web api */
$webMessage = "\n\n ===================== \n web api \n\n";
foreach ($urls as $key => $uri) {
    $page = getPage($uri, $config['ua']);
    $sb = substr($page, stripos($page, "Direttrice in tempo reale"), strlen($page));
    $s = strip_tags($sb);
    $a= substr($s, 0, stripos($s, "Chi siamo"));
    $a = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($a));
    $a = preg_replace('[&nbsp;]', ' ', $a);
    $tmp = preg_replace('[\n]', '', trim($a));
    $webMessage = $webMessage . "=====================================\n";
    $webMessage = $webMessage . $i18n[$key] . "\n". $tmp . "\n";
}
$textMail .= $webMessage;

if($send_mail){
    $to      = "alberto@ielpo.net";
    $subject = "Controllo direttrici Trenord";
    $message = $textMail;
    $headers = 
        "Bcc: alberto.ielpo@gmail.com". "\r\n" .    
        "From: services@ielpo.net" . "\r\n" .
        "Reply-To: services@ielpo.net" . "\r\n" .
        "X-Mailer: PHP/" . phpversion() . "\r\n" ;
    
    mail($to, $subject, $message, $headers);
}

echo $textMail;
echo "End " . date(DATE_RFC2822) . $br;
?>
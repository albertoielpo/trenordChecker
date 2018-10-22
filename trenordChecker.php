<?php
/**
 * @author Alberto Ielpo
 * @version 0.2
 * Check link and send email
 */
$config = require 'config.php';
$i18n = require 'i18n.php';
/* const */
$urls = $config['url_to_check'];
$send_mail = $config['send_mail'];
$br="<br/>";

/**
 * get page from url
 */
function getPage ($url) {
    $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
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
$final = "";
foreach ($urls as $key => $url) {
    $page = getPage($url);
    $sb = substr($page, stripos($page, "Direttrice in tempo reale"), strlen($page));
    $s = strip_tags($sb);
    $a= substr($s, 0, stripos($s, "Chi siamo"));
    $a = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($a));
    $a = preg_replace('[&nbsp;]', ' ', $a);
    $tmp = preg_replace('[\n]', '', trim($a));
    $final = $final . "=====================================\n";
    $final = $final . $i18n[$key] . "\n". $tmp . "\n";
}
if($send_mail){
    $to      = 'alberto.ielpo@gmail.com';
    $subject = 'Controllo direttrici Trenord';
    $message = $final;
    $headers = 'From: services@mondeando.com' . "\r\n" .
        'Reply-To: services@mondeando.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
}

echo $final;
echo "End " . date(DATE_RFC2822) . $br;
?>
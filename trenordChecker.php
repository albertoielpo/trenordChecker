<?php
/**
 * @author Alberto Ielpo
 * @version 0.2
 * Check link and send email
 */
$config = require 'config.php';
$i18n = require 'i18n.php';
/* const */
$url = $config['all_dir'];
$dirToCheck = $config['dir_to_check'];
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
$textMail = "";
$json = getPage($url);
$parsed = json_decode($json, true);
foreach ($parsed as $par) {
    if(in_array($par['descrizione'], $dirToCheck)){        
        //var_dump($par);       //debug 
        $textMail .= $i18n[$par['descrizione']] . "\n";
        foreach ($par['news'] as $news) {
            $textMail .= $news['date'] . "\n";
            $textMail .= $news['severity_description'] . "\n";
            $textMail .= $news['description'] . "\n";
        }
        $textMail.="\n";
    }
}

if($send_mail){
    $to      = 'alberto.ielpo@gmail.com';
    $subject = 'Controllo direttrici Trenord';
    $message = $textMail;
    $headers = 'From: services@ielpo.net' . "\r\n" .
        'Reply-To: services@ielpo.net' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
}

echo $textMail;
echo "End " . date(DATE_RFC2822) . $br;
?>
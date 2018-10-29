<?php
/**
 * @author Alberto Ielpo
 * @version 0.4
 * Check link and send email
 */
$config = require 'conf/config.php';
$i18n = require 'conf/i18n.php';

include "utils/mailer.php";
include "utils/wget.php";

/* const */
$url = $config['all_dir'];
$dirToCheck = $config['dir_to_check'];
$urls = $config['url_to_check'];
$send_mail = $config['send_mail'];
$api_web_enabled = $config['api_web_enabled'];
$api_mobile_enabled = $config['api_mobile_enabled'];
$br="<br/>";

$wget = new Wget();

/** ***************************************************** */
/** main */
echo "Trenord checker v." . $config['version'] . $br;
echo "Start " . date(DATE_RFC2822) . $br;

$textMail = "";

/* mobile api */
if($api_mobile_enabled){
    $textMail .= "mobile api \n\n";
    $json = $wget->download($url);
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
}

if($api_web_enabled){
    /* web api */
    $webMessage = "\n\n=====================\nweb api \n\n";
    foreach ($urls as $key => $uri) {
        $page = $wget->download($uri);
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
}

if($send_mail){
    $to      = "alberto.ielpo@gmail.com";
    $subject = "Controllo direttrici Trenord";
    $message = $textMail;
    $headers = 
        "From: services@ielpo.net" . "\r\n" .
        "Reply-To: services@ielpo.net" . "\r\n" .
        "X-Mailer: PHP/" . phpversion() . "\r\n" ;

    $mailer = new Mailer($to, $subject, $message, $headers);
    $mailer->send_mail();
}

echo $textMail;
echo "End " . date(DATE_RFC2822) . $br;
?>
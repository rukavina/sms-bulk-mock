<?php
namespace BulkMock;

/**
 * Description of Utils
 *
 * @author milan
 */
class Utils {

    public static function getNumberOfSMSsegments($text, $MaxSegments = 6) {
        $TotalSegment = 0;
        $textlen = mb_strlen($text);
        if ($textlen == 0){
            return false; //I can see most mobile devices will not allow you to send empty sms, with this check we make sure we don't allow empty SMS
        }

        if (self::isGsm7bit($text)) { //7-bit
            $SingleMax = 160;
            $ConcatMax = 153;
        } else { //UCS-2 Encoding (16-bit)
            $SingleMax = 70;
            $ConcatMax = 67;
        }

        if ($textlen <= $SingleMax) {
            $TotalSegment = 1;
        } else {
            $TotalSegment = ceil($textlen / $ConcatMax);
        }

        if ($TotalSegment > $MaxSegments){
            return false; //SMS is very big.
        }
        return $TotalSegment;
    }

    public static function isGsm7bit($text) {
        $gsm7bitChars = "\\\@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà^{}[~]|€";
        $textlen = mb_strlen($text);
        for ($i = 0; $i < $textlen; $i++) {
            if ((strpos($gsm7bitChars, $text[$i]) == false) && ($text[$i] != "\\")) {
                return false;
            } //strpos not     able to detect \ in string
        }
        return true;
    }
    
    public static function guid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }    

}

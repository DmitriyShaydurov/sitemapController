<?php
/**
 * Description: This class converts Cyrillic characters into Latin characters in accordance with ISO 9
 * Could be used for SEO URL creation
 * Author: Dmitriy Shaydurov <dmitriy.shaydurov@gmail.com>
 * Author URI: http://smb-studio.com
 * Version: 1.0
 * Date: 27.10.2017
 **/

namespace shaydurov\opencart;

 class CyrToLat
 {
     // is it necessary to add dashes? Example: Убить Била  - result: ubit-bila
     public $addDashes = true;

     // is it necessary to remove reserved characters?
     public $removeReserved = true;

     // is it necessary to remove points?
     public $removePoints = true;

     // is it necessary to use lower case letters?
     public $useLowerCase = true;

     protected $cyr = array(
         "Є", "І", "Ѓ", "і", "№", "є", "ѓ", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З",
         "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч",
         "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я", "а", "б", "в", "г", "д", "е", "ё", "ж",
         "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц",
         "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", "—", "«", "»", "…");

     protected $lat = array(
         "YE", "I", "G", "i", "#", "ye", "g", "A", "B", "V", "G", "D", "E", "YO", "ZH",
         "Z", "I", "J", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F", "X", "C",
         "CH", "SH", "SHH", "'", "Y", "", "E", "YU", "YA", "a", "b", "v", "g", "d", "e",
         "yo", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f",
         "x", "c", "ch", "sh", "shh", "", "y", "", "e", "yu", "ya", "-", "", "", "");

     //  Reserved Characters  in accordance with "Uniform Resource Identifier (URI): Generic Syntax"  p 2.2
     protected $genDelims = array(":", "/", "?", "#", "[", "]", "@", "%");

     protected $subDelims = array("!", "$", "&", "'", "(", ")",
         "*", "+", ",", ";", "=");

     public function convert($line)
     {
         $line = trim($line);
         $line = str_replace($this->cyr, $this->lat, $line);

         if ($this->removeReserved){
             $line = str_replace($this->genDelims, "", $line);
             $line = str_replace($this->subDelims, "", $line);
         }

         if ($this->removePoints){
             $line = str_replace('.', "", $line);
         }

         if ($this->addDashes) {
             $line = str_replace(' ', '-', $line);
         }
         if ($this->useLowerCase) {
             $line = strtolower($line);
         }
         $line = preg_replace('/\s+/', '', $line);
         //$line = preg_replace('/\s\s+/', '-', $line);
         $line = preg_replace('/\-{2,}/', '', $line);

         return $line;

     }
 }

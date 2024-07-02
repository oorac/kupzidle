<?php

    namespace App\Services;

    use HTMLPurifier;
    use HTMLPurifier_Config;

    class HtmlData
    {

        public static function getHtmlPurifierData($data)
        {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
            $config->set('Attr.EnableID', true);
            $config->set('HTML.Trusted', true);
            $config->set('AutoFormat.AutoParagraph', true);
            $def = $config->getHTMLDefinition(true);
            $def->addAttribute('h4', 'target', 'Enum#_blank,_self,_target,_top');
            $purifier = new HTMLPurifier($config);
            return $purifier->purify($data);
        }

    }
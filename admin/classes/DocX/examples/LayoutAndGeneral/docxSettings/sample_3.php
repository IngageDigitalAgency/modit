<?php

require_once '../../../classes/CreateDocx.php';

$docx = new CreateDocx();

$text = 'Change compatibilityMode and overrideTableStyleFontSizeAndJustification tags';
$docx->addText($text);

$settings = array(
    'compat' => array(
        'compatibilityMode' => array('val' => '15'),
        'overrideTableStyleFontSizeAndJustification' => array('val' => '1'),
    )
);
$docx->docxSettings($settings);

$docx->createDocx('example_docxSettings_3');
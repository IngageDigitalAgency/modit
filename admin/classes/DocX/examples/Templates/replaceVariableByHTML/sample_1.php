<?php

require_once '../../../classes/CreateDocx.php';

$docx = new CreateDocxFromTemplate('../../files/TemplateHTML.docx');

$docx->replaceVariableByHTML('ADDRESS', 'inline', '<p style="font-family: verdana, sans-serif; font-size: 11px">C/ Matías Turrión 24, Madrid 28043 <strong>Spain</strong></p>', array('isFile' => false, 'parseDivsAsPs' => true, 'downloadImages' => false));
$docx->replaceVariableByHTML('CHUNK_1', 'block', 'http://www.2mdc.com/PHPDOCX/example.html', array('isFile' => true, 'parseDivsAsPs' => true,  'filter' => '#capa_bg_bottom', 'downloadImages' => true));
$docx->replaceVariableByHTML('CHUNK_2', 'block', 'http://www.2mdc.com/PHPDOCX/example.html', array('isFile' => true, 'parseDivsAsPs' => false,  'filter' => '#lateral', 'downloadImages' => true));

$docx->createDocx('example_replaceTemplateVariableByHTML_1');
<?php

function simplest_xml_to_array($xmlstring) {
    return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
}

print_r(simplest_xml_to_array('<?xml version="1.0" encoding="utf-8"?><int xmlns="http://www.82009668.com/">2</int>'));



?>

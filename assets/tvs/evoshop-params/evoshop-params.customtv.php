<?php 
if (IN_MANAGER_MODE != 'true') {
    die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}

if (!isset($modx->loadedjscripts['es-params-css'])) {
 	echo '<link rel="stylesheet" type="text/css" href="/assets/tvs/evoshop-params/evoshop-params.css">';
 	$modx->loadedjscripts['es-params-css'] = ['src'=>'/assets/tvs/evoshop-params/evoshop-params.css'];
}

$arr = [];
list($optionType, $params) = explode(':',$field_value);
$arr['type']=$optionType;
if(count($params)>0) {
	$arrParams = explode('||', $params);
	foreach($arrParams as $param) {
		list($field, $value) = explode('==', $param);
		$arr['params'][$field] = $value;
	}
}

$out = '';
$out .= '<div class="es-param-wrap">';
$out .= '<div class="row" style="margin: 0 -15px;">';
$out .= '<div class="col col-sm-5"><select name="param-type">';
$out .= $arr['type']=='radio' ? '<option value="radio" selected>Radio</option>' : '<option value="radio">Radio</option>';
$out .= $arr['type']=='select' ? '<option value="select" selected>Select</option>' : '<option value="select">Select</option>';
$out .= $arr['type']=='checkbox' ? '<option value="checkbox" selected>Checkbox</option>' : '<option value="checkbox">Checkbox</option>';
$out .= '</select></div>';
$out .= '</div>';

$out .= '<div class="es-param-rows">';
foreach($arr['params'] as $name=>$price) {
	$out .= '<div class="row es-params" style="margin: 10px -15px 5px -15px;">';
	$out .= '<div class="col col-sm-5"><input type="text" name="es-param-name" placeholder="Значение" value="'.$name.'" /></div>';
	$out .= '<div class="col col-sm-5"><input type="text" name="es-param-price" placeholder="Цена" value="'.$price.'" /></div>';
	$out .= '<div class="btn-group"><button class="btn btn-danger btn-del"><i class="fa fa-trash"></i></button></div>';
	$out .= '</div>';
}
$out .= '</div>';
	$out .= '<hr/>';
	$out .= '<div class="row es-params" style="margin: 10px -15px 5px -15px;">';
	$out .= '<div class="col col-sm-5"><input type="text" name="es-param-name" placeholder="Значение" value="" /></div>';
	$out .= '<div class="col col-sm-5"><input type="text" name="es-param-price" placeholder="Цена" value="" /></div>';
	$out .= '<div class="btn-group"><button class="btn btn-success btn-add"><i class="fa fa-plus"></i></button></div>';
	$out .= '</div>';

$out .= '<input type="hidden" id="tv[+field_id+]" name="tv[+field_id+][]" value="[+field_value+]" onchange="documentDirty=true;"/>';
$out .= '</div>';


echo $out;

if (!isset($modx->loadedjscripts['es-params-js'])) {
 	echo '<script type="text/javascript" src="/assets/tvs/evoshop-params/evoshop-params.js"></script>';
 	$modx->loadedjscripts['es-params-js'] = ['src'=>'/assets/tvs/evoshop-params/evoshop-params.js'];
}
?>

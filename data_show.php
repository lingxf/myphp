<?php
include_once 'data_lib.php';
include_once 'disp_lib.php';
include_once 'common_js.php';

if(!isset($cust_divid))
	$cust_divid = 'div_data_list';
?>
<script type="text/javascript">
function recover_line(ele, line_id)
{
<?php
	print("\turl = '$action_script?';\n");
	foreach($all_fields as $one){
		if(!($one[3] & 0x100))
			continue;
		$key_name = $one[0];
		if($one[3] & 0x100)
			print("\turl += 'key_name=$key_name';\n");
	}
?>	
	url += "&action=recover&key="+line_id;
	load_url_reload(url, '', '');
}

function change_edit_status(ele, line_id)
{
	if(confirm("确实要归档？归档以后不能修改删除"))
	{
<?php
	print("\turl = '$action_script?';\n");
	foreach($all_fields as $one){
		if(!($one[3] & 0x100))
			continue;
		$key_name = $one[0];
		if($one[3] & 0x100)
			print("\turl += 'key_name=$key_name';\n");
	}
?>	
		url += "&action=archieve&key="+line_id;
		load_url_reload(url, '', '');
	}
	return false;
}

function del_line(ele, line_id)
{
	if(confirm("确实要删除？"))
	{
<?php
	print("\turl = '$action_script?';\n");
	foreach($all_fields as $one){
		if(!($one[3] & 0x100))
			continue;
		$key_name = $one[0];
		if($one[3] & 0x100)
			print("\turl += 'key_name=$key_name';\n");
	}
	global $enable_recycle;
	if($enable_recycle)
		print("\turl += '&action=recycle';\n");
	else
		print("\turl += '&action=delete';\n");

?>	
		url += "&key="+line_id;
		loadXMLDoc(url, function(){
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				rtext = xmlhttp.responseText;
				if(rtext.substr(0, 2) == 'ok'){
					line = ele.parentNode.parentNode.parentNode
					table = line.parentNode
					table.removeChild(line);
					document.getElementById("text_error").innerHTML=rtext.substr(2);
				}else
					alert(rtext);
			}else{
				if(xmlhttp.status=='0')
					document.getElementById("text_error").innerHTML="Please wait...";
				else
					document.getElementById("text_error").innerHTML=xmlhttp.status;
			}
		});
	}
	return false;
}

function copy_line(ele, line_id)
{
<?php
	print("\turl = '$action_script?';\n");
	foreach($all_fields as $one){
		if(!($one[3] & 0x100))
			continue;
		$key_name = $one[0];
		print("\turl += 'key_name=$key_name';\n");
	}
?>	
	url += "&action=copy&key="+line_id;
	load_url_reload(url, '', '');
}


function switch_page(dir)
{
<?php
	print("\turl = '$script?action=page&dir='+dir;");
	print("\tdiv_name = '$cust_divid';\n");
?>
	loadXMLDoc(url, function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			rtext = xmlhttp.responseText;
			document.getElementById(div_name).innerHTML=rtext;
		}else{
			if(xmlhttp.status=='0')
				document.getElementById(div_name).value="Please wait...";
			else
				document.getElementById(div_name).value=xmlhttp.status;
		}
	});
	return false;

}

function set_readonly(on)
{
	<?php
	foreach($all_fields as $one){
		$id_name = $one[0];
		if($one[1] != "")
			$id_name = $one[1];
		$width = $one[2];
		$attr = $one[3] & 0xff;
		if($attr == 2 && ($one[3] & 0x100)){
			print("\tif(on)\n");
			print("\t	document.getElementById('$id_name').setAttribute('readonly', 'true');\n");
			print("\telse\n");
			print("\t	document.getElementById('$id_name').removeAttribute('readonly');\n");
		}
	}
	?>
}

function show_asearch()
{
	clear_edit();
	set_readonly(false);
	document.getElementById('text_error').innerHTML = "";
	document.getElementById('div_edit').removeAttribute('hidden');
	document.getElementById('data_button_search').removeAttribute('hidden');
	document.getElementById('data_button_save').setAttribute('hidden', 'true');
	document.getElementById('data_button_add').setAttribute('hidden', 'true');
	window.location.href= "#div_edit";
}

function qsearch_data(op)
{
	if(op == 0)
		document.getElementById('text_search').value = '';
	text = document.getElementById('text_search').value;
<?php
	print("\turl = '$script?action=page&reset=yes&start=0&dir=qsearch&search_cond=&text='+text;\n");
	print("\tdiv_name = '$cust_divid';\n");
?>
	loadXMLDoc(url, function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			rtext = xmlhttp.responseText;
			document.getElementById(div_name).innerHTML=rtext;
		}else{
			if(xmlhttp.status=='0')
				document.getElementById(div_name).value="Please wait...";
			else
				document.getElementById(div_name).value=xmlhttp.status;
		}
	});
	return false;

}

function clear_edit()
{
<?php
	foreach($all_fields as $one){
		$id_name = $one[0];
		if($one[1] != "")
			$id_name = $one[1];
		$width = $one[2];
		$attr = $one[3] & 0xff;
		if($width < 0 ){
			if($attr == 1 && ($one[3] & 0x100))
				print("\tdocument.getElementById('$id_name').value = '';\n");
			continue;
		}
		if($attr == 5 )
			continue;
		if($attr == 2 )
			print("\tdocument.getElementById('$id_name').removeAttribute('readonly');\n");
		if($attr > 1 ){
			print("\tdocument.getElementById('$id_name').value = '';\n");
		}
	}
?>
}


function add_data()
{
	document.getElementById('div_edit').removeAttribute('hidden');
	document.getElementById('text_error').innerHTML = "";
	document.getElementById('data_button_add').removeAttribute('hidden');
	document.getElementById('data_button_save').setAttribute('hidden', 'true');
	clear_edit();
	set_readonly(true);
	window.location.href= "#div_edit";
}

function edit_line(ele, key)
{

	set_readonly(true);
	tr = ele.parentNode.parentNode.parentNode;
	tds = tr.childNodes;
	col = new Array();
	for(i = 0; i < tds.length; i++){
		td = tds[i].firstChild;
		p = td.childNodes;
		if(p.length>0)
			txt = p[0].innerHTML;
		else
			txt = td.innerHTML
		if(!txt)
			txt = td.innerHTML
		a =  from_html(txt);
		b  = a.replace(/\b(0+)/gi, '');
		if(b == '' && a != '')
			col[i] = 0;
		else
			col[i] = b;
	}
	document.getElementById('div_edit').removeAttribute('hidden');
	document.getElementById('text_error').setAttribute('hidden', 'true');
	document.getElementById('data_button_save').removeAttribute('hidden');
	window.location.href= "#div_edit";
<?php
	$i = 0;
	foreach($all_fields as $one){
		$id_name = $one[0];
		if($one[1] != "")
			$id_name = $one[1];
		$width = $one[2];
		$attr = $one[3] & 0xff;
		if($width < 0 ){
			if($attr == 1 && ($one[3] & 0x100))
				print("\tdocument.getElementById('$id_name').value = key;\n");
			continue;
		}
		if($attr == 5 ){
			eval("global $one[5];");
			$var = '$check_list';
			eval("$var = $one[5];");
			print("\tvalue = tds[$i].firstChild.firstChild.id;\n");
			foreach($check_list as $field=>$bit){
				print("\tif(value & $bit)\n");
				print("\t	document.getElementById('$field').setAttribute('checked', true);\n");
				print("\telse\n");
				print("\t	document.getElementById('$field').removeAttribute('checked');\n");
			}

		}else if($attr > 1 ){
			print("\tdocument.getElementById('$id_name').value = col[$i];\n");
		}
		$i += 1;
	}
	if($permit & 2)
		print("\tdocument.getElementById('button_save').removeAttribute('disabled');\n");
	else
		print("\tdocument.getElementById('button_save').setAttribute('disabled', 'true');\n");

?>
}

function save_data(action)
{
<?php
	print("\turl = '$action_script?action='+action;\n");
	
	foreach($all_fields as $one){
		$id_name = $one[0];
		$key_name = $id_name;
		if($one[1] != '')
			$id_name = $one[1];
		$attr = $one[3] & 0xff;
		if($attr == 0)
			continue;
		if($attr == 5 ){
			eval("global $one[5];");
			$var = '$check_list';
			eval("$var = $one[5];");
			print("\tvalue = 0;\n");
			foreach($check_list as $field=>$bit){
				print("\tif(document.getElementById('$field').checked)\n");
				print("\t	value += $bit;\n");
			}

		}else{
			print("\tvalue = document.getElementById('$id_name').value;\n");
		}
		print("\tvalue = encodeURI(value);\n");
		if($one[3] & 0x100)
			print("\turl += '&key='+value+'&key_name=$key_name';\n");

		if(($one[3] & 0x100)){
			print("\tif(action != 'add')\n");
		}else if($attr == 1){
			print("\tif(action != 'search')\n");
		}else if($attr == 2)
			print("\tif(action != 'save')\n");
		print("\turl += '&$id_name='+value\n");
	}
	print("\tdiv_name = '$cust_divid';\n");
?>
	if(action == 'search') 
		url += '&dir=search&start=0';
		url = encodeURI(url);
	loadXMLDoc(url, function(){
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			rtext = xmlhttp.responseText;
			status = rtext.substr(0, 2);
			if(action == 'search')
				document.getElementById(div_name).innerHTML=rtext;
			else if(status != 'ok'){
				alert(rtext);
			}else{
				if(action == 'save'){
					document.getElementById("text_error").innerHTML="保存成功";
				}
				document.getElementById(div_name).innerHTML=rtext.substr(2);
				document.getElementById('div_edit').setAttribute('hidden', 'true');
				document.getElementById('text_error').removeAttribute('hidden');
			}
		}else{
			if(xmlhttp.status=='0')
				document.getElementById("text_error").innerHTML="Please wait..";
			else
				document.getElementById("text_error").innerHTML=xmlhttp.status;
		}
	});
	return false;
}

function export_data()
{
<?php
	print("\turl = '$action_script?action=export&export_check=1&doc='\n");
	print("\tnew_url = '$action_script?action=export&doc='\n");
?>
	load_url_reload(url, new_url, '');
}

function switch_recycle()
{
<?php
	$recycle = get_persist_var('recycle', false);
	$recycle = !$recycle;
	print("\turl = '$action_script?action=list&recycle=$recycle'\n");
?>
	window.location.href = url;
}


function change_perpage(page, view){
<?php
	print("\turl = '$script';\n");
?>
	url = url + "?action=list&perpage="+page;
	window.location.href = url;
	return;
};

</script>
<?php

if($action == 'list'){

	if(!isset($search_bar) || $search_bar == 'yes'){
		if(!isset($cust_add_button) || $cust_add_button == false)
			print("<input id='button_add_show' name='add' type='button' onclick='return add_data();' value='$word_add'></input>");
		print("<input id='text_search'  type='text' onchange='qsearch_data(1)' value=''></input>");
		print("<input id='button_search'  type='button' onclick='qsearch_data(1);' value='$word_search'></input>");
		print("<input id='button_asearch'  type='button' onclick='show_asearch();' value='$word_asearch'></input>");
		print("<input id='button_search'  type='button' onclick='qsearch_data(0);' value='$word_reset'></input>");
		print("<input id='button_export'  type='button' onclick='export_data();' value='$word_export'></input>");
	}

	print("&nbsp;每页");
	print("<select id='sel_class' onchange='change_perpage(this.value, 0)'>");
	$perpage = get_persist_var('perpage', $def_perpage);
	$order_list = array(10=>"10",20=>"20", 40=>"40", 60=>"60", 80=>"80",  100=>"100");
	foreach($order_list as $key => $text) {
		print("<option value='$key'");
		if($key == $perpage) print("selected");
		print(">$text</option>");
	}
	print("</select>");
	for($i = 1;$i < 10;$i++)
		print("&nbsp;");
	reset_status();
	$_SESSION['recycle'] = '';
	if(isset($enable_go_recycle) && $enable_go_recycle){
		$recycle = get_persist_var('recycle', false);
		if($recycle)
			print("<input id='button_recycle'  type='button' onclick='switch_recycle();' value='$word_out_recycle'></input>");
		else
			print("<input id='button_recycle'  type='button' onclick='switch_recycle();' value='$word_go_recycle'></input>");
	}
	print("<div id='div_data_list'>");
	list_data();
	print("</div>");

	print("<br>");
	print("<div hidden id='div_edit'>");
	print("<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:$edit_width.0pt;margin-left:20.5pt;border-collapse:collapse'> <tbody>");
	$i = 0;
	foreach($all_fields as $one){
		$type = ($one[3] & 0xff);
		if($type == 0){
			$i += 1;
			continue;
		}

		if(isset($one[4]))
			$default = $one[4];
		else
			$default = '';
		$field = $one[0];
		if($one[1] != '')
			$field = $one[1];
		$type_text = 'input';
		$readonly = '';

		if($type == 1){
			$type_text == 'hidden';
			print("<input type='hidden' id='$field' value='$default'>");
			$i += 1;
			continue;
		}else if($type == 2){
			$type_text == 'input';
			$readonly = 'readonly';
		}else if($type == 4)
			$type_text == 'textarea';
		else if($type == 5){
			eval("global $one[5];");
			$var = '$check_list';
			eval("$var = $one[5];");
			$type_text = 'checkbox';
			foreach($check_list as $fn=>$value){
				print("<tr><th width='100' align='left'>$fn:</th><td><input id='$fn' $readonly type='$type_text' value='$default'></td></tr>");
			}
			continue;
		}
		print("<tr><th width='100' align='left'>$field:</th><td><input id='$field' $readonly type='$type_text' value='$default'></td></tr>");
		$i += 1;
	}

	print("</tbody></table>");
	print("&nbsp;");
	print("<input id='data_button_add' hidden name='save' onclick='save_data(\"add\");' type='button' value='$word_add'></input>");
	print("<input id='data_button_save' name='save' onclick='save_data(\"save\");' type='button' value='$word_save'></input>");
	print("<input id='data_button_search' name='search' onclick='save_data(\"search\");' type='button' value='$word_search'></input>");
	print("</div>");
	print("<div id='text_error'></div>");
	print("</html>");
}
?>


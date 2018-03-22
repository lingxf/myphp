function switch_page_simple(name, dir)
{
	url = "action_stub.php?table_name="+name+"&action=page&dir="+dir;
	div_name = "div_"+name;
	load_url_nok(url, div_name);
	return false;
}


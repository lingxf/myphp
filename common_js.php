<script type="text/javascript" >
/*
default show return html in div

0 - do nothing
1 - reload
2 - new url
3 - new url delay

message != '' alert message
div != '' show return html
alert return html if not non

0x100 - no alert when err
*/

function load_url_nok(url, div, message, div_err)
{
	if(typeof message === 'undefined') message = '';
	if(typeof div_err === 'undefined') div_err = '';
	_load_url(url, 0, div, message, '', div_err, false, true);
}

function load_url_value(url, id)
{
	_load_url(url, 0, id, '', '', '', true, true);
}

function load_url(url, div, message, div_err)
{
	if(typeof message === 'undefined') message = '';
	if(typeof div_err === 'undefined') div_err = '';
	_load_url(url, 0, div, message, '', div_err, false, false, false, '');
}

function load_url_nok_callback(url, div, callback)
{
	_load_url(url, 0, div, '', '', '', false, true, callback);
}

function load_url_callback(url, div, callback)
{
	_load_url(url, 0, div, '', '', '', false, false, callback);
}


function load_url_reload(url, new_url, message)
{
	if(typeof new_url === 'undefined') new_url = '';
	if(typeof message === 'undefined') message = '';
	_load_url(url, 1, '', message, new_url, '');
}

function _load_url(url, behaviour, div, message, new_url, div_err, byid, nook, callback)
{
	if(typeof byid === 'undefined') byid = false;
	if(typeof nook === 'undefined') nook = false;
	if(typeof callback === 'undefined') callback = '';
	loadXMLDoc(url, function() {
	         if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			 	text = xmlhttp.responseText;
				suc_bh = behaviour & 0xff;
				err_bh = behaviour & 0xff00;
				ok = text.substr(0, 2);
				if(ok == 'ok' || nook){
					if(!nook)
	            		rtext = text.substr(2, text.length - 2);
					else
	            		rtext = text;
					if(message != '')
	            		alert(message);

					if(div != ''){
						if(!byid)
							document.getElementById(div).innerHTML = rtext;
						else
							document.getElementById(div).value = rtext;
					}
					else if(rtext != '')
						alert(rtext);

					if(suc_bh == 1){
						if(err_bh & 0x200)
							setTimeout("window.location.href =" + new_url, 3);
						else if(new_url != '')
							window.location.href = new_url;
						else
							window.location.reload();
					}
					if(callback != '')
						callback();
				}else{
					if(!(err_bh & 0x100))
						alert(text);
				}
			}else{
				if(div_err != ''){
					if(xmlhttp.status=='0')
						document.getElementById(div_err).innerHTML="请等待...";
					else if(xmlhttp.status != '200')
						document.getElementById(div_err).innerHTML=xmlhttp.status + ' ' + xmlhttp.responseText;
				}
			}
	});
}

function ajaxFunction()
{
	alert('ajax');
	var xmlHttp;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				alert("您的浏览器不支持AJAX！");
				return false;
			}
			return xmlHttp;
		}
		return xmlHttp;
	}
	return xmlHttp;
}

function loadXMLDoc(url,cfunc)
{
	//xmlhttp = ajaxFunction();
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=cfunc;
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}

function from_html(htmlstr){
	htmlstr = htmlstr.replace("&lt;", "<", "gm");		
	htmlstr = htmlstr.replace("&gt;", ">", "gm");				
	htmlstr = htmlstr.replace("&amp;", "&", "gm");						
	htmlstr = htmlstr.replace("&nbsp;", " ", "gm");	
	htmlstr = htmlstr.replace("<br>", "\n", "gm");
	return htmlstr;
}

function to_html(htmlstr){
	htmlstr = htmlstr.replace("<", "&lt;", "gm");		
	htmlstr = htmlstr.replace(">", "&gt;", "gm");				
	htmlstr = htmlstr.replace("&", "&amp;", "gm");						
	htmlstr = htmlstr.replace(" ", "&nbsp;", "gm");	
	htmlstr = htmlstr.replace("\n", "<br>", "gm");
	return htmlstr;
}
</script>

//函数作用：如果结果为 null  那么显示为空字符串
function delnull(jsonobj) {
	$.each(jsonobj,function(k,v) {
		if(v==null) jsonobj[k]='';
	});
	return jsonobj;
}

function getWindow(uname,html) {
	layer.open({
		type: 1,
		title :'修改 <b style="color:red;">'+uname+'</b> 信息',
		area: ['600px', '360px'],
		//shadeClose: true, //点击遮罩关闭
		content: html
	});
}


function completeWindow(uname,adminid,json_data) {
	var html = '\
<span class="fspan"><table id="window" class="table_data" style="margin-left:100px;">\
	<tr adminid="'+json_data.adminid+'">\
<input type="hidden" name="adminid" value="'+json_data.adminid+'" />\
		<td>用户名：</td>\
		<td>\
			<input disabled type="text" name="username" value="'+json_data.adminname+'" />\
		</td>\
	</tr>\
\
<tr>\
<td>修改密码[输入新密码]：</td>\
<td>\
<input type="password" name="password" value="" />\
<b style="margin-left:10px;">留空代表不修改</b>\
</td>\
</tr>\
<tr>\
<td>确认新密码：</td>\
<td>\
<input type="password" name="cpassword" value="" />\
<b style="margin-left:10px;">留空代表不修改</b>\
</td>\
</tr>\
	<tr>\
		<td>真实姓名：</td>\
		<td>\
			<input type="text" name="realname" value="'+json_data.realname+'" />\
		</td>\
	</tr>\
\
	<tr>\
		<td>所属职位：</td>\
		<td>\
			<input name="position" value="'+json_data.position+'" />\
		</td>\
	</tr>\
\
	<tr>\
		<td>所属角色：</td>\
		<td>';
if(json_data.adminid!=1) {
	html += '<select name="role">';
	$.each(json_data.roleList,function(k,v) {
		html += '<option value="'+v.roleid+'" ';
		if(v.roleid==json_data.roleid) html += 'selected ';
		html += '>'+v.remark+'</option>';
	});
} else {
	html += '<b style="color:red;">超级管理员</b>';
}
html += '\
			</select>\
		</td>\
	</tr>\
\
	<tr>\
		<td>联系号码：</td>\
		<td>\
			<input name="contactno" value="'+json_data.contactno+'" />\
		</td>\
	</tr>\
\
	<tr>\
		<td>联系地址：</td>\
		<td>\
			<input name="address" value="'+json_data.address+'" />\
		</td>\
	</tr>\
</table>\
<input adminid="'+adminid+'" class="button button_" style="margin-left:100px;margin-top:20px;" type="button" value="提 交" /></span>';
	getWindow(uname,html,json_data);
}



function ajaxPageHtml($json_data,$num) {
	var html = '';
	var $i = $num-1;
	$.each($json_data , function (k,v) {
		delnull(v);
		$i++;
		html += '\
		<tr adminid="'+v.adminid+'">\
			<td align="center">'+$i+'</td>\
			<td class="adminname" align="center">'+v.adminname+'</td>\
			<td class="realname" align="center">'+v.realname+'</td>\
			<td class="rolename" align="center">';
		if(!v.rolename)
			html += '<b style="color:red;">超级管理员</b>';
		else
			html += v.rolename;
		html += '</td>\
		<td class="position" align="center">'+v.position+'</td>\
		<td class="contactno" align="center">'+v.contactno+'</td>\
		<td class="address" align="center">'+v.address+'</td>\
		<td class="lastlogintime" align="center">';
		if(!v.lastlogintime)
			html += '<b style="color:green;">暂无登录记录</b>';
		else
			html += v.lastlogintime;
		html += '</td>\
		<td class="lastloginip" align="center">';
		
		if(!v.lastlogintime)
			html += '<b style="color:green;">暂无登录记录</b>';
		else
			html += v.lastloginip;
		html += '</td>\
		<td class="createtime" align="center">'+v.createtime+'</td>\
		<td align="center">\
		<a uname="'+v.realname+'" adminid="'+v.adminid+'" class="a_edit" href="javascript:;" style="margin-right:15px;">修改</a>';
		if(v.adminid!=1)
			html += '<a uname="'+v.realname+'" adminid="'+v.adminid+'" class="a_delete" href="javascript:;">删除</a>';
		html += '</td></tr>';
	});
	return html;
}


//函数作用：点击 "首页" 或 "末页" 的时候
//type 1:首页  2:末页
function clickIndex(ajaxUrl,$data,type) {
	$.ajax({
		'url' : ajaxUrl,
		'type' : 'POST',
		dataType : 'json',
		'data' : $data,
		beforeSend : function() {
			loading = layer.load(3);
		},
		complete : function () {
			layer.close(loading);
		},
		success : function (a) {
			if(a.status=='error') {
				layer.close(loading);
				layer.alert(a.message, {icon: 2,title:'提示信息'});
				return;
			}
			if(type==1) {
				$('#lipage4 a').attr('pageno',1);
				$('#lipage5 a').attr('pageno',1);
				page = 1;
			} else if(type==2) {
				$('#lipage4 a').attr('pageno',a.totalpage);
				$('#lipage5 a').attr('pageno',a.totalpage);
				page = a.totalpage;
			}
			if(page==1) {
				$('#lipage2 , #lipage4').hide();
				$('#lipage7 , #lipage5').show();
				$('#lipage3').css({'margin-left':'240px'});
			} else if(page==a.totalpage) {
				$('#lipage7 , #lipage5').hide();
				$('#lipage2 , #lipage4').show();
				$('#lipage3').css({'margin-left':'0px'});
			} else {
				$('#lipage7 , #lipage5').show();
				$('#lipage2 , #lipage4').show();
				$('#lipage3').css({'margin-left':'0px'});
			}
			if(a.totalpage==1) {
				$('#lipage2 , #lipage4').hide();
				$('#lipage7 , #lipage5').hide();
			}
			$('#font_pageno').html(a.totalpage);
			
			$('.table_data tbody').html(ajaxPageHtml(a.dataList,a.num));
			var html = '';
			for(i=1;i<=a.totalpage;i++) {
				html += '<option value="'+i+'" ';
				if(i==a.pageno) html += 'selected ';
				html += '>第 '+i+' 页</option>';
			}
			$('#lipage6 select').html(html);
		}
	});
}




/*
type
1 : 上一页  prev
2 : 下一页  next
*/
function ajaxPage(ajaxUrl,obj,type,isselect) {
	var $data = {
		'action' : 'page',
		'pageno' : parseInt(obj.val())
	};
	if(isselect) {
		//if(type==1) $data.pageno = parseInt(obj.val())-1;
		//else if(type==2) $data.pageno = parseInt(obj.val())+1;
		$.ajax({
			'url' : ajaxUrl,   //"{:U('RBAC/managerList')}"
			'type' : 'POST',
			dataType : 'json',
			'data' : $data,
			beforeSend : function() {
				loading = layer.load(3);
			},
			complete : function () {
				layer.close(loading);
			},
			success : function (a) {
				if(a.status=='error') {
					layer.close(loading);
					layer.alert(a.message, {icon: 2,title:'提示信息'});
					return;
				}
				var pageno = parseInt(obj.attr('pageno'));
				
				$('#lipage4 a').attr('pageno',obj.val());
				$('#lipage5 a').attr('pageno',obj.val());
				page = obj.val();
				//alert(a.totalpageno);
				if(page==1) {
					$('#lipage2 , #lipage4').hide();
					$('#lipage7 , #lipage5').show();
					$('#lipage3').css({'margin-left':'240px'});
				} else if(page==a.totalpage) {
					$('#lipage7 , #lipage5').hide();
					$('#lipage2 , #lipage4').show();
					$('#lipage3').css({'margin-left':'20px'});
				} else {
					$('#lipage7 , #lipage5').show();
					$('#lipage2 , #lipage4').show();
					$('#lipage3').css({'margin-left':'0px'});
				}
				if(a.totalpage==1) {
					$('#lipage2 , #lipage4').hide();
					$('#lipage7 , #lipage5').hide();
				}
				$('#font_pageno').html(a.totalpage);
				$('.table_data tbody').html(ajaxPageHtml(a.dataList,a.num));
				var html = '';
				//alert(a.pageno);  a.totalpage
				for(i=1;i<=a.totalpage;i++) {
					html += '<option value="'+i+'" ';
					if(i==a.pageno) html += 'selected ';
					html += '>第 '+i+' 页</option>';
				}
				obj.attr('pageno',obj.val());
				$('#lipage6 select').html(html);
			}
		});
		return;
	}
	if(type==1) $data.pageno = parseInt(obj.attr('pageno'))-1;
	else if(type==2) $data.pageno = parseInt(obj.attr('pageno'))+1;
	$.ajax({
		'url' : ajaxUrl,
		'type' : 'POST',
		dataType : 'json',
		'data' : $data,
		beforeSend : function() {
			loading = layer.load(3);
		},
		complete : function () {
			layer.close(loading);
		},
		success : function (a) {
			if(a.status=='error') {
				layer.close(loading);
				layer.alert(a.message, {icon: 2,title:'提示信息'});
				return;
			}
			var pageno = parseInt(obj.attr('pageno'));
			if(type==1) {
				$('#lipage4 a').attr('pageno',pageno-1);
				$('#lipage5 a').attr('pageno',pageno-1);
				page = pageno-1;
			} else if(type==2) {
				$('#lipage4 a').attr('pageno',pageno+1);
				$('#lipage5 a').attr('pageno',pageno+1);
				page = pageno+1;
			}
			if(page==1) {
				$('#lipage2 , #lipage4').hide();
				$('#lipage7 , #lipage5').show();
				$('#lipage3').css({'margin-left':'240px'});
			} else if(page==a.totalpage) {
				$('#lipage7 , #lipage5').hide();
				$('#lipage2 , #lipage4').show();
				$('#lipage3').css({'margin-left':'0px'});
			} else {
				$('#lipage7 , #lipage5').show();
				$('#lipage2 , #lipage4').show();
				$('#lipage3').css({'margin-left':'0px'});
			}
			if(a.totalpage==1) {
				$('#lipage2 , #lipage4').hide();
				$('#lipage7 , #lipage5').hide();
			}
			$('#font_pageno').html(a.totalpage);
			//alert(a.num);
			$('.table_data tbody').html(ajaxPageHtml(a.dataList,a.num));
			var html = '';
			//alert(a.pageno);  a.totalpage
			for(i=1;i<=a.totalpage;i++) {
				html += '<option value="'+i+'" ';
				if(i==a.pageno) html += 'selected ';
				html += '>第 '+i+' 页</option>';
			}
			$('#lipage6 select').html(html);
		}
	});
}

//函数作用：删除完成后，重新加载页面
function afterDel(a) {
	$('#lipage4 a').attr('pageno',a.pageno);
	$('#lipage5 a').attr('pageno',a.pageno);
	page = parseInt(a.pageno);
	if(page==1) {
		$('#lipage2 , #lipage4').hide();
		$('#lipage7 , #lipage5').show();
		$('#lipage3').css({'margin-left':'240px'});
	} else if(page==a.totalpage) {
		$('#lipage7 , #lipage5').hide();
		$('#lipage2 , #lipage4').show();
		$('#lipage3').css({'margin-left':'0px'});
	} else {
		$('#lipage7 , #lipage5').show();
		$('#lipage2 , #lipage4').show();
		$('#lipage3').css({'margin-left':'0px'});
	}
	if(a.totalpage<=1) {
		$('#lipage2 , #lipage4').hide();
		$('#lipage7 , #lipage5').hide();
		$('#lipage6').hide();
		$('#lipage3').css({'margin-left':'400px'});
	}
	$('#font_pageno').html(a.totalpage);
	$('.table_data tbody').html(ajaxPageHtml(a.datalist,a.num));
	var html = '';
	//alert(a.pageno);  a.totalpage
	for(i=1;i<=a.totalpage;i++) {
		html += '<option value="'+i+'" ';
		if(i==a.pageno) html += 'selected ';
		html += '>第 '+i+' 页</option>';
	}
	$('#lipage6 select').html(html);
	
	layer.msg('删除成功', {icon: 1,time:1000});
}

//函数作用：搜索真实姓名
function searchName(ajaxUrl,content) {
	$data = {
		'action' : 'searchname',
		'realname' : content
	};
	
	$.ajax({
		'url' : ajaxUrl,
		'type' : 'POST',
		dataType : 'json',
		'data' : $data,
		beforeSend : function() {
			loading = layer.load(3);
		},
		complete : function () {
			layer.close(loading);
		},
		success : function (a) {
			if(a.status=='error') {
				layer.close(loading);
				layer.alert(a.message, {icon: 2,title:'提示信息'});
				return;
			}
			$('.table_data tbody').html(ajaxPageHtml(a.dataList,1));
			$('ul.page').hide();
		}
	});
}
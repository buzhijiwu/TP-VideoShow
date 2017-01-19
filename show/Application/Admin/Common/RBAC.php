<?php
function _getNode() {
	$lan = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
	$nodeList_m_result = M('menu')->where(array('lantype'=>$lan,'parentid'=>array('NEQ',-1)))->order('sort ASC')->select();
	$array = array();
	foreach($nodeList_m_result as $k=>$v) {
		$array[$v['menuid']] = $v;
	}
	$array = generateTree($array);
	return $array;
}

function getroleNode () {
	$lan = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
	$nodeList_m_result = M('menu')->where(array('lantype'=>$lan,'parentid'=>array('NEQ',-1)))->order('sort ASC')->select();
	$array = array();
	foreach($nodeList_m_result as $k=>$v) {
		$array[$v['menuid']] = $v;
	}
	$array = generateTree($array);
	$html = '<div class="div_1">';
	foreach($array as $k=>$v) {
		$html .= '<input type="hidden" name="nodeid[]" value="'.$v['menuid'].'" />';
		$html .= '<div class="node0">';
		$html .= '<div class="div_1_a"><font><span style="border:0px solid red;display:inline-block;width:8px;height:10px;background:url(__PUBLIC__/Public/Images/ico03.gif) no-repeat;"></span>';
	}
}
/*
	
		 {$v['menuname']} </font> &nbsp; <input style="display:none;" class="sort" size="1" type="text" name="nodesort[]" value="{$v['sort']}" placeholder="顺序" /> &nbsp;<label style="font-size:14px;"><input class="node0_ck" type="checkbox" value="1" />选择</label>
<if condition="!$v['surpernode']" >
<a style="margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#0864C8;" class="edit_node" href="javascript:;" nid="{$v['id']}">编辑</a>
<a style="display:none;margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#5864C8;" class="del_node1" href="javascript:;" nid="{$v['id']}">删除</a>
</if>
		</div>
		<foreach name="v.son" item="v0">
		<input type="hidden" name="nodeid[]" value="{$v0['id']}" />
		<div class="node1">
			<div class="div_1_b" style="margin-left:20px;margin-top:5px;"><font color="green">&nbsp;<span style="border:0px solid red;display:inline-block;width:8px;height:10px;background:url(__PUBLIC__/Public/Images/ico03.gif) no-repeat;"></span> {$v0['menuname']} </font> &nbsp; <input class="sort" style="display:none;border:1px solid pink;" size="1" type="text" name="nodesort[]" value="{$v0['sort']}"  placeholder="顺序" />&nbsp;<label style="font-size:14px;color:green;"><input class="node1_ck" type="checkbox" value="1" />选择</label>
<if condition="!$v0['surpernode']" >
<a style="margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#0864C8;" class="edit_node" href="javascript:;" nid="{$v0['id']}">编辑</a>
<a style="display:none;margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#5864C8;" class="del_node1" href="javascript:;" nid="{$v0['id']}">删除</a>
</if>
</div>
			<foreach name="v0.son" item="v1">
			<input type="hidden" name="nodeid[]" value="{$v1['id']}" />
			<div class="node2">
				<div class="div_1_c" style="margin-left:40px;margin-top:5px;"><font color="<if condition="$v1['ismenu'] eq 0">red<else />purple</if>"> &nbsp;&nbsp; {$v1['menuname']}</font> &nbsp; <input style="display:none;" class="sort" size="1" type="text" name="nodesort[]" value="{$v1['sort']}"  placeholder="顺序" />&nbsp;<label style="font-size:14px;color:pink;"><input class="node2_ck" type="checkbox" value="1" />选择</label>
<if condition="!$v1['surpernode']" >
<a style="margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#0864C8;" class="edit_node" href="javascript:;" nid="{$v1['id']}">编辑</a>
<a style="display:none;margin-left:15px;text-decoration:none;font-weight:bold;font-size:12px;color:#5864C8;" class="del_node2 del_node1" href="javascript:;" nid="{$v1['id']}">删除</a>
</if>
</div>
			</div>
			</foreach>
		</div>
		</foreach>
	</div>
	</foreach>
</div>

<div style="margin-top:30px;margin-left:130px;">
	<input style="background:#53B3B6;font-size:20px;width:100px;height:30px;cursor:pointer;" class="subit" type="button" value="提 &nbsp;&nbsp;&nbsp; 交" />
</div>
*/
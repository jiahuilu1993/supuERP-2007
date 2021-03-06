<?php
//企业列表  获取页面请求，查询列表用extract获取get变量，修改与添加用 $_GET 或$_post
$ctfrom	= getgp( 'ctfrom' )?getgp( 'ctfrom' ):$_SESSION['extraInfo']['ctfrom'];
$fields = $join = $where = '';
extract($_GET, EXTR_SKIP); //获取搜索项 
//$_GET['ep_name']
if ($_SESSION['extraInfo']['ctfrom']=='01000000') {
    $ctf='';
}else{
    $ctf = $_SESSION['extraInfo']['ctfrom'];
    $hezuofang = 1;
}
$ctfrom			   = getgp( 'ctfrom' )?getgp( 'ctfrom' ):$ctf;
/*搜索条件*/
if ($ep_name) {
    $where .= " AND e.ep_name LIKE '%" . str_replace('%', '\%', $ep_name) . "%'";
}
if ($ep_phone) {
    $where .= " AND e.ep_phone LIKE '%" . str_replace('%', '\%', $ep_phone) . "%'";
}
$ep_amount_select = '<option value="500">0——500</option>
       <option value="1000">500——1000</option>
       <option value="more">more</option>';
if ($ep_amount == "500") {
    $where .= " AND e.ep_amount<=500";
    $ep_amount_select = str_replace('value="500"', 'value="500" selected', $ep_amount_select);
}
if ($ep_amount == "1000") {
    $where .= " AND e.ep_amount>500 AND e.ep_amount<=1000";
    $ep_amount_select = str_replace('value="1000"', 'value="1000" selected', $ep_amount_select);
}
if ($ep_amount == "more") {
    $where .= " AND e.ep_amount>1000";
    $ep_amount_select = str_replace('value="more"', 'value="more" selected', $ep_amount_select);
}
if ($ep_level) {
    $where .= " AND e.ep_level='$ep_level'";
}
//合同来源限制

if(  $ctfrom ){
    $where .= " AND e.ctfrom = '$ctfrom'";
}
$ctfrom_select  = str_replace("value=\"$ctfrom\" >", "value=\"$ctfrom\" selected>", $ctfrom_select);
$ep_type_select = str_replace("value=\"$ep_type\">", "value=\"$ep_type\" selected>", $ep_type_select);
if ($areacode) {
    $pcode = substr($areacode, 0, 2) . '0000';
    $where .= " AND LEFT(e.areacode,2) = '" . substr($areacode, 0, 2) . "'";
    $province_select = str_replace("value=\"$pcode\">", "value=\"$pcode\" selected>", $province_select);
}
if ($work_code) {
    $where .= " AND e.work_code = '$work_code'";
}
if ($person) {
    $where .= " AND e.person LIKE '%" . str_replace('%', '\%', $person) . "%'";
}
if ($person_tel) {
    $where .= " AND e.person_tel LIKE '%" . str_replace('%', '\%', $person_tel) . "%'";
}
if ($parent_id) {
    $where .= " AND e.parent_id = '$parent_id'";
}
$where .= " AND e.deleted = '0'";
/*分页*/
if (!$export) {
    $total = $db->get_var("SELECT COUNT(*) FROM sp_enterprises e WHERE 1 $where");
    $pages = numfpage($total);
}
/*列表*/
$enterprises = array();
$query       = $db->query("SELECT e.* FROM sp_enterprises e $join WHERE 1 $where ORDER BY e.update_date DESC $pages[limit]");
while ($rt = $db->fetch_array($query)) {
//LY 翻译个别表项，并格式化数据数组
        //var_dump($rt);
    
    $qh_name =$db->get_row("select * from sp_settings_region where code='".$rt['areacode']."' and deleted=0");
    $metas                   = array();
    $rt['province']          = $qh_name['name'];
    // $rt['ctfrom']            = f_ctfrom($rt['ctfrom']);
    $where = "code="."'".$rt['ctfrom']."'";
    $rt['ctfrom'] = $db->get_var("SELECT name FROM sp_settings_ctfrom WHERE  $where");
    $metas                   = $enterprise->meta($rt['eid']);
    //var_dump($metas);
    $rt['union_count_V']     = (!$rt['union_count']) ? '' : "<a href=\"?c=enterprise&a=list&parent_id={$rt[eid]}\">$rt[union_count]</a>";
    $rt['site_count_V']      = (!$rt['site_count']) ? '' : "<a href=\"?c=enterprise&a=list_site&eid={$rt[eid]}\">$rt[site_count]</a>";
    $rt['ep_type_V']         = $ep_type_array[$rt['ep_type']]['name'];
    $rt                      = array_merge($rt, $metas);
    //var_dump($rt);
    $enterprises[$rt['eid']] = $rt;
    // echo "<pre />";
    // print_r($rt);exit;
    //var_dump($enterprises);
}
     //var_dump($export);
if (!$export) {

    tpl();
} else { //导出企业列表
    ob_start();
    //LY 调用XLS将数据表格化实现批量导出
    tpl('xls/list_enterprise');
    $data = ob_get_contents();
    var_dump($data);
    ob_end_clean();
    export_xls('企业列表', $data);
}
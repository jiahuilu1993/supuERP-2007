<?php

require( DATA_DIR . 'cache/audit_ver.cache.php' );

$tid = (int)getgp( 'tid' );
$ctid = (int)getgp( 'ct_id' );
$t_info=$db->get_row("SELECT eid,tb_date,te_date FROM `sp_task` WHERE `id` = '$tid'");
extract( $t_info, EXTR_SKIP );
$ep_info=load("enterprise")->get(array("eid"=>$eid));
extract( $ep_info, EXTR_SKIP );

//项目编号
$xiangmu=$db->getAll("select sp.cti_code from `sp_project` sp left join `sp_settings_audit_vers` ssav on sp.`audit_ver`=ssav.audit_ver where sp.`tid`='$tid' GROUP BY sp.iso");
$str_xiangmu='';
foreach( $xiangmu as $v)
{
	$str_xiangmu .= $v['cti_code'].'/';
}
$str_xiangmu = substr($str_xiangmu,0,strlen($str_xiangmu)-1);
// 项目编号
$ct=$db->get_row("SELECT ct_code FROM `sp_project` WHERE `tid` = '$tid' AND `deleted` = '0'");
extract( $ct, EXTR_SKIP );

//审核组信息
$leader = $auditors = array();
$sql="SELECT name,role,uid FROM sp_task_audit_team  WHERE tid = '$tid' and deleted=0";
$query = $db->query( $sql);

while( $rt = $db->fetch_array( $query ) ){
	if( $rt['role']=="1001" ){
		$leader=$rt[name];
	} else {
		$auditors[$rt[uid]]=$rt['name'];
	} 
	
}

$filename = '审表022 审核员评价表('.$ep_name.').doc';
//读入模板文件 
$tpldata = readover( DOCTPL_PATH . 'doc/SH-022.xml' );

//企业信息部分
$arr_search = array('<','>','&','\'','"');
$arr_replace = array('&lt;','&gt;','&amp;','&apos;','&quot;');
$ep_name = str_ireplace($arr_search,$arr_replace,$ep_name);

$output = str_replace( '{ep_name}', $ep_name, $tpldata );
$output = str_replace( '{ct_code}', $ct_code, $output );
$output = str_replace( '{sex_zh}', $sex_zh, $output );
$output = str_replace( '{tel}', $tel, $output );
$output = str_replace( '{leader}', $leader, $output );
$output = str_replace( '{cti_code}', $str_xiangmu , $output );
if( getgp('downs')==1 ){
	$filename = iconv( 'UTF-8', 'gbk', $filename );
	if(!empty(getgp('dates'))){
		$filePath = CONF.'downs'.'/'.getgp('dates');
	}else{
		$filePath = CONF.'downs';
	}
	//没有目录创建目录
	if(!is_dir($filePath)) {
	    mkdir($filePath, 0777, true);
	}
	//如果存在就删除文件
	if( file_exists($filePath.'/'.$filename) ){
		@unlink ($filePath.'/'.$filename); 
	}

	file_put_contents($filePath.'/'.$filename,$output);
	
	if( file_exists($filePath.'/'.$filename) ){
		echo $filePath.'/'.iconv( 'gbk','UTF-8', $filename );
	}
}else{
	header("Content-type: application/octet-stream");
	header("Accept-Ranges: bytes");
	header("Content-Disposition: attachment; filename=" . iconv( 'UTF-8', 'gbk', $filename ) );
	echo $output;exit;
}
?>
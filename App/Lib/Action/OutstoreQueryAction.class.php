<?php
import("@.Action.Common");
class OutstoreQueryAction extends AppAction{
    public function index() {
        import("@.ORG.Util.Page");
        $model=M("outstore_sub");
        $s_keyword = $_GET['keyword'];
        if(strstr($s_keyword,'已勾')){
            $s_keyword = 1;
        }elseif(strstr($s_keyword,'未勾')){
            $s_keyword = 0;
        }elseif(strstr($s_keyword,'复核')){
            $s_keyword = 2;
        }
        elseif(strstr($s_keyword,'未提')){
            $s_keyword = -1;
        }

        if($_GET["searchBy"]!="") {
            $map[$_GET["searchBy"]]=array("like","%{$s_keyword}%");
        }
        if($_GET["osm_danju_no"]!=""){
            $map["osm_danju_no"]=array("like","%{$_GET['osm_danju_no']}%");
            $this->assign("out_danju_no",$_GET['osm_danju_no']);
        }
        if($_GET["osm_buyerunit"]!=""){
            $map["osm_buyerunit"]=array("like","%{$_GET['osm_buyerunit']}%");
            $this->assign("out_buyerunit",$_GET['osm_buyerunit']);
        }
        if($_GET["osm_operator"]!=""){
            $map["osm_operator"]=array("like","%{$_GET['osm_operator']}%");
            $this->assign("out_operator",$_GET['osm_operator']);
        }

        if($_GET["osm_status"]!=""){
            $map["osm_status"]=$_GET['osm_status'];
            $this->assign("out_status",$_GET['osm_status']);
        }
//        if($_GET["oss_total_start"]!=""&&$_GET["oiss_total_end"]=="")$map["osm_total"]=array("egt","{$_GET['oss_total_start']}");
//        if($_GET["oss_total_start"]==""&&$_GET["oss_total_end"]!="")$map["osm_total"]=array("elt","{$_GET['oss_total_end']}");
//        if($_GET["oss_total_start"]!=""&&$_GET["oss_total_end"]!=""){
//            $map["osm_total"]=array(array("egt","{$_GET['oss_total_start']}"),array("elt","{$_GET['oss_total_end']}"));
//        }
        if($_GET["osm_writer"]!=""){
            $map["osm_writer"]=array("like","%{$_GET['osm_writer']}%");
            $this->assign("out_writer",$_GET['osm_writer']);
        }
        if($_GET["osm_date_start"]!=""&&$_GET["osm_date_end"]==""){
            $map["osm_date"]=array("egt","{$_GET['osm_date_start']}".' 00:00:00');
            $this->assign("out_date_start",$_GET['osm_date_start']);
        }
        if($_GET["osm_date_start"]==""&&$_GET["osm_date_end"]!=""){
            $map["osm_date"]=array("elt","{$_GET['osm_date_end']}".' 59:59:59');
            $this->assign("out_date_end",$_GET['osm_date_end']);
        }
        if($_GET["osm_date_start"]!=""&&$_GET["osm_date_end"]!=""){
            $map["osm_date"]=array(array("egt","{$_GET['osm_date_start']}".' 00:00:00'),array("elt","{$_GET['osm_date_end']}".' 59:59:59'));
            $this->assign("out_date_start",$_GET['osm_date_start']);
            $this->assign("out_date_end",$_GET['osm_date_end']);
        }
        if($_GET["oss_prodname"]!=""){
            $prodname = urldecode($_GET['oss_prodname']);
            $map["oss_prodname"]=array("like","%{$prodname}%");
            $this->assign("out_prodname",$prodname);
        }
        if($_GET["oss_store"]!=""){
            $map["sto_name"]=array("like","%{$_GET['oss_store']}%");
            $this->assign("out_store",$_GET['oss_store']);
        }
        $list = $model->join("twms_outstore_main as b on osm_id=oss_mainid")->join('twms_store as c on sto_id=oss_store')->where($map)->field('osm_id')->select();
        $count=count($list);
        $Page = new Page($count,C('PAGE_SIZE'));
        $show = $Page->show();
        $this->assign('page',$show);
        $list=$model->alias("a")->join("twms_outstore_main as b on osm_id=oss_mainid")->join('twms_store as c on sto_id=oss_store')->
            join('twms_product as d on oss_prod=prod_id')->where($map)->
            field("a.*,b.*,d.*")->
            limit($Page->firstRow.','.$Page->listRows)->order("osm_status asc, osm_id desc")->select();

        $list_count=$model->alias("a")->join("twms_outstore_main as b on osm_id=oss_mainid")->join('twms_store as c on sto_id=oss_store')->
            join('twms_product as d on oss_prod=prod_id')->where($map)->
            field("sum(oss_count) as count,sum(oss_plancount) as plancount,sum(oss_count*prod_volume) as total_volume")->find();
        $this->assign("real_count",$list_count['count']);
        $this->assign("plan_count",$list_count['plancount']);
		$this->assign("total_volume",$list_count['total_volume']);
        $this->assign("searchBy",$_GET['searchBy']);
        $this->assign("keyword",$_GET['keyword']);
        $this->assign("list",$list);
        $this->display();
    }
    public function delete(){
        $model_outsub=M("outstore_sub");
        $model_insub=M("instore_sub");
        $model_outmain=M("outstore_main");
        $model_inmain=M("instore_main");
        //删除因为出库不影响入库单的数据原始性，只影响数据统计
        //$list_outSub=$model_outsub->where(array('oss_mainid'=>$_GET['osm_id']))->select();
        //foreach($list_outSub as $row){
        //    $model_insub->setInc("iss_count","iss_id={$row['oss_insubid']}",$row["oss_count"]);
        //    $model_insub->setInc("iss_total","iss_id={$row['oss_insubid']}",$row["oss_count"]*$row["oss_price"]);
        //}
        $model_outsub->where(array('oss_mainid'=>$_GET['osm_id']))->delete();
        $out_main=$model_outmain->where(array('osm_id'=>$_GET['osm_id']))->find();
        $model_inmain->setInc("ism_total","ism_id={$out_main['osm_inmainid']}",$out_main["osm_total"]);
        $model_outmain->where(array('osm_id'=>$_GET['osm_id']))->delete();
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库单删除");
        $this->redirect("index");
    }

    public function approve(){
        $model_main=M("outstore_main");
        $map["osm_id"]=$_GET['id'];
        $data['osm_status'] = 1;
        $data['osm_status_time'] = date('Y-m-d H:i:s');
        $data['osm_operator'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库勾单成功");
        $this->redirect("index");
    }

    public function doApprove(){
        $model_main=M("outstore_main");
        $map["osm_id"]=$_POST['osm_id'];
        $data['osm_status'] = 1;
        $data['osm_status_time'] = date('Y-m-d H:i:s');
        $data['osm_operator'] =$_SESSION['user']['user_realname'];
        $data['osm_carry'] = $_POST['osm_carry'];
        if($data['osm_carry']==''){
            echo "<script>alert('请选择搬运组!');window.history.back();</script>";
            die;
        }
        $model_main->where($map)->save($data);

        $model_sub=M("outstore_sub");

        for($i=0;$i<count($_POST["oss_id"]);$i++){
            $data['oss_count']=$_POST['oss_count'][$i];
            $data['oss_plancount']=$_POST['oss_plancount'][$i];


            $model_sub->where(array("oss_id"=>$_POST['oss_id'][$i]))->save($data);

        }
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库勾单成功");
        $this->redirect("index");
    }

    public function doReview(){
        $model_main=M("outstore_main");
        $map["osm_id"]=$_GET['osm_id'];
        $data['osm_status'] = 2;
        $data['osm_reviwer_time'] = date('Y-m-d H:i:s');
        $data['osm_reviwer'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库单复核成功");
        $this->redirect("index");
    }

    public function doSubmitOUT(){
        $model_main=M("outstore_main");
        $map["osm_id"]=$_GET['osm_id'];
        $data['osm_status'] = 0;
        $data['osm_submit_time'] = date('Y-m-d H:i:s');
        $data['osm_submiter'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库单提交成功");
        $this->redirect("index");
    }

    public function doRollbackOUT(){
        $model_main=M("outstore_main");
        $map["osm_id"]=$_GET['osm_id'];
        $data['osm_status'] = -1;
        $data['osm_rollback_time'] = date('Y-m-d H:i:s');
        $data['osm_rollbacker'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库单退单成功");
        $this->redirect("index");
    }


    public function view(){
        $model_main=M('outstore_main');
        $main=$model_main->where(array('osm_id'=>$_GET['osm_id']))->find();
        $model_sub=M('outstore_sub');
        $list_sub=$model_sub->join('twms_prod_cate on oss_cate=pdca_id')->
            join('twms_product on oss_prod=prod_id')->
            join('twms_store on sto_id=oss_store')->where(array('oss_mainid'=>$_GET['osm_id']))->order('oss_id asc')->select();
        $model_store=M('store');
        $list_store=$model_store->select();
        $model_carry=M("prod_carry");
        $list_carry=$model_carry->select();
        $count_plan = $model_sub->where(array("oss_mainid"=>$_GET['osm_id']))->sum('oss_plancount');
        $count_real = $model_sub->where(array("oss_mainid"=>$_GET['osm_id']))->sum('oss_count');

        $this->assign("count_plan",$count_plan);
        $this->assign("count_real",$count_real);
        $this->assign("list_carry",$list_carry);
        $this->assign('list_store',$list_store);
        $this->assign('main',$main);
        $this->assign('list_sub',$list_sub);
        $this->display();
    }
    public function toEdit(){
        $model_main=M('outstore_main');
        $main=$model_main->where(array('osm_id'=>$_GET['osm_id']))->find();
        $model_sub=M('outstore_sub');
        $list_sub=$model_sub->join('twms_product on prod_id=oss_prod')->
            join('twms_store on sto_id=oss_store')->
            join('twms_prod_cate on oss_cate=pdca_id')->
            where(array('oss_mainid'=>$_GET['osm_id']))->order('oss_prodname asc')->select();

        $model_store=M('store');
        $list_store=$model_store->select();
        $this->assign('list_store',$list_store);

        //add for quality
        $model_quality=M('prod_quality');
        $list_quality=$model_quality->select();
        $this->assign('list_quality',$list_quality);

        $this->assign('main',$main);
        $this->assign('list_sub',$list_sub);
        $this->display();
    }
    public function doEdit(){
        $model_outmain=M("outstore_main");
        $model_outmain->create();
        $model_outsub=M("outstore_sub");

        $model_outsub->where(array('oss_mainid'=>$_POST['osm_id']))->delete();
        for($i=0;$i<count($_POST["oss_prodname"]);$i++){
            $data_sub["oss_insubid"]=$_POST["oss_insubid"][$i];
            $data_sub["oss_mainid"]=$_POST['osm_id'];
            $data_sub["oss_prodname"]=$_POST["oss_prodname"][$i];

            $data_sub["oss_quality"]=$_POST["oss_quality"][$i]; //new add
            $data_sub["oss_unit"]=$_POST["oss_unit"][$i]; //new add

            $data_sub["oss_prod"]=$_POST["oss_prod"][$i];
            $data_sub["oss_price"]=$_POST["oss_price"][$i];
            $data_sub["oss_count"]=$_POST["oss_count"][$i];
            $data_sub['oss_plancount']=$_POST['oss_plancount'][$i];
            if($data_sub["oss_count"]==''){
                $data_sub["oss_count"] = $data_sub["oss_plancount"];
            }

            if($data_sub['oss_count'] > $data_sub['oss_plancount']){
                echo "<script>alert('实发数量不能大于应发数量!');window.history.back();</script>";
                die;
            }

            $data_sub["oss_total"]=$_POST["oss_total"][$i];
            $data_sub["oss_store"]=$_POST["oss_store"][$i];
            $data_sub["oss_remark"]=$_POST["oss_remark"][$i];
            $data_sub["oss_cate"]=$_POST["oss_cate"][$i];
            $model_outsub->add($data_sub);
            //$data_main["osm_total"]+=$_POST["oss_total"][$i];
            ////删除因为出库不影响入库单的数据原始性，只影响数据统计
            //$model_insub->setDec("iss_count","iss_id={$_POST['oss_insubid'][$i]}",$_POST["oss_count"][$i]);
            //$model_insub->setDec("iss_total","iss_id={$_POST['oss_insubid'][$i]}",$_POST["oss_count"][$i]*$_POST["oss_price"][$i]);
            //$model_inmain->setDec("ism_total","ism_id={$_POST['iss_mainid']}",$_POST["oss_count"][$i]*$_POST["oss_price"][$i]);
        }
        //$data_main["osm_inmainid"]=$_POST["iss_mainid"];
        $model_outmain->save();
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("编辑出库单据");
        $this->redirect("index");
    }

    // add for auto select
    public function autoSelect(){
        $model=M("guest");
        $q = strtolower($_POST["queryString"]);
        $map['gust_name'] = array('like', '%'.$q.'%');
        $list = $model->where($map)->field('gust_name')->select();
        foreach ( $list as $row){

            echo $row['gust_name']."\n";
        }
    }

    public function exportOSS(){

        $s_keyword = $_POST['keyword'];
        if(strstr($s_keyword,'已勾')){
            $s_keyword = 1;
        }elseif(strstr($s_keyword,'未勾')){
            $s_keyword = 0;
        }elseif(strstr($s_keyword,'复核')){
            $s_keyword = 2;
        }elseif(strstr($s_keyword,'未提')){
            $s_keyword = -1;
        }
        if($_POST["searchBy"]!=""){
            $map[$_POST["searchBy"]]=array("like","%{$s_keyword}%");
        }

        if($_POST["out_buyerunit"]!=""){
            $map["b.osm_buyerunit"]=array("like","%{$_POST['out_buyerunit']}%");
        }
        if($_POST["out_danju_no"]!=""){
            $map["b.osm_danju_no"]=array("like","%{$_POST['out_danju_no']}%");
        }
        if($_POST["out_prodname"]!=""){
            $map["a.oss_prodname"]=array("like","%{$_POST['out_prodname']}%");
        }

        if($_POST["out_status"]!=""){
            $map["b.osm_status"]=$_POST['out_status'];
        }

        if($_POST["out_store"]!=""){
            $map["c.sto_name"]=array("like","%{$_POST['out_store']}%");
        }
        if($_POST["out_date_start"]!=""&&$_POST["out_date_end"]=="")
        {
            $map["b.osm_date"]=array("egt","{$_POST["out_date_start"]}".' 00:00:00');
        }
        if($_POST["out_date_start"]==""&&$_POST["out_date_end"]!="")
        {
            $map["b.osm_date"]=array("elt","{$_POST["out_date_end"]}".' 59:59:59');
        }
        if($_POST["out_date_start"]!=""&&$_POST["out_date_end"]!=""){
            $map["b.osm_date"]=array(array("egt","{$_POST["out_date_start"]}".' 00:00:00'),array("elt","{$_POST["out_date_end"]}".' 59:59:59'));
        }
        if($_POST["out_writer"]!=""){
            $map["b.osm_writer"]=array("like","%{$_POST['out_writer']}%");
        }
        if($_POST["out_operator"]!=""){
            $map["b.osm_operator"]=array("like","%{$_POST['out_operator']}%");
        }
        $xlsName  = "出库单";

        if(($_SESSION['user']['user_type']==1||$_SESSION['user']['user_type']==4)){
            $xlsCell  = array(
                array('osm_buyerunit','客户单位'),
                array('osm_danju_no','客户单据号'),
                array('osm_danju_date','客户单据日期'),
                array('oss_prodname','品名规格'),
                array('pdca_name','货物类别'),
                array('oss_unit','计价单位'),
                array('oss_quality','质量类别'),
                array('oss_plancount','应发数量'),
                array('oss_count','实发数量'),
                array('prod_price','装卸成本单价'),
                array('prod_realprice','装卸收入单价'),
                array('prod_volume','单位立方量'),
                array('osm_carry','搬运组'),
                array('osm_date','制单日期'),
                array('osm_writer','制单员'),
                array('osm_status','单据状态'),
                array('osm_status_time','勾单日期'),
                array('osm_operator','勾单员'),
                array('osm_remark','单据备注')

            );
            $filed = 'osm_buyerunit,osm_danju_no,DATE_FORMAT(osm_danju_date,"%Y-%m-%d") osm_danju_date,oss_prodname,pdca_name,oss_unit,oss_quality,oss_plancount,oss_count,prod_price,prod_realprice,prod_volume,osm_carry,DATE_FORMAT(osm_date,"%Y-%m-%d") osm_date,osm_writer,CASE WHEN osm_status =0 THEN  "未勾单" WHEN osm_status =1 THEN  "已勾单" WHEN osm_status =2 THEN  "已复核" END osm_status,DATE_FORMAT(osm_status_time,"%Y-%m-%d") osm_status_time,osm_operator,osm_remark';

        }else{

            $xlsCell  = array(
                array('osm_buyerunit','客户单位'),
                array('osm_danju_no','客户单据号'),
                array('osm_danju_date','客户单据日期'),
                array('oss_prodname','品名规格'),
                array('pdca_name','货物类别'),
                array('oss_unit','计价单位'),
                array('oss_quality','质量类别'),
                array('oss_plancount','应收数量'),
                array('oss_count','实收数量'),
                array('prod_volume','单位立方量'),
                array('osm_carry','搬运组'),
                array('osm_date','制单日期'),
                array('osm_writer','制单员'),
                array('osm_status','单据状态'),
                array('osm_status_time','勾单日期'),
                array('osm_operator','勾单员'),
                array('osm_remark','单据备注')

            );
            $filed = 'osm_buyerunit,osm_danju_no,DATE_FORMAT(osm_danju_date,"%Y-%m-%d") osm_danju_date,oss_prodname,pdca_name,oss_unit,oss_quality,oss_plancount,oss_count,prod_volume,osm_carry,DATE_FORMAT(osm_date,"%Y-%m-%d") osm_date,osm_writer,CASE WHEN osm_status =0 THEN  "未勾单" WHEN osm_status =1 THEN  "已勾单" WHEN osm_status =2 THEN  "已复核" END osm_status,DATE_FORMAT(osm_status_time,"%Y-%m-%d") osm_status_time,osm_operator,osm_remark';
        }

        $model_sub=M("outstore_sub");
        $list_sub = $model_sub->alias('a')->join('twms_outstore_main b on a.oss_mainid	=b.osm_id')->
            join('twms_prod_cate c on a.oss_cate=c.pdca_id')->
            join('twms_product d on a.oss_prod	 = d.prod_id')->
            where($map)->
            Field($filed)->
            order('osm_buyerunit,osm_danju_no')->
            select();

        //var_dump($model_sub->getLastSql());

        $common = new CommonAction();
        $common->exportExcel($xlsName,$xlsCell,$list_sub);

    }

}
?>
<?php

import("@.Action.Common");
class InstoreQueryAction extends AppAction{
    public function index() {
        import("@.ORG.Util.Page");
        $model=M("instore_sub");
        $s_keyword = $_GET['keyword'];
        if(strstr($s_keyword,'已勾')){
            $s_keyword = 1;
        }elseif(strstr($s_keyword,'未勾')){
            $s_keyword = 0;
        }elseif(strstr($s_keyword,'复核')){
            $s_keyword = 2;
        }elseif(strstr($s_keyword,'未提')){
            $s_keyword = -1;
        }

        if($_GET["searchBy"]!=""){
            $map[$_GET["searchBy"]]=array("like","%{$s_keyword}%");
        }
        if($_GET["ism_danju_no"]!=""){
            $map["ism_danju_no"]=array("like","%{$_GET['ism_danju_no']}%");
            $this->assign("in_danju_no",$_GET['ism_danju_no']);
        }
        if($_GET["ism_sellerunit"]!=""){
            $map["ism_sellerunit"]=array("like","%{$_GET['ism_sellerunit']}%");
            $this->assign("in_sellerunit",$_GET['ism_sellerunit']);
        }
        if($_GET["ism_operator"]!=""){
            $map["ism_operator"]=array("like","%{$_GET['ism_operator']}%");
            $this->assign("in_operator",$_GET['ism_operator']);

        }

        if($_GET["ism_status"]!=""){
            $map["ism_status"]=$_GET['ism_status'];
            $this->assign("in_status",$_GET['ism_status']);

        }

//        if($_GET["iss_total_start"]!=""&&$_GET["iss_total_end"]=="")$map["ism_total"]=array("egt","{$_GET['iss_total_start']}");
//        if($_GET["iss_total_start"]==""&&$_GET["iss_total_end"]!="")$map["ism_total"]=array("elt","{$_GET['iss_total_end']}");
//        if($_GET["iss_total_start"]!=""&&$_GET["iss_total_end"]!=""){
//            $map["ism_total"]=array(array("egt","{$_GET['iss_total_start']}"),array("elt","{$_GET['iss_total_end']}"));
//        }
        if($_GET["ism_writer"]!=""){
            $map["ism_writer"]=array("like","%{$_GET['ism_writer']}%");
            $this->assign("in_writer",$_GET['ism_writer']);
        }
        if($_GET["ism_date_start"]!=""&&$_GET["ism_date_end"]=="")
        {
            $map["ism_date"]=array("egt","{$_GET['ism_date_start']}".' 00:00:00');
            $this->assign("in_date_start",$_GET['ism_date_start']);
        }
        if($_GET["ism_date_start"]==""&&$_GET["ism_date_end"]!="")
        {
            $map["ism_date"]=array("elt","{$_GET['ism_date_end']}".' 59:59:59');
            $this->assign("in_date_end",$_GET['ism_date_end']);
        }
        if($_GET["ism_date_start"]!=""&&$_GET["ism_date_end"]!=""){
            $map["ism_date"]=array(array("egt","{$_GET['ism_date_start']}".' 00:00:00'),array("elt","{$_GET['ism_date_end']}".' 59:59:59'));
            $this->assign("in_date_start",$_GET['ism_date_start']);
            $this->assign("in_date_end",$_GET['ism_date_end']);
        }
        if($_GET["iss_prodname"]!=""){
            $prodname = urldecode($_GET['iss_prodname']);
            $map["iss_prodname"]=array("like","%{$prodname}%");
            $this->assign("in_prodname",$prodname);
        }
        if($_GET["iss_store"]!=""){
            $map["c.sto_name"]=array("like","%{$_GET['iss_store']}%");
            $this->assign("in_store",$_GET['iss_store']);
        }
        $list=$model->alias('a')->join("twms_instore_main as b on ism_id=iss_mainid")->join('twms_store as c on sto_id=iss_store')->where($map)->
            field('ism_id')->select();
        $count=count($list);
        $Page = new Page($count,C('PAGE_SIZE'));
        $show = $Page->show();
        $this->assign('page',$show);
        $list=$model->alias('a')->join("twms_instore_main as b on ism_id=iss_mainid")->join('twms_store as c on sto_id=iss_store')->
            join('twms_product as d on iss_prod=prod_id')->where($map)->
            field("a.*,b.*,d.*")->
            order("ism_status asc,ism_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        //var_dump($model->getLastSql());
        $list_count=$model->alias('a')->join("twms_instore_main as b on ism_id=iss_mainid")->join('twms_store as c on sto_id=iss_store')->
            join('twms_product as d on iss_prod=prod_id')->where($map)->
            field("sum(iss_count) as count,sum(iss_plancount) as plancount")->find();
        //var_dump($list_count['count']);
        $this->assign("real_count",$list_count['count']);
        $this->assign("plan_count",$list_count['plancount']);
        $this->assign("searchBy",$_GET['searchBy']);
        $this->assign("keyword",$_GET['keyword']);
        $this->assign("list",$list);
        $this->display();
    }
    public function delete(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_GET['id'];
        $model_main->where($map)->delete();
        $model_sub=M("instore_sub");
        $model_sub->where(array("iss_mainid"=>$_GET['id']))->delete();
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("订单删除");
        $this->redirect("index");
    }

    public function approve(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_GET['id'];
        $data['ism_status'] = 1;
        $data['ism_status_time'] = date('Y-m-d H:i:s');
        $data['ism_operator'] =$_SESSION['user']['user_realname'];

        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("勾单成功");
        $this->redirect("index");
    }

    public function doApprove(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_POST['ism_id'];
        $data['ism_status'] = 1;
        $data['ism_status_time'] = date('Y-m-d H:i:s');
        $data['ism_operator'] =$_SESSION['user']['user_realname'];
        $data['ism_carry'] = $_POST['ism_carry'];
        if($data['ism_carry']==''){
            echo "<script>alert('请选择搬运组!');window.history.back();</script>";
            die;
        }
        $model_main->where($map)->save($data);

        $model_sub=M("instore_sub");

        for($i=0;$i<count($_POST["iss_id"]);$i++){
            $data['iss_count']=$_POST['iss_count'][$i];
            $data['iss_plancount']=$_POST['iss_plancount'][$i];

            $model_sub->where(array("iss_id"=>$_POST['iss_id'][$i]))->save($data);

        }

        import('@.ORG.Util.SysLog');
        SysLog::writeLog("入库勾单成功");
        $this->redirect("index");
    }

    public function doReview(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_GET['ism_id'];
        $data['ism_status'] = 2;
        $data['ism_reviwer_time'] = date('Y-m-d H:i:s');
        $data['ism_reviwer'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("入库单复核成功");
        $this->redirect("index");
    }

    public function doSubmitIN(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_GET['ism_id'];
        $data['ism_status'] = 0;
        $data['ism_submit_time'] = date('Y-m-d H:i:s');
        $data['ism_submiter'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("入库单提交成功");
        $this->redirect("index");
    }


    public function doRollbackIN(){
        $model_main=M("instore_main");
        $map["ism_id"]=$_GET['ism_id'];
        $data['ism_status'] = -1;
        $data['ism_rollback_time'] = date('Y-m-d H:i:s');
        $data['ism_rollbacker'] =$_SESSION['user']['user_realname'];
        $model_main->where($map)->save($data);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("入库单退单成功");
        $this->redirect("index");
    }


    public function view(){
        $model_main=M("instore_main");
        $main=$model_main->where(array("ism_id"=>$_GET['ism_id']))->find();
        $model_sub=M("instore_sub");
        $list_sub=$model_sub->join('twms_product on iss_prod=prod_id')->
            join('twms_store on sto_id=iss_store')->
            join('twms_prod_cate on pdca_id=iss_cate')->
            where(array("iss_mainid"=>$_GET['ism_id']))->order('iss_id	 asc')->select();
        $model_carry=M("prod_carry");
        $list_carry=$model_carry->select();
        $count_plan = $model_sub->where(array("iss_mainid"=>$_GET['ism_id']))->sum('iss_plancount');
        $count_real = $model_sub->where(array("iss_mainid"=>$_GET['ism_id']))->sum('iss_count');

        $this->assign("main",$main);
        $this->assign("list_sub",$list_sub);
        $this->assign("list_carry",$list_carry);
        $this->assign("count_plan",$count_plan);
        $this->assign("count_real",$count_real);
        $this->display();
    }
    public function toEdit(){
        $model_main=M("instore_main");
        $main=$model_main->where(array("ism_id"=>$_GET['ism_id']))->find();
        $model_sub=M('instore_sub');
        $list_sub=$model_sub->alias('a')->join('twms_product on iss_prod=prod_id')->
            join('twms_prod_cate on iss_cate=pdca_id')->where(array('iss_mainid'=>$_GET['ism_id']))->order('iss_prodname asc')->select();
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
        $model_main=M("instore_main");

        $model_main->create();
        $model_sub=M("instore_sub");
        $model_sub->where(array('iss_mainid'=>$_POST['ism_id']))->delete();

        $map["ism_danju_no"] = $_POST['ism_danju_no'];
        $map["ism_id"] = array('neq',$_POST['ism_id']);
        $one=$model_main->where($map)->find();
        if($one){
            echo "<script>alert('客户单据号已存在，请确认后重新填写!');window.history.back();</script>";
            die;
        }

        for($i=0;$i<count($_POST["iss_prodname"]);$i++){
            $data['iss_mainid']=$_POST['ism_id'];
            $data['iss_prodname']=$_POST['iss_prodname'][$i];
            $data['iss_prod']=$_POST['iss_prod'][$i];
            $data['iss_cate']=$_POST['iss_cate'][$i];
            $data['iss_price']=$_POST['iss_price'][$i];

            $data['iss_quality']=$_POST['iss_quality'][$i];
            $data['iss_unit']=$_POST['iss_unit'][$i];

            $data['iss_count']=$_POST['iss_count'][$i];
            $data['iss_plancount']=$_POST['iss_plancount'][$i];
            if($data["iss_count"]==''){
                $data["iss_count"] = $data["iss_plancount"];
            }

            if($data['iss_count'] > $data['iss_plancount']){
                echo "<script>alert('实收数量不能大于应收数量!');window.history.back();</script>";
                die;
            }

            $data['iss_total']=$_POST['iss_total'][$i];
            $data['iss_store']=$_POST['iss_store'][$i];
            $data['iss_remark']=$_POST['iss_remark'][$i];
            $data['iss_datetime']=date('Y-m-d H:i:s');
            $model_main->ism_total+=$data['iss_total'];
            $model_sub->add($data);
        }
        $model_main->save();
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("编辑出库单据");
        $this->redirect("index");
    }

    public function exportISS(){

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
        if($_POST["keyword"]!=""){
            $map[$_POST["searchBy"]]=array("like","%{$s_keyword}%");
        }

        if($_POST["in_sellerunit"]!=""){
            $map["b.ism_sellerunit"]=array("like","%{$_POST['in_sellerunit']}%");
        }
        if($_POST["in_danju_no"]!=""){
            $map["b.ism_danju_no"]=array("like","%{$_POST['in_danju_no']}%");
        }
        if($_POST["in_prodname"]!=""){
            $map["a.iss_prodname"]=array("like","%{$_POST['in_prodname']}%");
        }

        if($_POST["in_status"]!=""){
            $map["b.ism_status"]=$_POST['in_status'];
        }
        if($_POST["in_store"]!=""){
            $map["c.sto_name"]=array("like","%{$_POST['in_store']}%");
        }
        if($_POST["in_date_start"]!=""&&$_POST["in_date_end"]=="")
        {
            $map["b.ism_date"]=array("egt","{$_POST["in_date_start"]}".' 00:00:00');
        }
        if($_POST["in_date_start"]==""&&$_POST["in_date_end"]!="")
        {
            $map["b.ism_date"]=array("elt","{$_POST["in_date_end"]}".' 59:59:59');
        }
        if($_POST["in_date_start"]!=""&&$_POST["in_date_end"]!=""){
            $map["b.ism_date"]=array(array("egt","{$_POST["in_date_start"]}".' 00:00:00'),array("elt","{$_POST["in_date_end"]}".' 59:59:59'));
        }
        if($_POST["in_writer"]!=""){
            $map["b.ism_writer"]=array("like","%{$_POST['in_writer']}%");
        }
        if($_POST["in_operator"]!=""){
            $map["b.ism_operator"]=array("like","%{$_POST['in_operator']}%");
        }
        $xlsName  = "入库单";

        if(($_SESSION['user']['user_type']==1||$_SESSION['user']['user_type']==4)){
            $xlsCell  = array(
                array('ism_sellerunit','客户单位'),
                array('ism_danju_no','客户单据号'),
                array('ism_danju_date','客户单据日期'),
                array('iss_prodname','品名规格'),
                array('pdca_name','货物类别'),
                array('iss_unit','计价单位'),
                array('iss_quality','质量类别'),
                array('iss_plancount','应收数量'),
                array('iss_count','实收数量'),
                array('prod_price','装卸成本单价'),
                array('prod_realprice','装卸收入单价'),
                array('prod_volume','单位立方量'),
                array('ism_carry','搬运组'),
                array('ism_date','制单日期'),
                array('ism_writer','制单员'),
                array('ism_status','单据状态'),
                array('ism_status_time','勾单日期'),
                array('ism_operator','勾单员'),
                array('ism_remark','单据备注')
            );
            $filed = 'ism_sellerunit,ism_danju_no,DATE_FORMAT(ism_danju_date,"%Y-%m-%d") ism_danju_date,iss_prodname,pdca_name,prod_unit,iss_quality,iss_count,iss_plancount,prod_price,prod_realprice,prod_volume,ism_carry,DATE_FORMAT(ism_date,"%Y-%m-%d") ism_date,ism_writer,CASE WHEN ism_status =0 THEN  "未勾单" WHEN ism_status =1 THEN  "已勾单" WHEN ism_status =2 THEN  "已复核" END ism_status,DATE_FORMAT(ism_status_time,"%Y-%m-%d") ism_status_time,ism_operator,ism_remark';
        }else{
            $xlsCell  = array(
                array('ism_sellerunit','客户单位'),
                array('ism_danju_no','客户单据号'),
                array('ism_danju_date','客户单据日期'),
                array('iss_prodname','品名规格'),
                array('pdca_name','货物类别'),
                array('iss_unit','计价单位'),
                array('iss_quality','质量类别'),
                array('iss_plancount','应收数量'),
                array('iss_count','实收数量'),
                array('prod_volume','单位立方量'),
                array('ism_carry','搬运组'),
                array('ism_date','制单日期'),
                array('ism_writer','制单员'),
                array('ism_status','单据状态'),
                array('ism_status_time','勾单日期'),
                array('ism_operator','勾单员'),
                array('ism_remark','单据备注')
            );
            $filed = 'ism_sellerunit,ism_danju_no,DATE_FORMAT(ism_danju_date,"%Y-%m-%d") ism_danju_date,iss_prodname,pdca_name,prod_unit,iss_quality,iss_count,iss_plancount,prod_volume,ism_carry,DATE_FORMAT(ism_date,"%Y-%m-%d") ism_date,ism_writer,CASE WHEN ism_status =0 THEN  "未勾单" WHEN ism_status =1 THEN  "已勾单" WHEN ism_status =2 THEN  "已复核" END ism_status,DATE_FORMAT(ism_status_time,"%Y-%m-%d") ism_status_time,ism_operator,ism_remark';
        }

        $model_sub=M("instore_sub");

        $list_sub = $model_sub->alias('a')->join('twms_instore_main b on a.iss_mainid	=b.ism_id')->
            join('twms_prod_cate c on a.iss_cate=c.pdca_id')->
            join('twms_product d on a.iss_prod	 = d.prod_id')->
            where($map)->
            Field($filed)->
            order('ism_sellerunit,ism_danju_no')->
            select();
        //var_dump($model_sub->getLastSql());
        $common = new CommonAction();
        $common->exportExcel($xlsName,$xlsCell,$list_sub);

    }

}
?>
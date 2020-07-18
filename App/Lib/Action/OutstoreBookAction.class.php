<?php

class OutstoreBookAction extends AppAction{
    public function index() {
        $this->assign('osm_sn','OU-'.date('Ymd-His-').rand(100,999));
        $this->display();
    }
    public function doAdd(){
        $model_outmain=M("outstore_main");
        $model_outsub=M("outstore_sub");
        $model_inmain=M("instore_main");
        $model_insub=M("instore_sub");

        $map["osm_danju_no"] = $_POST['osm_danju_no'];
        $one=$model_outmain->where($map)->find();
        if($one){
            echo "<script>alert('客户单据号已存在，请确认后重新填写!');window.history.back();</script>";
            die;
        }

        $model_outmain->create();
        $main_id=$model_outmain->add();
        for($i=0;$i<count($_POST["row_count"]);$i++){
            $data_sub["oss_insubid"]=$_POST["oss_insubid"][$i];
            $data_sub["oss_mainid"]=$main_id;
            $data_sub["oss_prodname"]=$_POST["oss_prodname"][$i];

            $data_sub["oss_quality"]=$_POST["oss_quality"][$i]; //new add
            $data_sub["oss_unit"]=$_POST["oss_unit"][$i]; //new add

            $data_sub["oss_prod"]=$_POST["oss_prod"][$i];
            //$data_sub["oss_price"]=$_POST["oss_price"][$i];
            $data_sub["oss_count"]=$_POST["oss_count"][$i];
            $data_sub["oss_plancount"]=$_POST["oss_plancount"][$i];

            if($data_sub["oss_count"]==''){
                $data_sub["oss_count"] = $data_sub["oss_plancount"];
            }

            if($data_sub['oss_count'] > $data_sub['oss_plancount']){
                echo "<script>alert('实发数量不能大于应发数量!');window.history.back();</script>";
                die;
            }


            $data_sub["oss_total"]=$_POST["oss_total"][$i];
            $data_sub["oss_store"]=$_POST["oss_store"][$i];
            //$data_sub["oss_remark"]=$_POST["oss_remark"][$i];
            $data_sub["oss_cate"]=$_POST["oss_cate"][$i];
            $model_outsub->add($data_sub);
            //$data_main["osm_total"]+=$_POST["oss_total"][$i];
            ////删除因为出库不影响入库单的数据原始性，只影响数据统计
            //$model_insub->setDec("iss_count","iss_id={$_POST['oss_insubid'][$i]}",$_POST["oss_count"][$i]);
            //$model_insub->setDec("iss_total","iss_id={$_POST['oss_insubid'][$i]}",$_POST["oss_count"][$i]*$_POST["oss_price"][$i]);
            //$model_inmain->setDec("ism_total","ism_id={$_POST['iss_mainid']}",$_POST["oss_count"][$i]*$_POST["oss_price"][$i]);
        }
        $data_main["osm_status"]=-1;
        $model_outmain->where(array('osm_id'=>$main_id))->save($data_main);
        import('@.ORG.Util.SysLog');
        SysLog::writeLog("出库单创建成功");
        $this->redirect("OutstoreQuery/index");
    }
    public function checkName(){
        $model=M('product');
        $map['prod_name']=$_GET['name'];
        $one=$model->where($map)->find();
        if($one==null){
            echo 'no_exist';
        }else{
            echo 'exist';
        }
    }
    public function getProduct(){
        //$model=M('instore_sub');
        $buyer=trim($_GET['buyer']);

        if($_GET['term']){
            $prod_name=trim($_GET['term']);
            $condition = "where (prod_name like '%$prod_name%' OR prod_code like '%$prod_name%') and a.ism_sellerunit = '$buyer'";
        }else{
            $condition = "where ism_sellerunit = '$buyer'";
        }
//        $map['iss_count']=array('neq',0);
//        $list=$model->join('twms_prod_cate on iss_cate=pdca_id')->
//            join('twms_product on iss_prod=prod_id')->
//            join('twms_outstore_sub on oss_insubid = iss_id')->
//            join('twms_outstore_main on osm_id = oss_mainid')->
//            where($map)->order('pdca_id,prod_name')->select();
        $Model_count = new Model();
        $countsql = "select a.*,b.outcount_real,c.outcount_plan
                from
                (SELECT sum(iss_count) as `incount`,a.*,b.pdca_name,d.ism_sellerunit,c.*
                FROM twms_instore_sub a
                LEFT JOIN twms_prod_cate as b on a.iss_cate=b.pdca_id
                LEFT JOIN twms_product  as c on iss_prod=prod_id
                LEFT JOIN twms_instore_main as d on iss_mainid=ism_id
                 WHERE d.ism_status>0
                 GROUP BY iss_prod
                )  as a
                left Join
                (SELECT oss_prod, oss_quality, SUM( oss_count ) AS `outcount_real`
                FROM twms_outstore_sub
                LEFT JOIN twms_outstore_main AS d ON oss_mainid = osm_id
                WHERE d.osm_status >0
                GROUP BY oss_prod )  as b on  a.iss_prod =  b.oss_prod

				left Join
                (SELECT oss_prod, oss_quality, SUM( oss_count ) AS `outcount_plan`
                FROM twms_outstore_sub
                LEFT JOIN twms_outstore_main AS d ON oss_mainid = osm_id
                WHERE d.osm_status =0
                GROUP BY oss_prod )  as c on  c.oss_prod = a.iss_prod
                {$condition}
				 order by pdca_name asc";



        $list = $Model_count->query($countsql);
        foreach($list as $row){
            $countForUse = $row['incount'] - $row['outcount_real'] - $row['outcount_plan'];
            if($row['outcount_plan']==null) $row['outcount_plan'] =0;
            $result[]=array(
                'label'=>$row['prod_name'].'(可提数量:'.$countForUse.') (未提数量:'.$row['outcount_plan'].')',
                'category'=>$row['pdca_name'],
                'value'=>$row['prod_name'],
                'prod_unit'=>$row['prod_unit'],
                'iss_prod'=>$row['iss_prod'],
                'iss_id'=>$row['iss_id'],

                'iss_cate'=>$row['iss_cate'],

                //'iss_total'=>$row['iss_total'],
               // 'iss_remark'=>$row['iss_remark'],
                //'iss_mainid'=>$row['iss_mainid'],
                'pdca_name'=>$row['pdca_name']
            );
        }
        echo json_encode($result);
    }
    public function getStore(){
        $model=M('store');
        $list=$model->select();
        echo json_encode($list);
    }

    public function getQuality(){
        $model=M('prod_quality');
        $list=$model->select();
        echo json_encode($list);
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


}
?>
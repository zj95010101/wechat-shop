<?php
function p($data){
    echo "<pre/>";
    print_r($data);die;
}
function v($data){
    echo "<pre/>";
    var_dump($data);die;
}
function h($data){
    echo "<pre/>";
    halt($data);
}
function r($data){
    return v($data);
}

function yn($str){
    return $str?'是':'否';
}
function status($id,$status){
    if($status==1){
        $str="<a href='javascript:;' class='zt' status='0' id='$id'><span class='label label-success radius'
id='sta'>正常</span></a>";
    }else{
        $str="<a href='javascript:;' class='zt' status='1' id='$id'><span class='label label-success radius' id='sta'>待审</span></a>";
    }
    return $str;
}
function getCat($data){
    $cats=config('MConf.list');
    foreach($data as $k=>$v){
        $data[$k]['catname']=$cats[$v['catid']]?$cats[$v['catid']]:'-';
    }
    return $data;
}
function getCat2($data){
    $cats=config('MConf.list');
    $data['catname']=$cats[$data['catid']]?$cats[$data['catid']]:'-';
    return $data;
}
function api($code=0,$msg='success',$data=[],$http=200){
    $data=[
       'code'=>$code,
        'msg'=>$msg,
        'data'=>$data,
    ];
    return json($data,$http);
}
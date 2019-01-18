<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\lib\Util;

/**
 * 图片上传管理
 */
class Image extends controller
{
    public function index(Request $request)
    {
        try {
            $resUpd = Util::saveUploadFile('file');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return Util::show($resUpd['code'], $resUpd['message'], isset($resUpd['data']) ? $resUpd['data'] : []);

        //获取上传的文件
        // $file = request()->file('file');
        // //保存文件到指定目录下
        // $info = $file->move("./public/static/upload");
        // //获取保存的文件上层目录 + 名称
        // $saveName = $info->getSaveName();
        // $imgLink = config("live.host") . '/upload/' . $saveName;

        // return Util::show(config('code.success'), '上传成功', ['image' => $imgLink]);
    }
}
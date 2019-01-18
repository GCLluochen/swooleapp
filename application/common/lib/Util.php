<?php
namespace app\common\lib;

/**
 * 通用工具类
 */
class Util
{   
    /**
     * API 数据返回
     *
     * @param [type] $code
     * @param [type] $message
     * @param array $data
     * @return void
     */
    public static function show($code, $message, $data = [])
    {
        $result = [
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ];
        //return $result;
        return json_encode($result,  JSON_UNESCAPED_UNICODE);
    }

    /**
     * 生成随机码
     *
     * @return void
     */
    public static function generateRandomCode()
    {
        return mt_rand(100000, 999999);
    }


    /**
     * 上传文件保存操作
     *
     * @param string $formName 上传文件的表单 name
     * @return void
     */
    public static function saveUploadFile(string $formName = '')
    {
        $uploadFile = $_FILES[$formName];
        //判断上传是否成功
        if ((int)$uploadFile['error'] !== 0) {
            return ['code' => config('code.error'), 'message' => '上传失败'];
        }
        $tmpName = trim($uploadFile['tmp_name']);
        $savePathPrefix = './public/static/';
        $savePath = '/upload/' . date("Ymd") . '/';
        $fullPath = $savePathPrefix . $savePath;

        if (!is_dir($fullPath)) {
            //递归创建上传文件夹
            mkdir($fullPath, 0777, true);
        }
        //获取源文件后缀
        $oriNameArr = explode('.', trim($uploadFile['name']));
        $fileExt = $oriNameArr[count($oriNameArr) - 1];
        if (is_dir($fullPath)) {
            //新的文件名称
            $newName = date("YmdHis") . mb_substr(MD5(mt_rand(10000, 99999)), 0, 15) . ".{$fileExt}";
            $fullName = $fullPath . $newName;
            $resMove = move_uploaded_file($tmpName, $fullName);
            if ($resMove) {
                return ['code' => config('code.success'), 'message' => '上传成功', 'data' => ['image' => (config('live.host') . $savePath . $newName)]];
            } else {
                return ['code' => config('code.error'), 'message' => '保存失败'];
            }
        } else {
            return ['code' => config('code.error'), 'message' => '保存失败'];
        } 
    }

}
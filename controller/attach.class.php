<?php


class AttachClass{

    function attach_file($category, $page_srl, $doc_srl, $user_srl, $status)
    {
        $image_path = "files/images/";
        $binaries_path = "files/binaries/";
        $thumbnail_path = "files/thumbnail/";
$all_result = true;
        $result_array = array("files" => array());


     //   echo "HI::";
       // print_r($_FILES['uploadedfile']);
        for ($i = 0; $i < $this -> getAttachCount(); $i++) {
            $error = "attach_error";
            $upload_result = false;

            if ($_FILES['uploadedfile']['name'][$i] == null) return false;
//$img_name = $file_name.".".$tmp_img[1];
            $file = $_FILES['uploadedfile']['name'][$i];
            $filename = basename($file, strrchr($file, '.'));
            $extension = substr(strrchr($file, '.'), 1);
            $filevalue = getTimeStamp() . '-' . GenerateString(10);
            $size = $_FILES['uploadedfile']["size"][$i];
//Check jpg image
            if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {
                $filename = $filevalue;
                $kind = "image";
                $img_name = $filename . "." . $extension;
                $target_path = $image_path . basename($img_name);
            } else {
                $kind = "file";
                $target_path = $binaries_path . $filevalue;
            }


            //파일 사이즈 체크 5M 제한 5M :5242880
            if ($size > 31457280) {

                $error = "attach_size_error";
            } else {
                $upload_result = move_uploaded_file($_FILES['uploadedfile']['tmp_name'][$i], $target_path);
                $thumbnail_file = "";
                $thumbnail_file = $thumbnail_path . $img_name;
                if ($extension == "jpg" || $extension == "jpeg") {
                    Thumbnail::create($target_path,
                        120, 120,
                        SCALE_EXACT_FIT,
                        Array(
                            'savepath' => $thumbnail_file
                        ));
                } else {
                    $thumbnail_file = null;
                }
            }


            if ($upload_result == true) {
                $result = Model_Attach_addAttch($page_srl, $category, $doc_srl, $user_srl, $kind, $filename, $extension, $filevalue, $size, $status);

                if ($kind == "image") {
                    array_push($result_array['files'], array("name" => $filename . "." . $extension, "size" => $size, "url" => getCoreUrl(true) . "$target_path", "thumbnailUrl" => $thumbnail_file, "deleteUrl" => getAPIUrlS() . "?a=attach_delete&name=" . $filename . "." . $extension, "deleteType" => "DELETE"));
                } else {
                    //bin
                    array_push($result_array['files'], array("name" => $filename . "." . $extension, "size" => $size, "url" => getAPISUrl() . "?a=attach_download&filevalue=" . "$filevalue", "thumbnailUrl" => $thumbnail_file, "deleteUrl" => getAPIUrlS() . "?a=attach_delete&name=" . $filename . "." . $extension, "deleteType" => "DELETE"));
                }

            } else {
                array_push($result_array['files'], array("name" => $filename . "." . $extension, "size" => $size, "error" => $error));
            }
        }


        return $result_array;


    }


    function getAttachCount(){
        return count($_FILES['uploadedfile']['name']);;
    }

    function makeDownloadLink($file_category, $name, $filevalue)
    {
        $image_path = "files/images/";
        $binaries_path = "files/binaries/";


        if ($file_category == "image") {
            //Image
            // return getCoreUrl(true) . "$target_path";
        } else {
            //Binary

        }
    }

    function attach_read(  $user_srl, $category ,$doc_srl, $doc_status, $info)
    {
       // $status = $DOCUMENT_CLASS -> getDocStatus($PAGE_CLASS, $user_srl, $doc_srl);
        $row = Model_Attach_attachRead($category, $doc_srl, $doc_status ,$user_srl);
      //  var_dump($result);
      //  echo "count : ".mysqli_num_rows($result);

        $array = null;

        $total = mysqli_num_rows($row);
        for ($i = 0; $i < $total; $i++) {
            mysqli_data_seek($row, $i);           //포인터 이동
            $result = mysqli_fetch_array($row);        //레코드를 배열로 저장

            //  echo print_info($result, $doc_info);
            $result['url'] = $this->getDownloadUrl($result['kind'], $result['filevalue'] , $result['extension']);
            $array[] = array_info_match($result, $info);
        }



        return $array;
    }

    function deleteAttach($name)
    {
        $result_array = array("files" => array(array($name => true)));
        return $result_array;
    }

    function getDownloadUrl($kind, $filevalue, $extentsion){

        //Check this is image file
        if($kind == "image"){
            return getSiteAddress()."files/images/".$filevalue.".".$extentsion;
        }else{
            // not image
          //  return
            
            return getAPIAddress()."?a=attach&filevalue=".$filevalue;
        }
    }

    function getDocAttachCount($doc_srl)
    {
        $count = $this -> getAttachCount($doc_srl);
        $comment_count = DBQuery("UPDATE `documents` SET `attach` = '$count' WHERE `srl` = '$doc_srl'");
    }

//Require document_class.php

//    function getAttachCount($doc_srl)
//    {
//        $attach_count = DBQuery("SELECT * FROM  `attach` WHERE  `doc_srl` = '$doc_srl' AND `status` != '5'");
//        $total = mysqli_num_rows($attach_count);
//
//        return $total;
//    }

    function AttachDownload($filevalue){
        $attach_info = $this -> getAttachInfoByfileValue($filevalue);
        $path = "files/binaries/".$attach_info['filevalue'];

        $this -> addAttachDownloadCount($attach_info['srl']);



        $filesize = filesize($path);
        $filename = $attach_info['filename'];
//$filename = mb_basename($path);
        if( $this -> CheckIE() ) $filename = $this -> utf2euc($filename);

        header("Pragma: public");
        header("Expires: 0");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$filename".".".$attach_info['extension']."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: $filesize");

        ob_clean();
        flush();
        readfile($path);
    }

    function getAttachInfoByfileValue($filevalue)
    {
        $row = mysqli_fetch_array(DBQuery("SELECT * FROM  `attach` WHERE  `filevalue` LIKE '$filevalue'"));
        return $row;
    }

    function addAttachDownloadCount($attach_srl)
    {
        $attach_info = $this -> getAttachInfoBySrl($attach_srl);
        $result_num = $attach_info['count'] + 1;
        $comment_count = DBQuery("UPDATE `attach` SET  `count` = '$result_num' WHERE `srl` = '$attach_srl'");
    }


    //Deprecated
//    function attach_read_print($DOCUMENT_CLASS, $category, $user_srl, $doc_srl)
//    {
//        //$user_srl = AuthCheck($user_srl, false);
//        $row = $this -> attach_read($DOCUMENT_CLASS, $user_srl, $doc_srl);
//
//        $total = mysqli_num_rows($row);
//        for ($i = 0; $i < $total; $i++) {
//            mysqli_data_seek($row, $i);           //포인터 이동
//            $result = mysqli_fetch_array($row);        //레코드를 배열로 저장
//
//            if ($result['kind'] == "image") {
//                echo getSiteAddress() . "files/images/" . $result['filevalue'] . "." . $result['extension'] . "/LINE/.";
//            }
//
//            if ($result['kind'] == "file") {
//                echo getSiteAddress() . "board/download.php?v=" . $result['filevalue'] . "&n=" . $result['filename'] . "&e=" . $result['extension'] . "/LINE/.";
//            }
//
//        }
//    }

    function getAttachInfoBySrl($srl)
    {
        $row = mysqli_fetch_array(DBQuery("SELECT * FROM  `attach` WHERE  `srl` LIKE '$srl'"));
        return $row;
    }

    function CheckIE() { return isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false; }

    function utf2euc($str) { return iconv("UTF-8","cp949//IGNORE", $str); }

    function mb_basename($path) { return end(explode('/',$path)); }



}
?>
<?php
/**
 * Created by Tarks.
 * User: JHRunning
 */


class AttachApiClass{


    function API_DownloadAttach($user_srl){
        
      //  $PAGE_CLASS = new PageClass();
        $ATTACH_CLASS = new AttachClass();

        $filevalue = REQUEST('filevalue');


       $ATTACH_CLASS -> AttachDownload($filevalue);


    }


    function API_UploadAttach($user_srl)
    {

        //  $PAGE_CLASS = new PageClass();
        $ATTACH_CLASS = new AttachClass();


        echo EncodeJson($ATTACH_CLASS->attach_file('square', 0, 0, $user_srl, 0));

    }


    function API_DeleteAttach($user_srl)
    {

        $ATTACH_CLASS = new AttachClass();
        $name = REQUEST('name');

        echo EncodeJson($ATTACH_CLASS->deleteAttach($name));
    }





}


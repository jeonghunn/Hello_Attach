<?php
/**
 *
 * User: JHRunning
 * Date: 2/10/15
 * Time: 2:32 PM
 */


if($ACTION == "attach") $AttachAPI -> API_DownloadAttach($user_srl);
if($ACTION == "attach_download") $AttachAPI -> API_DownloadAttach($user_srl);
if ($ACTION == "attach_upload") $AttachAPI->API_UploadAttach($user_srl);
if ($ACTION == "attach_delete") $AttachAPI->API_DeleteAttach($user_srl);
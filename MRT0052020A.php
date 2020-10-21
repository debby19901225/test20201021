<?php
// +-------------------------------------------------------------------------------+
// | Enterprise Information Portal                                                 |
// +-------------------------------------------------------------------------------+
// | Copyright (c) 2019 Tungs' Taichung MetroHarbor Hospital All Rights Reserved.  |
// +-------------------------------------------------------------------------------+
// | MRT0052020A 學員臨床學習案例教學與討論紀錄表                                     |
// | MRT0052020A_OK                                                                |
// +-------------------------------------------------------------------------------+
// | Authors: t13446 <t13446@ms.sltung.com.tw>                                     |
// +-------------------------------------------------------------------------------+
//
//    $Id: MRT0052020A.php,v 1.0 2020/10/02 09:58:00 t13446 Exp $
require_once 'dcx.WebUI.Form.php';
require_once 'dcx.WebUI.Grid.php';
require_once 'dc.Eip.Common.php';
require_once 'PSEPLib.php';
class MRT0052020A extends UIBase
{
    // 宣告全域變數：資料庫連線
    public $eipplugin_pdo = null;
    // 預設資料檢查沒有錯誤
    public $isError = 0;
    // 預設沒有錯誤欄位
    public $errorArr = array();
    // 欄位化變數
    public $filedArr = array();
    // 執行新增SQL語法的陣列
    public $insArr = array();
    // 執行修改SQL語法的陣列
    public $updArr = array();
    // 主檔mst_no
    public $mst_no = null;
    // 編輯模式
    private $editForm;
    // 觀看模式
    private $viewForm;
    // PDF模式
    private $PDFForm;
    // 表格顯示時機
    private $showTime;
    // 當前編輯者身分
    public $idTyp;
    // 當前表單狀態
    public $FormTsc;
    // 當前執行任務號
    public $Task_no;
    // 當前執行人員員工號
    public $chkUserNo;
    // HTML 網頁
    public $content;
    // DNS的變數
    public $DNS;

    /* 上方為固定參數 */
    // 是否
    private $ynArr;
    // 未填清單 2018-01-17
    public $nullList;

    //建立資料庫連線
    public function init ()
    {
        ini_set('memory_limit', '256M');
        $this->eipplugin_pdo = tapi::DBLink('EIPPLUGIN');
        $this->site_map = false;

        // 引入資料檔
        include_once 'data.php';

        $pkey = $this->param['pkey'];
        // 設定MST_NO
        $this->mst_no = $pkey;

        // 確認本次狀態執行人員
        $this->chkUserNo = PSEPLib::checkUser('A' ,$this->param['pkey']);
        PSEPLib::setDNS();
    }
    public function ReturnPDF ($dir)
    {
        // 取得該作業所有資料
        $myData = PSEPLib::getSNData($this->mst_no);
        // 設定畫面
        $this->setFrom($myData);
        // 設定PDF畫面
        $this->mkPDFhtml();
        // 產出作業
        PSEPLib::exportToPDF($this->content,$this->mst_no,"F","",$dir);
    }
    public function doViewPDF ()
    {
        $pkey = $this->param['pkey'];
        // 設定MST_NO
        $this->mst_no = $pkey;
        // 取得該作業所有資料
        $myData = PSEPLib::getSNData($this->mst_no);
        // 設定畫面
        $this->setFrom($myData);
        // 設定PDF畫面
        $this->mkPDFhtml();
        // 產出作業
        PSEPLib::exportToPDF($this->content,$this->mst_no,"I");
    }
    public function doDownPDF ()
    {
        $pkey = $this->param['pkey'];
        // 設定MST_NO
        $this->mst_no = $pkey;
        // 取得該作業所有資料
        $myData = PSEPLib::getSNData($this->mst_no);
        // 設定畫面
        $this->setFrom($myData);
        // 設定PDF畫面
        $this->mkPDFhtml();
        // 產出作業
        PSEPLib::exportToPDF($this->content,$this->mst_no,"D");
    }
    public function doDataReBcak()
    {
        $pkey = $this->param['pkey'];
        //  取得MST_NO
        $this->mst_no = $pkey;
        //  Transaction 開始
        try {
            $this->eipplugin_pdo->beginTransaction();
            PSEPLib::rebackTASK($this->param['rebackMsg']);
            $this->eipplugin_pdo->commit();
            $this->output($this->MsgBox('退回成功!'));
            $this->output($this->script('parent.location.reload();'));
        } catch (Exception $e) {
            $this->eipplugin_pdo->rollBack();
            $this->output($this->MsgBox('發生例外錯誤'));
        }
    }

    public function doDataModify ()
    {
        $pkey = $this->param['pkey'];
        //  取得MST_NO
        $this->mst_no = $pkey;

        $this->setShowTime($this->mst_no);
        $FormSts = PSEPLib::getFormSts($this->param['pkey']);
        $chkArr = array();

        // 判斷空值
        $this->isError = 0;
        /*****************修改部分*******************/
        if($this->showTime["109240000_00000000"] == $FormSts) {
            $chkArr = array("fid_109240100_00000000","fid_109240200_00000000","fid_109240300_00000000","fid_109240400_00000000","fid_109240500_00000000","fid_109240600_00000000",
                            "fid_109240700_00000000");
        }
        if($this->showTime["109240800_00000000"] == $FormSts) {
            $chkArr = array("fid_109240800_00000000");
        }
        /*****************修改部分*******************/
        $this->chkNull($chkArr);
        // 顯示未填欄位
        //var_dump($this->nullList);
        if($this->chkUserNo != $_SESSION['DC_EMP_SN']) {
            $this->isError = 2;
            array_push($this->errorArr, '99');
        }
        // 2017-08-17
        $cbs = PSEPLib::chkBySelf();
        if($cbs && $this->isError == 0) {
            if($this->param['setTch']) {
                PSEPLib::setTchBySelf($this->param['setTch']);
            } else {
                $this->isError = 3;
                array_push($this->errorArr, '98');
            }
        }
        if($this->isError == 0) {
            try {
                //  Transaction 開始
                $this->eipplugin_pdo->beginTransaction();
                //  拆解出欄位化的資料
                $this->pickFiled($this->param);
                //  清空修改 && 新增陣列
                $this->insArr = array();
                $this->updArr = array();
                // 上傳檔案加綁定UNO
                $this->uploadfile();
                //  判斷修改欄位或者是新增欄位
                PSEPLib::chkInsUpd();
                //  更新描述檔
                PSEPLib::updSQL();
                //  新增描述檔
                PSEPLib::insSQL();
                // 改變狀態
                PSEPLib::changFormTsc();
                // 發代辦事項到新執行的人員
                PSEPLib::makTask('A' ,$this->mst_no);
                //  Transaction 結束
                $this->eipplugin_pdo->commit();
                if(PSEPLib::checkSign()) {
                    $this->output($this->MsgBox('繳交成功!\n該作業尚有簽核流程需執行！'));
                } else {
                    $this->output($this->MsgBox('繳交成功!'));
                    $this->output($this->script('parent.location.reload();'));
                }
            } catch (Exception $e) {
                $this->eipplugin_pdo->rollBack();
                $this->output($this->MsgBox('發生例外錯誤'));
            }
        } else {
            if($this->isError == 1) {
                $msg = '請確認所有欄位是否填妥';
            } else {
                // 2017-08-17
                $chkDescArr[98] = '請設定批改教師';
                $chkDescArr[99] = '該表單目前並非您可以填寫，請確認。';
                $msg = '';
                foreach ( $this->errorArr as $k => $v) {
                    $msg .= $chkDescArr[$v] .'\n';
                }
            }
            $this->output($this->MsgBox($msg));
        }
    }
    public function doDataTemp ()
    {
        $pkey = $this->param['pkey'];
        //  取得MST_NO
        $this->mst_no = $pkey;

        $this->setShowTime($this->mst_no);
        $FormSts = PSEPLib::getFormSts($this->param['pkey']);
        if($this->chkUserNo != $_SESSION['DC_EMP_SN']) {
            $this->isError = 2;
            array_push($this->errorArr, '99');
        }
        if($this->isError == 0) {
            try {
                //  Transaction 開始
                $this->eipplugin_pdo->beginTransaction();
                //  拆解出欄位化的資料
                $this->pickFiled($this->param);
                //  清空修改 && 新增陣列
                $this->insArr = array();
                $this->updArr = array();
                // 上傳檔案加綁定UNO
                $this->uploadfile();
                //  判斷修改欄位或者是新增欄位
                PSEPLib::chkInsUpd();
                //  更新描述檔
                PSEPLib::updSQL();
                //  新增描述檔
                PSEPLib::insSQL();
                //  Transaction 結束
                $this->eipplugin_pdo->commit();
                $this->output($this->MsgBox('暫存成功!'));
                $this->output($this->script('parent.location.reload();'));
            } catch (Exception $e) {
                $this->eipplugin_pdo->rollBack();
                $this->output($this->MsgBox('發生例外錯誤'));
            }
        } else {
            $chkDescArr[99] = '該表單目前並非您可以填寫，請確認。';
            $msg = '';
            foreach ( $this->errorArr as $k => $v) {
                $msg .= $chkDescArr[$v] .'\n';
            }
            $this->output($this->MsgBox($msg));
        }
    }
    public function doSign() {
        PSEPLib::doSign($this->param['msg']);
    }
    //主程式
    public function view ()
    {
        // 確認本次狀態執行人員
        $this->chkUserNo = PSEPLib::checkUser('A' ,$this->param['pkey']);
        // 參數處理
        $this->pcsParam();
        // 邏輯處理
        $this->pcsLogic();
        // 顯示的功能頁面
        $this->setContainer();
    }
    /**
     * 參數處理
     */
    private function pcsParam ()
    {
        if ($this->param['btnClear']) {
            unset($this->param);
        }
    }
    /**
     * 程式邏輯處理：檢核條件
     */
    private function pcsLogic ()
    {
        //判斷目前頁面：編輯、新增、查詢W
        if($this->param['pkey']){
            //修改資料
            if($this->chkUserNo == $_SESSION['DC_EMP_SN']) {
                if($this->param['popWin']) {
                    $this->funcPage = "pop";
                    $this->execEvent("btnReBack", "doDataReBcak");
                } else {
                    $this->funcPage = "edit";
                    $this->execEvent("btnEdit", "doDataModify");
                    $this->execEvent("btnTemp", "doDataTemp");
                }
            } else if ($this->chkUserNo == '0'){
                //108/11/07 12948 開啟pdf用開窗模式
                if ($this->param['btnPDF1']){
                    $this->doViewPDF();
                }
                $this->execEvent("btnPDF2", "doDownPDF");
                $this->execEvent("btnSign", "doSign");
                $this->funcPage = "view";
            } else {
                $this->funcPage = "view";
            }
        } else {
            $this->funcPage = "error";
        }
    }
    private function setContainer ()
    {
        $this->pack(EipCommon::selfDiv());
        $this->pack($this->start("frm"));
        $space = str_repeat('&emsp;', 20);
        switch ($this->funcPage) {
            case 'add':
                break;
            case 'edit':
                include_once 'dcx.WebUI.PopWindow.php';
                $PopWindow = new UIPopWindow("退回作業", "", 700, 150, false); // 資料編輯
                $this->pack($PopWindow);

                $myData = PSEPLib::getSNData($this->param['pkey']);
                $this->setShowTime($this->param['pkey']);
                $FormSts = PSEPLib::getFormSts($this->param['pkey']);
                $this->setFrom($myData);
                $Form = new UIForm();
                $Form->setWidth("100%");
                // 2017-08-17
                $cbs = PSEPLib::chkBySelf();
                if($cbs) {
                    $Form->setHData ('指定批改教師', '', $this->text_employee("TNAME", "setTch", $this->param['TNAME'], $this->param['setTch'], 20, false) , true);
                }
                /*****************修改部分*******************/
                $Form->setTitleBar('學員臨床學習案例教學與討論紀錄表');
                //備註
                $html = $this->getMemo();
                if ($html) {
                    $Form->setBlank($html, "left");
                }
                //edit
                if ($this->showTime["109240000_00000000"] <= $FormSts) {
                    $Form->setHData ('學習部門', '', '影像醫學部放射診斷科' , false);
                    $Form->setHData ('討論案例編號', '', ($this->showTime['109240100_00000000'] == $FormSts) ? $this->editForm['109240100_00000000'] : $this->viewForm['109240100_00000000'] , false);
                    $Form->setHData ('學員', '', ($this->showTime['109240200_00000000'] == $FormSts) ? $this->editForm['109240200_00000000'] : $this->viewForm['109240200_00000000'] , false);
                    $Form->setHData ('日期', '', ($this->showTime['109240300_00000000'] == $FormSts) ? $this->editForm['109240300_00000000'] : $this->viewForm['109240300_00000000'] , false);
                    $Form->setHData ('討論項目', '', ($this->showTime['109240400_00000000'] == $FormSts) ? $this->editForm['109240400_00000000'] : $this->viewForm['109240400_00000000'] , false);
                    $Form->set2HData('討論內容[含特殊病例、不懂案例、加強指導案例]', '', ($this->showTime['109240500_00000000'] == $FormSts) ? $this->editForm['109240500_00000000'] : $this->viewForm['109240500_00000000'] , false,
                                     '討論內容（圖片附件）', '', ($this->showTime['109240501_00000000'] == $FormSts) ? $this->editForm['109240501_00000000'] : $this->viewForm['109240501_00000000'] , false);
                    $Form->set2HData('指導內容', '', ($this->showTime['109240600_00000000'] == $FormSts) ? $this->editForm['109240600_00000000'] : $this->viewForm['109240600_00000000'] , false,
                                     '指導內容（圖片附件）', '', ($this->showTime['109240601_00000000'] == $FormSts) ? $this->editForm['109240601_00000000'] : $this->viewForm['109240601_00000000'] , false);
                    $Form->set2HData('討論結果', '', ($this->showTime['109240700_00000000'] == $FormSts) ? $this->editForm['109240700_00000000'] : $this->viewForm['109240700_00000000'] , false,
                                     '討論結果（圖片附件）', '', ($this->showTime['109240701_00000000'] == $FormSts) ? $this->editForm['109240701_00000000'] : $this->viewForm['109240701_00000000'] , false);
                }
                if ($this->showTime["109240800_00000000"] <= $FormSts) {
                    $Form->setHData ('評核', '', ($this->showTime['109240800_00000000'] == $FormSts) ? $this->editForm['109240800_00000000'] : $this->viewForm['109240800_00000000'] , false);
                }
                /*****************修改部分*******************/
                $reback = '';
                if($this->idTyp == 'B' && $this->FormTsc != '1') {
                    $reback = '&nbsp;&nbsp;' . $this->button("popWin",'退回作業', "onClick='".$PopWindow->getScript($this->makeUrl(array('popWin' => 'on','pkey' => $this->mst_no)))."'");
                }
                $send = PSEPLib::setSendBtn();
                $Form->setBlank($send . '&nbsp;&nbsp;' . $this->submit("btnTemp", '暫存') . '&nbsp;&nbsp;' . $this->button("Cancel", DC_UI_CANCEL, "onClick='parent.selfDivOff();' ") . $reback);
                $this->pack($Form);
                // 退回意見
                $this->pack('</br></br>');
                $rebackData = PSEPLib::getRebackData($this->mst_no);
                $this->pack($rebackData);

                // 未填選項 2018-01-17
                $this->pack($this->script(PSEPLib::setUnWriRed($this->nullList)));
                break;
            case 'view':
                $myData = PSEPLib::getSNData($this->param['pkey']);
                $this->setShowTime($this->param['pkey']);
                $FormSts = PSEPLib::getFormSts($this->param['pkey']);
                $FormSts = ($FormSts == 'F') ? 99 : (int) $FormSts - 1;
                $this->setFrom($myData);
                
                $Form = new UIForm();
                $Form->setWidth("100%");
                //108/11/07 12948 關閉改為回前一頁
                $Form->setTitleBar($this->button("Cancel", '回前一頁', "onclick='parent.selfDivOff();'"));
                //108/11/07 12948 預覽改為開窗模式
                $btnPDF1 = $this->button("btnPDF1", '預覽', "onclick=window.open('".$this->makeUrl(array("btnPDF1" => "1","pkey"=> $this->param['pkey']))."')");
                $btnPDF2 = $this->submit("btnPDF2", '下載', "onclick='submit_object=\"\";'");
                $btnPDF = $btnPDF1 . '&nbsp;&nbsp;' . $btnPDF2;
                /*****************修改部分*******************/
                $title = '學員臨床學習案例教學與討論紀錄表';
                /*****************修改部分*******************/
                $Form->setBlank('<table width=100% cellpadding=0 cellspacing=0 bgcolor="#3668B1"><tr height="24"><td>&nbsp;<img src="themes/default/form_001.gif" align="absmiddle">&nbsp;&nbsp;<font color="#FFFFFF"><b>'.$title.'</b></font></td><td align=right>'.$btnPDF.'</td></tr></table>');
                /*****************修改部分*******************/
                //備註
                $html = $this->getMemo();
                if ($html) {
                    $Form->setBlank($html, "left");
                }
                if ($this->showTime["109240000_00000000"] <= $FormSts) {
                    $Form->setHData ('學習部門', '', '影像醫學部放射診斷科' , false);
                    $Form->setHData ('討論案例編號', '', $this->viewForm['109240100_00000000'] , false);
                    $Form->setHData ('學員', '', $this->viewForm['109240200_00000000'] , false);
                    $Form->setHData ('日期', '', $this->viewForm['109240300_00000000'] , false);
                    $Form->setHData ('討論項目', '', $this->viewForm['109240400_00000000'] , false);
                    $Form->set2HData('討論內容[含特殊病例、不懂案例、加強指導案例]', '', $this->viewForm['109240500_00000000'] , false,
                                     '討論內容（圖片附件）', '', $this->viewForm['109240501_00000000'] , false);
                    $Form->set2HData('指導內容', '', $this->viewForm['109240600_00000000'] , false,
                                     '指導內容（圖片附件）', '', $this->viewForm['109240601_00000000'] , false);
                    $Form->set2HData('討論結果', '', $this->viewForm['109240700_00000000'] , false,
                                     '討論結果（圖片附件）', '', $this->viewForm['109240701_00000000'] , false);
                }
                if ($this->showTime["109240800_00000000"] <= $FormSts) {
                    $Form->setHData ('評核', '', $this->viewForm['109240800_00000000'] , false);
                }
                /*****************修改部分*******************/
                $this->pack($Form);
                // 退回意見
                $this->pack('</br></br>');
                $rebackData = PSEPLib::getRebackData($this->mst_no);
                $this->pack($rebackData);
                $signData = PSEPLib::getSignMsg($this->mst_no);
                $this->pack($signData);
                PSEPLib::setSignForm($this->param["msg"]);
                break;
            case 'pop':
                $reback = '<input type="submit" name="btnReBack" onclick="if(!confirm(\'是否真的要退回作業？\')){return false;}" value="退回重寫"/>';
                $Form = new UIForm();
                $Form->setWidth("100%");
                $Form->setHData ('退回意見', '', $this->textarea ("rebackMsg", (isset($myData["rebackMsg"])) ? $myData["rebackMsg"] : $this->param["rebackMsg"],60,4), false);
                $Form->setBlank($reback);
                $this->pack($Form);
                break;
            case 'error':
                $this->pack('</br></br></br><p align="center" style="font-family:Microsoft JhengHei,Times New Roman;white-space: pre-wrap;">請勿進行非法操作</p>');
                break;
            default:
                $this->pack('</br></br></br><p align="center" style="font-family:Microsoft JhengHei,Times New Roman;white-space: pre-wrap;">查無此頁面！</p>');
                break;
        }
        $this->pack($this->end());
    }
    /**
     * 篩選出欄位化欄位
     */
    private function pickFiled ($input)
    {
        $FormSts = PSEPLib::getFormSts($this->param['pkey']);
        $this->setShowTime($this->param['pkey']);
        foreach ($this->showTime as $k => $v) {
            if($v == $FormSts) {
                $this->filedArr[$k] = '';
            }
        }
        foreach ($input as $k => $v) {
            if(preg_match("/fid_/i",$k)) {
                $str = explode('_' , $k);
                $this->filedArr[$str[1] . '_' . $str[2] ] = $v;
            }
        }
    }
    /**
     *  檢查空值
     */
    private function chkNull($checkArr) {
        foreach ($checkArr as $k => $v) {
            if ($v == 'fid_109240104_00000000' || $v == 'fid_109240201_03000000') {
                $condition = (!$this->param[$v] || $this->param[$v] == '0') ? true : false;
            } else {
                $condition = (!$this->param[$v] && $this->param[$v] != '0') ? true : false;
            }
            if ($condition) {
                $this->isError = 1;
                array_push($this->errorArr, $k);
                // 2018-01-17
                $this->nullList[] = $v;
            }
        }
    }
    /**
     * 設定顯示時機
     */
    private function setShowTime($mst_no) {

        $SQL = 'select b.TBL_REL,b.MSR_NO,b.DET_NO,b.FIB_SHOW
                  from EP_SMI a, EP_FIB b
                 where a.MST_NO = :MST_NO
                   and a.FORM_NO = b.FORM_NO';
        $stmt = $this->eipplugin_pdo->prepare($SQL);
        $stmt->bindParam(":MST_NO", $mst_no, PDO::PARAM_STR);
        $stmt->execute();
        $selectData = array();
        while ($list = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($list as $k => $v) {
                $list[$k] = trim($v);
            }
            $this->showTime[$list['MSR_NO'].'_'.$list['DET_NO']] = $list['FIB_SHOW'];
        }
    }
    /************** 上傳FM模組 **************/
    /**
     * 上傳檔案用 若無則為空
     */
    private function uploadfile() {
        // PSEPLib::uploadFMdata(欄位名稱,欄位化命名);
        // ex PSEPLib::uploadFMdata('fileA','108030000_00000000');
        PSEPLib::uploadFMdata('file1','109240501_00000000');    
        PSEPLib::uploadFMdata('file2','109240601_00000000');    
        PSEPLib::uploadFMdata('file3','109240701_00000000');    
    }
    /**
     * 產生PDF畫面
     */
    private function mkPDFhtml()
    {
        //建立表格內文
        $this->content  = ' </br><div align="center"><img src = "http://tfshap.sltung.com.tw/TFS/service/gFile.php?rew=500&q=MEA5RWxYDABpa|EA8AWl8WAQBTAQIEUQEAVQBUUgNQBlsJA0RY" height="30" width="200"></div>';
        /*****************修改部分*******************/
        $this->content .= ' <table border="1" width="100%" style="font-size:12px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td>
                                        <table border="0" width="100%" style="font-size:12px;" cellpadding="0" cellspacing="0">                        
                                            <tr>
                                                <td width="100%" style="font-size:20px;font-weight:bold;" align="center">學員臨床學習案例教學與討論紀錄表</td>
                                            </tr>
                                            <tr>
                                                <td width="4%" style="font-size:14px;">◎</td>
                                                <td width="96%">學員臨床實習案例教學與討論，亦可以CbD評核表予以評核紀錄。</td>
                                            </tr>
                                            <tr>
                                                <td width="4%" style="font-size:14px;">◎</td>
                                                <td width="96%">本表可自行影印使用。進用單位為放射診斷科者，至少5篇，請務必尊重病患隱私，遵守醫院相</td>
                                            </tr>
                                            <tr>
                                                <td width="4%"></td>
                                                <td width="96%">關規定。</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="100%">學習部門：影像醫學部放射診斷科</td>
                                </tr>
                                <tr>
                                    <td width="35%">討論案例編號：'.$this->PDFForm['109240100_00000000'].'</td>
                                    <td width="40%">學員：'.$this->PDFForm['109240200_00000000'].'</td>
                                    <td width="25%">日期：'.$this->PDFForm['109240300_00000000'].'</td>
                                </tr>
                                <tr>
                                    <td width="100%">討論項目：<br>'.$this->PDFForm['109240400_00000000'].'</td>
                                </tr>
                                <tr>
                                    <td width="100%">討論內容[含特殊病例、不懂案例、加強指導案例]：<br>'.$this->PDFForm['109240500_00000000'].'<br>'.$this->PDFForm["109240501_00000000"].'</td>
                                </tr>
                                <tr>
                                    <td width="100%">指導內容：<br>'.$this->PDFForm['109240600_00000000'].'<br>'.$this->PDFForm["109240601_00000000"].'</td>
                                </tr>
                                <tr>
                                    <td width="100%">討論結果：<br>'.$this->PDFForm['109240700_00000000'].'<br>'.$this->PDFForm["109240701_00000000"].'</td>
                                </tr>
                                <tr>
                                    <td width="100%">評核：<br>'.$this->PDFForm['109240800_00000000'].'<br><span align="right">臨床指導教師(簽名)：'.PSEPLib::getSignData($this->mst_no,2).'</span></td>
                                </tr>
                            </table>';
        /*****************修改部分*******************/
    }
    /**
     *  設定編輯模式&顯示模式&PDF模式的畫面
     */
    private function setFrom($myData) {
        if($this->isPost && !$this->param['btnPDF1'] && !$this->param['btnPDF2']) {
            foreach ($this->param as $k => $v) {
                $myData[$k] = $v;
            }
        }
        if(!$this->isPost) {
            PSEPLib::setDefault($myData);
        }
        /*****************修改部分*******************/
        // Edit Mode
        //討論案例編號
        $this->editForm["109240100_00000000"] = $this->text ("fid_109240100_00000000", $myData["fid_109240100_00000000"]);
        //學員
        $this->editForm["109240200_00000000"] = $this->text ("fid_109240200_00000000", $myData["fid_109240200_00000000"]);
        //日期
        $this->editForm["109240300_00000000"] = $this->calendar("fid_109240300_00000000", $myData["fid_109240300_00000000"], true, "", "BOTTOM", "", "2999-12-31", "", false);
        //討論項目
        $this->editForm["109240400_00000000"] = $this->textarea ("fid_109240400_00000000", $myData["fid_109240400_00000000"],60,4);
        //討論內容[含特殊病例、不懂案例、加強指導案例]
        $this->editForm["109240500_00000000"] = $this->textarea ("fid_109240500_00000000", $myData["fid_109240500_00000000"],60,4);
        //討論內容:附件
        $uploadFile = ($myData["fid_109240501_00000000"]) ? PSEPLib::fileView($this->DNS,$myData["fid_109240501_00000000"],'附件1') : '';
        $this->editForm['109240501_00000000'] = '<input type="file" id="file1" name="file1">'.$uploadFile;
        //指導內容
        $this->editForm["109240600_00000000"] = $this->textarea ("fid_109240600_00000000", $myData["fid_109240600_00000000"],60,4);
        //指導內容:附件
        $uploadFile = ($myData["fid_109240601_00000000"]) ? PSEPLib::fileView($this->DNS,$myData["fid_109240601_00000000"],'附件2') : '';
        $this->editForm['109240601_00000000'] = '<input type="file" id="file2" name="file2">'.$uploadFile;
        //討論結果
        $this->editForm["109240700_00000000"] = $this->textarea ("fid_109240700_00000000", $myData["fid_109240700_00000000"],60,4);
        //討論結果:附件
        $uploadFile = ($myData["fid_109240701_00000000"]) ? PSEPLib::fileView($this->DNS,$myData["fid_109240701_00000000"],'附件3') : '';
        $this->editForm['109240701_00000000'] = '<input type="file" id="file3" name="file3">'.$uploadFile;
        //評核
        $this->editForm["109240800_00000000"] = $this->textarea ("fid_109240800_00000000", $myData["fid_109240800_00000000"],60,4);
        //臨床指導教師(簽名)
        $this->editForm["109240900_00000000"] = "";

        // View Mode
        //討論案例編號
        $this->viewForm["109240100_00000000"] = $myData["fid_109240100_00000000"];
        //學員
        $this->viewForm["109240200_00000000"] = $myData["fid_109240200_00000000"];
        //日期
        $this->viewForm["109240300_00000000"] = $myData["fid_109240300_00000000"];
        //討論項目
        $this->viewForm["109240400_00000000"] = PSEPLib::showTextArea($myData["fid_109240400_00000000"]);
        //討論內容[含特殊病例、不懂案例、加強指導案例]
        $this->viewForm["109240500_00000000"] = PSEPLib::showTextArea($myData["fid_109240500_00000000"]);
        //討論內容:附件
        $this->viewForm['109240501_00000000'] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240501_00000000"], 150, 150);
        //指導內容
        $this->viewForm["109240600_00000000"] = PSEPLib::showTextArea($myData["fid_109240600_00000000"]);
        //指導內容:附件
        $this->viewForm['109240601_00000000'] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240601_00000000"], 150, 150);
        //討論結果
        $this->viewForm["109240700_00000000"] = PSEPLib::showTextArea($myData["fid_109240700_00000000"]);
        //討論結果:附件
        $this->viewForm['109240701_00000000'] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240701_00000000"], 150, 150);
        //評核
        $this->viewForm["109240800_00000000"] = PSEPLib::showTextArea($myData["fid_109240800_00000000"]);
        //臨床指導教師(簽名)
        $this->viewForm["109240900_00000000"] = "";

        // PDF Mode
        //討論案例編號
        $this->PDFForm["109240100_00000000"] = $myData["fid_109240100_00000000"];
        //學員
        $this->PDFForm["109240200_00000000"] = $myData["fid_109240200_00000000"];
        //日期
        $this->PDFForm["109240300_00000000"] = $myData["fid_109240300_00000000"];
        //討論項目
        $this->PDFForm["109240400_00000000"] = PSEPLib::showTextArea($myData["fid_109240400_00000000"]);
        //討論內容[含特殊病例、不懂案例、加強指導案例]
        $this->PDFForm["109240500_00000000"] = PSEPLib::showTextArea($myData["fid_109240500_00000000"]);
        //討論內容:附件
        $this->PDFForm["109240501_00000000"] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240501_00000000"], 150, 150);
        //指導內容
        $this->PDFForm["109240600_00000000"] = PSEPLib::showTextArea($myData["fid_109240600_00000000"]);
        //指導內容:附件
        $this->PDFForm["109240601_00000000"] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240601_00000000"], 150, 150);
        //討論結果
        $this->PDFForm["109240700_00000000"] = PSEPLib::showTextArea($myData["fid_109240700_00000000"]);
        //討論結果:附件
        $this->PDFForm["109240701_00000000"] = PSEPLib::imgPDFView($this->DNS,$myData["fid_109240701_00000000"], 150, 150);
        //評核
        $this->PDFForm["109240800_00000000"] = PSEPLib::showTextArea($myData["fid_109240800_00000000"]);
        //臨床指導教師(簽名)
        $this->PDFForm["109240900_00000000"] = "";
        /*****************修改部分*******************/
    }    
    /** 
     * 設定備註
     */
    private function getMemo ()
    {
        $Help = array();
        $Help[] = '學員臨床實習案例教學與討論，亦可以CbD評核表予以評核紀錄。';
        $Help[] = '本表可自行影印使用。進用單位為放射診斷科者，至少5篇，請務必尊重病患隱私，遵守醫院相關規定。';
        $html = "<table>";
        if (is_array($Help)) {
            foreach ($Help as $m) {
                $html .= "<tr>
                              <td width=10 valign=top><img src=".DCX_THEME_PATH."/form_005.gif></td>
                              <td style='font-size:14px;'>$m</td>
                          </tr>";
            }
        }
        $html .= "</table>";
        return $html;
    }
}
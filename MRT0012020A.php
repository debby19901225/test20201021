<?php
// +-------------------------------------------------------------------------------+
// | Enterprise Information Portal                                                 |
// +-------------------------------------------------------------------------------+
// | Copyright (c) 2019 Tungs' Taichung MetroHarbor Hospital All Rights Reserved.  |
// +-------------------------------------------------------------------------------+
// | MRT0012020A 臨床放射影像檢查學習前評核表                                         |
// | MRT0012020A_OK                                                                |
// +-------------------------------------------------------------------------------+
// | Authors: t13446 <t13446@ms.sltung.com.tw>                                     |
// +-------------------------------------------------------------------------------+
//
//    $Id: MRT0012020A.php,v 1.0 2020/09/28 08:15:00 t13446 Exp $
require_once 'dcx.WebUI.Form.php';
require_once 'dcx.WebUI.Grid.php';
require_once 'dc.Eip.Common.php';
require_once 'PSEPLib.php';
class MRT0012020A extends UIBase
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
    private $scoreArr = array('5'=>'A','4'=>'B','3'=>'C','2'=>'D','1'=>'E');
    private $typeArr  = array('1'=>'一般攝影檢查', '2'=>'特殊攝影檢查', '3'=>'品質管理', '4'=>'異常事件數據');
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
        if($this->showTime["109070000_00000000"] == $FormSts) {
            $chkArr = array("fid_109070101_00000000","fid_109070102_00000000","fid_109070201_01010200","fid_109070201_01020200","fid_109070201_01030200","fid_109070201_02010200",
                            "fid_109070201_02020200","fid_109070201_02030200","fid_109070201_03010200","fid_109070201_03020200","fid_109070201_03030200","fid_109070201_04010200",
                            "fid_109070202_00000000","fid_109070300_00000000");
            $chkArr2 = array();
            $chkArr2[0] = array("fid_109070201_01010101","fid_109070201_01010102","fid_109070201_01010103","fid_109070201_01010104","fid_109070201_01010105");
            $chkArr2[1] = array("fid_109070201_01020101","fid_109070201_01020102","fid_109070201_01020103","fid_109070201_01020104","fid_109070201_01020105");
            $chkArr2[2] = array("fid_109070201_01030101","fid_109070201_01030102","fid_109070201_01030103","fid_109070201_01030104","fid_109070201_01030105");
            $chkArr2[3] = array("fid_109070201_02010101","fid_109070201_02010102","fid_109070201_02010103","fid_109070201_02010104","fid_109070201_02010105");
            $chkArr2[4] = array("fid_109070201_02020101","fid_109070201_02020102","fid_109070201_02020103","fid_109070201_02020104","fid_109070201_02020105");
            $chkArr2[5] = array("fid_109070201_02030101","fid_109070201_02030102","fid_109070201_02030103","fid_109070201_02030104","fid_109070201_02030105");
            $chkArr2[6] = array("fid_109070201_03010101","fid_109070201_03010102","fid_109070201_03010103","fid_109070201_03010104","fid_109070201_03010105");
            $chkArr2[7] = array("fid_109070201_03020101","fid_109070201_03020102","fid_109070201_03020103","fid_109070201_03020104","fid_109070201_03020105");
            $chkArr2[8] = array("fid_109070201_03030101","fid_109070201_03030102","fid_109070201_03030103","fid_109070201_03030104","fid_109070201_03030105");
            $chkArr2[9] = array("fid_109070201_04010101","fid_109070201_04010102","fid_109070201_04010103","fid_109070201_04010104","fid_109070201_04010105");
        }
        /*****************修改部分*******************/
        $this->chkNull($chkArr);
        $this->chkoneNull($chkArr2);
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
                $Form->setTitleBar('臨床放射影像檢查學習前評核表');
                //edit
                if($this->showTime["109070000_00000000"] <= $FormSts) {
                    $Form->setHData ('學員姓名', '', ($this->showTime['109070101_00000000'] == $FormSts) ? $this->editForm['109070101_00000000'] : $this->viewForm['109070101_00000000'] , false);
                    $Form->setHData ('評核日期', '', ($this->showTime['109070102_00000000'] == $FormSts) ? $this->editForm['109070102_00000000'] : $this->viewForm['109070102_00000000'] , false);
                    for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
                        $pos = str_pad($a,2,'0',STR_PAD_LEFT);
                        $Form->setHData ('', '', '<span style="font-size:16px;color:blue;"><b>'.$a.'.'.$this->typeArr[$a].'</b></span>' , false);
                        $Form->setHData ('評核內容', '', '<span style="font-size:16px;color:blue;"><b>自評分數</b></span>' , false);
                        for ($b=1 ; $b<=count($this->detailArr[$a]) ; $b++) {
                            $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                            $Form->set2HData ($this->detailArr[$a][$b], '', ($this->showTime['109070201_'.$pos.$pos2.'0100'] == $FormSts) ? $this->editForm['109070201_'.$pos.$pos2.'0100'] : $this->viewForm['109070201_'.$pos.$pos2.'0100'] , false,
                                              '總分', $space, ($this->showTime['109070201_'.$pos.$pos2.'0200'] == $FormSts) ? $this->editForm['109070201_'.$pos.$pos2.'0200'] : $this->viewForm['109070201_'.$pos.$pos2.'0200'] , false);
                        }
                    }
                    $Form->setHData ('學習前自我評量總平均分數', '', ($this->showTime['109070202_00000000'] == $FormSts) ? $this->editForm['109070202_00000000'] : $this->viewForm['109070202_00000000'] , false);
                    $Form->setHData ('學習者需求', ' (特別想學習的項目)', ($this->showTime['109070300_00000000'] == $FormSts) ? $this->editForm['109070300_00000000'] : $this->viewForm['109070300_00000000'] , false);
                }
                /*****************修改部分*******************/
                //備註
                $html = $this->getMemo();
                if ($html) {
                    $Form->setTitleBar('評分說明');
                    $Form->setBlank($html, "left");
                }
                $reback = '';
                if($this->idTyp == 'B' && $this->FormTsc != '1') {
                    $reback = '&nbsp;&nbsp;' . $this->button("popWin",'退回作業', "onClick='".$PopWindow->getScript($this->makeUrl(array('popWin' => 'on','pkey' => $this->mst_no)))."'");
                }
                $send = PSEPLib::setSendBtn();
                $Form->setBlank($send . '&nbsp;&nbsp;' . $this->submit("btnTemp", '暫存') . '&nbsp;&nbsp;' . $this->button("Cancel", DC_UI_CANCEL, "onClick='parent.selfDivOff();' ") . $reback);
                $this->pack($Form);
                $this->pack($this->setScript());
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
                $title = '臨床放射影像檢查學習前評核表';
                /*****************修改部分*******************/
                $Form->setBlank('<table width=100% cellpadding=0 cellspacing=0 bgcolor="#3668B1"><tr height="24"><td>&nbsp;<img src="themes/default/form_001.gif" align="absmiddle">&nbsp;&nbsp;<font color="#FFFFFF"><b>'.$title.'</b></font></td><td align=right>'.$btnPDF.'</td></tr></table>');
                /*****************修改部分*******************/
                if($this->showTime["109070000_00000000"] <= $FormSts) {
                    $Form->setHData ('學員姓名', '', $this->viewForm['109070101_00000000'] , false);
                    $Form->setHData ('評核日期', '', $this->viewForm['109070102_00000000'] , false);
                    $Form->setHData ('評核內容', '', '<span style="font-size:16px;color:blue;"><b>評核項目</b></span>' , false);
                    for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
                        $pos = str_pad($a,2,'0',STR_PAD_LEFT);
                        $Form->setHData ('', '', '<span style="font-size:16px;color:blue;"><b>'.$this->typeArr[$a].'</b></span>' , false);
                        for ($b=1 ; $b<=count($this->detailArr[$a]) ; $b++) {
                            $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                            $Form->set2HData ($this->detailArr[$a][$b], '', $this->viewForm['109070201_'.$pos.$pos2.'0100'] , false,
                                              '總分', $space, $this->viewForm['109070201_'.$pos.$pos2.'0200'] , false);
                        }
                    }
                    $Form->setHData ('學習前自我評量總平均分數', '', $this->viewForm['109070202_00000000'] , false);
                    $Form->setHData ('學習者需求', ' (特別想學習的項目)', $this->viewForm['109070300_00000000'] , false);
                }
                /*****************修改部分*******************/
                //備註
                $html = $this->getMemo();
                if ($html) {
                    $Form->setTitleBar('評分說明');
                    $Form->setBlank($html, "left");
                }
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
            if(!$this->param[$v] && $this->param[$v] != '0') {
                $this->isError = 1;
                array_push($this->errorArr, $k);
                // 2018-01-17
                $this->nullList[] = $v;
            }
        }
    }
    /**
     *  檢查空值 (需選擇至少一個)
     */
    private function chkoneNull ($getArr) {
        if (is_array($getArr) && count($getArr)) {
            foreach ($getArr as $key => $value) {
                $checkArr = $getArr[$key];
                $error = true;
                if (is_array($checkArr) && count($checkArr)) {
                    foreach ($checkArr as $k => $v) {
                        if ($this->param[$v] || $this->param[$v] == '0') {
                            $error = false;
                            break;
                        }
                    }
                    if ($error) {
                        $this->isError = 1;
                        foreach ($checkArr as $k => $v) {
                            array_push($this->errorArr, $k);
                            $this->nullList[] = $v;
                        }
                    }
                }
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
    }
    /**
     * 產生PDF畫面
     */
    private function mkPDFhtml()
    {
        //建立表格內文
        $this->content  = ' </br><div align="center"><img src = "http://tfshap.sltung.com.tw/TFS/service/gFile.php?rew=500&q=MEA5RWxYDABpa|EA8AWl8WAQBTAQIEUQEAVQBUUgNQBlsJA0RY" height="30" width="200"></div>';
        /*****************修改部分*******************/
        $this->content .= ' <table border="0" width="100%" style="font-size:10px;" cellpadding="1" cellspacing="0">
                                <tr>
                                    <td width="100%" align="right">970801 初訂；1.0版</td>
                                </tr>
                                <tr>
                                    <td width="100%" align="right">1020101 修訂；1.1版</td>
                                </tr>
                                <tr>
                                    <td width="100%" align="right">1060702 修訂；1.2版</td>
                                </tr>
                                <tr>
                                    <td width="100%" align="right">1090827 修訂；1.3版</td>
                                </tr>
                            </table>
                            <table border="0" width="100%" style="font-size:20px;" cellpadding="10" cellspacing="0">
                                <tr>
                                    <td width="100%" align="center">臨床放射影像檢查學習前評核表</td>
                                </tr>
                            </table>
                            <table border="0" width="100%" style="font-size:14px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td width="50%">學員姓名：'.$this->PDFForm['109070101_00000000'].'</td>
                                    <td width="50%" align="right">日期：'.$this->PDFForm['109070102_00000000'].'</td>
                                </tr>
                            </table>
                            <table border="1" width="100%" style="font-size:12px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td width="8%"  align="center" rowspan="2" style="line-height:30px;">項次</td>
                                    <td width="19%" align="center" rowspan="2" style="line-height:30px;">評核項目</td>
                                    <td width="40%" align="center" rowspan="2" style="line-height:30px;">評核內容</td>
                                    <td width="25%" align="center" colspan="5">自評分數</td>
                                    <td width="8%"  align="center" rowspan="2" style="line-height:30px;">總分</td>
                                </tr>
                                <tr>
                                    <td width="5%" align="center">A</td>
                                    <td width="5%" align="center">B</td>
                                    <td width="5%" align="center">C</td>
                                    <td width="5%" align="center">D</td>
                                    <td width="5%" align="center">E</td>
                                </tr>
                            ';
        
        for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
            $pos = str_pad($a,2,'0',STR_PAD_LEFT);
            $rowNumb = count($this->detailArr[$a]);
            $lineH = ($a == 4) ? 15 : 50;
            $this->content .= ' <tr>
                                    <td width="8%"  align="center" rowspan="'.$rowNumb.'" style="line-height:'.$lineH.'px;">'.$a.'</td>
                                    <td width="19%" align="center" rowspan="'.$rowNumb.'" style="line-height:'.$lineH.'px;">'.$this->typeArr[$a].'</td>';
            for ($b=1 ; $b<=$rowNumb ; $b++) {
                $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                if ($b != 1) {
                    $this->content .= ' <tr>';
                }
            $this->content .= '     <td width="40%">'.$this->detailArr[$a][$b].'</td>
                                    <td width="5%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0105'].'</td>
                                    <td width="5%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0104'].'</td>
                                    <td width="5%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0103'].'</td>
                                    <td width="5%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0102'].'</td>
                                    <td width="5%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0101'].'</td>
                                    <td width="8%" align="center">'.$this->PDFForm['109070201_'.$pos.$pos2.'0200'].'</td>';
            $this->content .= ' </tr>';
            }
        }      
        $this->content .= '     <tr>
                                    <td width="67%" align="center" style="line-height:30px;">學習前自我評量總平均分數</td>
                                    <td width="25%"></td>
                                    <td width="8%" align="center"  style="line-height:30px;">'.$this->PDFForm['109070202_00000000'].'</td>
                                </tr>
                            </table>
                            <table border="0" width="100%" style="font-size:12px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td></td>
                                </tr>
                            </table>
                            <table border="1" width="100%" style="font-size:12px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td width="100%">學習者需求：<br>'.$this->PDFForm['109070300_00000000'].'</td>
                                </tr>
                            </table>
                            <table border="0" width="100%" style="font-size:14px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td width="14%">全期導師</td>
                                    <td width="86%">：'.PSEPLib::getSignData($this->mst_no,2).'</td>
                                </tr>
                                <tr>
                                    <td width="14%">教學負責人</td>
                                    <td width="86%">：'.PSEPLib::getSupSign('M').'</td>
                                </tr>
                                <tr>
                                    <td width="14%">計畫主持人</td>
                                    <td width="86%">：'.PSEPLib::getSupSign('E').'</td>
                                </tr>
                            </table>
                            <table border="0" width="100%" style="font-size:12px;" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="100%">評分說明：</td>
                                </tr>
                                <tr>
                                    <td width="3%">1.</td>
                                    <td width="97%">以五等級評核，亦可採用「級加分法」，但不採行減扣分法之計算方式，例如：</td>
                                </tr>
                                <tr>
                                    <td width="3%"></td>
                                    <td width="97%">評給86分者，可打為B+6(等於86分)。相對等級如下：</td>
                                </tr>
                                <tr>
                                    <td width="3%"></td>
                                    <td width="97%">A：90分以上、B：80分以上，未滿90分、C：70分以上，未滿80分</td>
                                </tr>
                                <tr>
                                    <td width="3%"></td>
                                    <td width="97%">D：60分以上，未滿70分、E：60分以下。</td>
                                </tr>
                            </table>
                            ';
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
        $this->setDetailData();
        // Edit Mode
        //學員姓名
        $this->editForm["109070101_00000000"] = $this->text ("fid_109070101_00000000", $myData["fid_109070101_00000000"]);
        //評核日期
        $this->editForm["109070102_00000000"] = $this->calendar("fid_109070102_00000000", $myData["fid_109070102_00000000"], true, "", "BOTTOM", "", "2999-12-31", "", false);
        //評核項目
        for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
            $pos = str_pad($a,2,'0',STR_PAD_LEFT);
            if (is_array($this->detailArr[$a])) {
                for ($b=1 ; $b<=count($this->detailArr[$a]) ; $b++) {
                    $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                    $list = '';
                    for ($c=5 ; $c>=1 ; $c--) {
                        $pos3 = str_pad($c,2,'0',STR_PAD_LEFT);
                        if ($c == 1) {
                            $jsOnclick = 'onchange="calScore(this)" onkeyup="ValidateNumber($(this),value,60)"';
                        } else {
                            $jsOnclick = 'onchange="calScore(this)" onkeyup="ValidateNumber($(this),value,9)"';
                        }
                        $this->editForm["109070201_".$pos.$pos2."01".$pos3] = $this->text ("fid_109070201_".$pos.$pos2."01".$pos3, $myData["fid_109070201_".$pos.$pos2."01".$pos3], 1, $jsOnclick);
                        if ($list) {
                            $list .= '，&nbsp;';
                        }
                        $list .= $this->scoreArr[$c].' : '.$this->editForm["109070201_".$pos.$pos2."01".$pos3].' 分';
                    }
                    $this->editForm["109070201_".$pos.$pos2."0100"] = $list;
                    $this->editForm["109070201_".$pos.$pos2."0200"] = $this->text ("fid_109070201_".$pos.$pos2."0200", $myData["fid_109070201_".$pos.$pos2."0200"], 1).' 分';
                }
            }
        }
        //學習前自我評量總平均分數
        $this->editForm["109070202_00000000"] = $this->text ("fid_109070202_00000000", $myData["fid_109070202_00000000"], 5).' 分';
        //學習者需求
        $this->editForm["109070300_00000000"] = $this->textarea ("fid_109070300_00000000", $myData["fid_109070300_00000000"],60,4);
        //全期導師簽名
        $this->editForm["109070400_00000000"] = "";

        // View Mode
        //學員姓名
        $this->viewForm["109070101_00000000"] = $myData["fid_109070101_00000000"];
        //評核日期
        $this->viewForm["109070102_00000000"] = $myData["fid_109070102_00000000"];
        //評核項目
        for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
            $pos = str_pad($a,2,'0',STR_PAD_LEFT);
            if (is_array($this->detailArr[$a])) {
                for ($b=1 ; $b<=count($this->detailArr[$a]) ; $b++) {
                    $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                    $list = '';
                    for ($c=5 ; $c>=1 ; $c--) {
                        $pos3 = str_pad($c,2,'0',STR_PAD_LEFT);
                        $this->viewForm["109070201_".$pos.$pos2."01".$pos3] = $myData["fid_109070201_".$pos.$pos2."01".$pos3];
                        if ($list) {
                            $list .= '，&nbsp;';
                        }
                        $list .= $this->scoreArr[$c].' : '.(($this->viewForm["109070201_".$pos.$pos2."01".$pos3] || $this->viewForm["109070201_".$pos.$pos2."01".$pos3] == '0') ? (($c == 1) ? '-' : '+').$this->viewForm["109070201_".$pos.$pos2."01".$pos3] : 0).' 分';
                    }
                    $this->viewForm["109070201_".$pos.$pos2."0100"] = $list;
                    $this->viewForm["109070201_".$pos.$pos2."0200"] = $myData["fid_109070201_".$pos.$pos2."0200"].' 分';
                }
            }
        }
        //學習前自我評量總平均分數
        $this->viewForm["109070202_00000000"] = $myData["fid_109070202_00000000"].' 分';
        //學習者需求
        $this->viewForm["109070300_00000000"] = PSEPLib::showTextArea($myData["fid_109070300_00000000"]);
        //全期導師簽名
        $this->viewForm["109070400_00000000"] = "";

        // PDF Mode
        //學員姓名
        $this->PDFForm["109070101_00000000"] = $myData["fid_109070101_00000000"];
        //評核日期
        $this->PDFForm["109070102_00000000"] = $myData["fid_109070102_00000000"];
        //評核項目
        for ($a=1 ; $a<=count($this->typeArr) ; $a++) {
            $pos = str_pad($a,2,'0',STR_PAD_LEFT);
            if (is_array($this->detailArr[$a])) {
                for ($b=1 ; $b<=count($this->detailArr[$a]) ; $b++) {
                    $pos2 = str_pad($b,2,'0',STR_PAD_LEFT);
                    for ($c=5 ; $c>=1 ; $c--) {
                        $pos3 = str_pad($c,2,'0',STR_PAD_LEFT);
                        $this->PDFForm["109070201_".$pos.$pos2."01".$pos3] = ($myData["fid_109070201_".$pos.$pos2."01".$pos3] || $myData["fid_109070201_".$pos.$pos2."01".$pos3] == '0') ? (($c == 1) ? '-' : '+').$myData["fid_109070201_".$pos.$pos2."01".$pos3] : '';
                    }
                    $this->PDFForm["109070201_".$pos.$pos2."0200"] = $myData["fid_109070201_".$pos.$pos2."0200"];
                }
            }
        }
        //學習前自我評量總平均分數
        $this->PDFForm["109070202_00000000"] = $myData["fid_109070202_00000000"];
        //學習者需求
        $this->PDFForm["109070300_00000000"] = PSEPLib::showTextArea($myData["fid_109070300_00000000"]);
        //全期導師簽名
        $this->PDFForm["109070400_00000000"] = "";
        /*****************修改部分*******************/
    }
    private function setDetailData ()
    {
        $this->detailArr = array();
        $this->detailArr[1][1] = '1.我對此部儀器分析原理了解程度';
        $this->detailArr[1][2] = '2.我清楚此儀器之分析項目';
        $this->detailArr[1][3] = '3.我會此部儀器之操作和保養';
        $this->detailArr[2][1] = '1.我對此部儀器分析原理了解程度';
        $this->detailArr[2][2] = '2.我清楚此儀器之分析項目';
        $this->detailArr[2][3] = '3.我會此部儀器之操作和基本保養';
        $this->detailArr[3][1] = '1.我知道正常影像與不正常影像圖像';
        $this->detailArr[3][2] = '2.我可以製作並檢視品管圖';
        $this->detailArr[3][3] = '3.我會處理品管異常數據';
        $this->detailArr[4][1] = '我知道危險異常通報程序及其重要性';
    }
    /** 
     * 設定備註
     */
    private function getMemo ()
    {
        $Help = array();
        $Help[] = '1.以五等級評核，A～B等級亦可採用「級加分法」，E 等級採行減扣分法之計算方式。
                        <br>&emsp;例如：評給86分者，可打為B+6(等於86分)。相對等級如下：
                        <br>&emsp;&emsp;A：90分以上
                        <br>&emsp;&emsp;B：80分以上，未滿90分
                        <br>&emsp;&emsp;C：70分以上，未滿80分
                        <br>&emsp;&emsp;D：60分以上，未滿70分
                        <br>&emsp;例如：評給54分者，可打為E-6(等於54分)。相對等級如下：
                        <br>&emsp;&emsp;E：60分以下。';
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
    private function setScript ()
    {
        $script = $this->script("
                // 只能輸入數字
                function ValidateNumber(e, pnumber, limit)
                {
                    let xID = e.id;
                    if (!/^\d+$/.test(pnumber)) {
                        document.getElementById(xID).value = '';
                    } else {
                        if (pnumber > limit) {
                            document.getElementById(xID).value = '';
                        }
                    }
                }
                // 計算總分
                function calScore(xthis) {
                    let xID  = xthis.id;
                    let xID1 = xID.substring(0,18) + '01';
                    let xID2 = xID.substring(0,18) + '0200';
                    let xID3 = xID.substr(-2);
                    var xBasic = 0;
                    var xTotal = 0;
                    document.querySelectorAll(\"[id^='\"+ xID1 +\"']\").forEach(el=>{
                        if (el.id == xID) {
                            if (xID3 == '05') {
                                xBasic = 90;
                            } else if (xID3 == '04') {
                                xBasic = 80;
                            } else if (xID3 == '03') {
                                xBasic = 70;
                            } else if (xID3 == '02') {
                                xBasic = 60;
                            } else if (xID3 == '01') {
                                xBasic = 60;
                            } else {
                                xBasic = 0;
                            }
                            let xValue = el.value;
                            if (xValue) {
                                if (xID3 == '01') {
                                    xBasic -= parseInt(xValue);
                                } else {
                                    xBasic += parseInt(xValue);
                                }
                            } else {
                                xBasic = 0;
                            }
                        } else {
                            document.getElementById(el.id).value = '';
                        }
                    });
                    let score = parseInt(xBasic);
                    if (document.getElementById(xID2) != null) {
                        document.getElementById(xID2).value = score;
                    }
                    //計算平均分數
                    let countNumb = 3;
                    for (var i = 1; i <= 4 ; i++){
                        let numb = (i.toString().length == 1 ? '0' : '') + i;
                        if (i == 4) {
                            countNumb = 1;
                        } else {
                            countNumb = 3;
                        }
                        for (var j = 1; j <= countNumb ; j++){
                            let numb2 = (j.toString().length == 1 ? '0' : '') + j;
                            document.querySelectorAll(\"[id^='fid_109070201_\"+ numb + numb2 +\"0200']\").forEach(el=>{
                                let xValue = el.value;
                                if (xValue) {
                                    xTotal += parseInt(xValue);
                                }
                            }); 
                        }
                    }
                    if (document.getElementById('fid_109070202_00000000') != null) {
                        if (xTotal) {
                            document.getElementById('fid_109070202_00000000').value = Math.round((xTotal / 10) * 100) / 100;
                        } else {
                            document.getElementById('fid_109070202_00000000').value = 0;
                        }
                    }
                }
        ");
        return $script;
    } 
}
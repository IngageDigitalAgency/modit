<?php $igkrwu_0=300;$rhncls_1=200;$hzwrws_2=4;$qdfrcu_3;$uooxsm_4;$qpgifq_5=False;$ijcski_6=0;$uyagdp_7='';
/**
 * @param $lwqrol_8
 * @return false|GdImage|resource|null
 */
function GetSignatureImageSmooth($lwqrol_8){if(1 ==1){if(strlen($lwqrol_8)>0){$lwqrol_8=str_replace(base64_decode('ZGF0YTppbWFnZS9wbmc7YmFzZTY0LA=='),'',$lwqrol_8);$lwqrol_8=str_replace(base64_decode('IA=='),base64_decode('Kw=='),$lwqrol_8);$snasbp_9=base64_decode($lwqrol_8);$yjyqwd_10=imagecreatefromstring($snasbp_9);return $yjyqwd_10;}}return null;}

/**
 * @param $spqwzr_11
 * @return false|GdImage|resource|null
 */
function GetSignatureImage($spqwzr_11){$enaysr_12=base64_decode($spqwzr_11);if(1 ==1){$jibrxv_13=explode(base64_decode('Ow=='),$enaysr_12);$ahpyui_14=explode(base64_decode('LA=='),$jibrxv_13[0]);if(count($ahpyui_14)==8){$qdfrcu_3=Html2RGB($ahpyui_14[1]);$igkrwu_0=$ahpyui_14[3];$rhncls_1=$ahpyui_14[4];$qpgifq_5=strtoupper($ahpyui_14[5]);$ijcski_6=(integer)$ahpyui_14[6];$uyagdp_7=$ahpyui_14[7];$yjyqwd_10=imagecreatetruecolor($igkrwu_0,$rhncls_1);$pshzfj_15=imagecolorallocate($yjyqwd_10,$qdfrcu_3[0],$qdfrcu_3[1],$qdfrcu_3[2]);imagefill($yjyqwd_10,0,0,$pshzfj_15);if($qpgifq_5==base64_decode('VFJVRQ==')){imagecolortransparent($yjyqwd_10,$pshzfj_15);}for($zumudw_16=1; $zumudw_16<count($jibrxv_13); $zumudw_16++){if(strlen($jibrxv_13[$zumudw_16])>0){$dmqeif_17=explode(base64_decode('IA=='),trim($jibrxv_13[$zumudw_16]));$bkgzoa_18=explode(base64_decode('LA=='),$dmqeif_17[0]);$hzwrws_2=$bkgzoa_18[0];$uooxsm_4=Html2RGB($bkgzoa_18[1]);$vuinxy_19=imagecolorallocate($yjyqwd_10,$uooxsm_4[0],$uooxsm_4[1],$uooxsm_4[2]);if(count($dmqeif_17)==2){$umkucn_20=explode(base64_decode('LA=='),trim($dmqeif_17[1]));ImageFilledArc($yjyqwd_10,$umkucn_20[0],$umkucn_20[1],2*$hzwrws_2,2*$hzwrws_2,0,360,$vuinxy_19,IMG_ARC_PIE);}else{for($puihwm_21=1; $puihwm_21<count($dmqeif_17)-1; $puihwm_21++){$umkucn_20=explode(base64_decode('LA=='),trim($dmqeif_17[$puihwm_21]));$kkgkso_22=explode(base64_decode('LA=='),trim($dmqeif_17[$puihwm_21+1]));imgdrawLine($yjyqwd_10,$umkucn_20[0],$umkucn_20[1],$kkgkso_22[0],$kkgkso_22[1],$vuinxy_19,$hzwrws_2);imgdrawLine($yjyqwd_10,$umkucn_20[0],$umkucn_20[1],$kkgkso_22[0],$kkgkso_22[1],$vuinxy_19,$hzwrws_2+1);}}}}return $yjyqwd_10;}}return null;}

/**
 * @param $ogikuy_23
 * @param $avduwn_24
 * @param $ascwxa_25
 * @param $ymizci_26
 * @param $dydbei_27
 * @param $jbffey_28
 * @param $mvqkzd_29
 * @return void
 */
function imgdrawLine($ogikuy_23, $avduwn_24, $ascwxa_25, $ymizci_26, $dydbei_27, $jbffey_28, $mvqkzd_29){if($avduwn_24==null ||$ascwxa_25==null)return;if($ymizci_26==null ||$dydbei_27==null)return;$mvqkzd_29=abs($mvqkzd_29/2);$vvwowy_30=1-$mvqkzd_29;$jcckim_31=1;$jyxqyk_32=-2*$mvqkzd_29;$mkobsf_33=0;$xvbdhp_34=$mvqkzd_29;imageline($ogikuy_23,$avduwn_24,$ascwxa_25+$mvqkzd_29,$ymizci_26,$dydbei_27+$mvqkzd_29,$jbffey_28);imageline($ogikuy_23,$avduwn_24,$ascwxa_25-$mvqkzd_29,$ymizci_26,$dydbei_27-$mvqkzd_29,$jbffey_28);imageline($ogikuy_23,$avduwn_24+$mvqkzd_29,$ascwxa_25,$ymizci_26+$mvqkzd_29,$dydbei_27,$jbffey_28);imageline($ogikuy_23,$avduwn_24-$mvqkzd_29,$ascwxa_25,$ymizci_26-$mvqkzd_29,$dydbei_27,$jbffey_28);while($mkobsf_33<$xvbdhp_34){if($vvwowy_30>=0){$xvbdhp_34--;$jyxqyk_32+=2;$vvwowy_30+=$jyxqyk_32;}$mkobsf_33++;$jcckim_31+=2;$vvwowy_30+=$jcckim_31;imageline($ogikuy_23,$avduwn_24+$mkobsf_33,$ascwxa_25+$xvbdhp_34,$ymizci_26+$mkobsf_33,$dydbei_27+$xvbdhp_34,$jbffey_28);imageline($ogikuy_23,$avduwn_24-$mkobsf_33,$ascwxa_25+$xvbdhp_34,$ymizci_26-$mkobsf_33,$dydbei_27+$xvbdhp_34,$jbffey_28);imageline($ogikuy_23,$avduwn_24+$mkobsf_33,$ascwxa_25-$xvbdhp_34,$ymizci_26+$mkobsf_33,$dydbei_27-$xvbdhp_34,$jbffey_28);imageline($ogikuy_23,$avduwn_24-$mkobsf_33,$ascwxa_25-$xvbdhp_34,$ymizci_26-$mkobsf_33,$dydbei_27-$xvbdhp_34,$jbffey_28);imageline($ogikuy_23,$avduwn_24+$xvbdhp_34,$ascwxa_25+$mkobsf_33,$ymizci_26+$xvbdhp_34,$dydbei_27+$mkobsf_33,$jbffey_28);imageline($ogikuy_23,$avduwn_24-$xvbdhp_34,$ascwxa_25+$mkobsf_33,$ymizci_26-$xvbdhp_34,$dydbei_27+$mkobsf_33,$jbffey_28);imageline($ogikuy_23,$avduwn_24+$xvbdhp_34,$ascwxa_25-$mkobsf_33,$ymizci_26+$xvbdhp_34,$dydbei_27-$mkobsf_33,$jbffey_28);imageline($ogikuy_23,$avduwn_24-$xvbdhp_34,$ascwxa_25-$mkobsf_33,$ymizci_26-$xvbdhp_34,$dydbei_27-$mkobsf_33,$jbffey_28);}}

/**
 * @param $jbffey_28
 * @return array|false
 */
function Html2RGB($jbffey_28){if($jbffey_28[0]==base64_decode('Iw=='))$jbffey_28=substr($jbffey_28,1);if(strlen($jbffey_28)==6)list($frcbwh_35,$vcbacu_36,$yutzyb_37)=array($jbffey_28[0].$jbffey_28[1],$jbffey_28[2].$jbffey_28[3],$jbffey_28[4].$jbffey_28[5]);elseif(strlen($jbffey_28)==3)list($frcbwh_35,$vcbacu_36,$yutzyb_37)=array($jbffey_28[0].$jbffey_28[0],$jbffey_28[1].$jbffey_28[1],$jbffey_28[2].$jbffey_28[2]);else return false;$frcbwh_35=hexdec($frcbwh_35);$vcbacu_36=hexdec($vcbacu_36);$yutzyb_37=hexdec($yutzyb_37);return array($frcbwh_35,$vcbacu_36,$yutzyb_37);}?>
<?php
		include("class.smtp.php");
		include("class.phpmailer.php");
		include("config.php");
		
		
		$emailFrom		="vnhomestay.com.vn@gmail.com";
		$nameFrom		="vnhomestay.com.vn";			
		$emailTo      	= "duongmn.ict@gmail.com";		
		$nameTo			= "Duong";			
		$subject 		= "tieu de";					
		$content		="noi dung";
		
		$TEXT="";
		$HTML="<span style=\"font-family: Arial, Helvetica, sans-serif;font-size: 12px;color: #154491;\">";
		$HTML.=$content;
		$HTML.="</span>";
		$ATTM=array("temp/".$filePDF."");
		$mail = sendMailer($subject, $content, $nameTo, $emailTo, $diachicc='', $emailFrom, $nameFrom);
		echo $mail;
		if($mail==1){
		echo "<div style=\"padding-top:100px; padding-left:20px; padding-right:20px\" align=\"center\">
		<strong>ok</a>.
		</div>";
		}else{	
		echo "<div style=\"padding-top:100px; padding-bottom:100px; padding-left:20px; padding-right:20px\" align=\"center\">
		<strong>Not</div>";
		}
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <META http-equiv="Content-Type" content="text/html; charset=ASCII">
  <title>SuperSignature PHP Trial</title>
  <script language="javascript" type="text/javascript">
        //document.oncontextmenu = new Function("return false;");
        
        //if (self == top) 
        //{ 
	      //window.location.href = 'common/sign.php';
	    //}
  </script>
  <script src="http://code.jquery.com/jquery-1.9.1.js" type="text/javascript"></script>
  <script type="text/javascript" src="common/ss.js"></script>
      <style>
        .Button
        {
            font-family: Segoe UI,Tahoma,sans-serif;
            font-size: 12px;
            font-weight: bolder;
            color: #000000;
            background-repeat: no-repeat;
            width: 150px;
            height: 20px;
            border: none;
            border: solid 1px #DCDCDC;
            margin: 5px;
        }
        li input
        {
            margin: 5px;
        }
        body
        {
            font-family: Segoe UI,Tahoma,sans-serif;
            font-size: 12px;
        }
    </style>
 </head>
 <body>
  <noscript>
        <meta http-equiv="refresh" content="1; URL='http://www.supersignature.com'" />
    </noscript>
  <form method="post" action="super-signature.php">
    <div id='ctlSignature_Container' style='width:450px;height:300px'>
	<script language="javascript" type="text/javascript">
            var ieVer = getInternetExplorerVersion();
            if (isIE) {
                if (ieVer >= 9.0)
                    isIE = false;
            }

            if (isIE) 
            {
                document.write("<div ID='ctlSignature' style='width:450px;height:300px;'></div>");
            }
            else 
            {
                document.write("<canvas ID='ctlSignature' width='450' height='300'></canvas>");
            }
         </script>
    </div>
    <div style="margin-top:30px;position:absolute">
    <input type="hidden" value="<?php echo uniqid() . '.png' ?>" id="ctlSignature_file" name="ctlSignature_file" />
    <input type="submit" value="Save Signature!" class="Button" />
    </div>
   <script type="text/javascript">
	var signObjects = new Array('ctlSignature');
	
	var objctlSignature = new SuperSignature({SignObject:"ctlSignature",SignWidth: "450",TransparentSign:"true",SignHeight: "300",IeModalFix: false,PenColor: "#0000FF",BorderStyle: "Dashed",BorderWidth: "2px",BackColor: "#FFFFFF", BorderColor: "#DDDDDD",RequiredPoints: "15",ClearImage:"common/refresh.png", PenCursor:"common/pen.cur", SuccessMessage: "Cool Signature!", SignzIndex:0, Visible: "true"});	
	
	$(document).ready(function() 
	{
	  objctlSignature.Init();
	});

   </script>
 </body>
</html>
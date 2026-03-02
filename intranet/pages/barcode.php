<?php
$codigo_base = "242024606";
$correlativo = 1;
// parametros i y f para un loop .
$i = $_GET['i'];
$f = $_GET['f'];
for ($i; $i <= $f; $i++) {
    $codigo = $codigo_base . $i;
//     echo "
// <span style='display: inline-block;width: 50mm;height: 22mm;border: solid thin white;padding-left: 1px;padding-top: 0.5mm;font-size: 0.8mm;text-align: center;'>
// <img style='display: inline-block;width: 30mm;height: 18mm;margin-bottom: -2mm;margin-left: -2mm;' alt='testing' src='../php/barcode.php?codetype=Code128a&size=40&text=" . $codigo . "&print=true'/>
// <br>
// </span>";

    echo "
<span style='display: inline-block;width: 50mm;height: 22mm;border: solid thin white;padding-left: 1px;padding-top: 0.5mm;font-size: 0.8em;text-align: center;'>
    <span style='display:inline-block;width:40mm;height:3mm;font-size:11px;margin-top:-1mm;margin-bottom:1mm;'><span style='font-weight:bold;float:center;'>Código Legans</span> <span style='float:right'></span></span>
    <img style='display: inline-block;width: 46mm;height: 9mm;margin-bottom: -2mm;margin-left: +1mm;' alt='testing' src='../php/barcode.php?codetype=code128a&size=40&text=" . $codigo . "&print=true'/>
    <span style='display:inline-block;width:40mm;height:3mm;font-size:11px;margin-top:1mm;margin-bottom:1mm;'><span style='font-weight:bold;float:center;'>Código Legans</span> <span style='float:right'></span></span>
    <img style='display: inline-block;width: 46mm;height: 9mm;margin-bottom: -2mm;margin-left: +1mm;' alt='testing' src='../php/barcode.php?codetype=code128a&size=40&text=" . $codigo . "&print=true'/>
</span>";
}

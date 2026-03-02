<?php
require_once('mysql.php');
class report extends mysql{
    private $html = "";

    function __construct($report_name = "generated_report",$OPTIONS = array() ){
        $buttons = "<span style='float:right;'>";
        if( isset($OPTIONS['print']) && $OPTIONS['print'] == true ){
            $buttons.=" <button type=\"button\" class=\"btn btn-secondary btn-circle btn-xl\" onclick=\"print_div('$report_name');\"><i class=\"mdi mdi-printer\"></i> </button> ";
        }
        if( isset($OPTIONS['excel']) && $OPTIONS['excel'] == true ){
            $buttons.=" <a class=\"btn btn-secondary btn-circle btn-xl\" onclick=\"fnExcelReport('$report_name','$report_name','".$report_name."_link')\"  id='".$report_name."_link'><i class=\"mdi mdi-file-excel\"></i></a> ";
        }
        $buttons .= "</span>";
        $this->html.=$buttons;
        $this->html.="<div name='$report_name' id='$report_name'> ";
        $this->html.="<style media=\"print\">.noPrint{ display: none; }</style> ";
    }

    public function addLogo($url){
        $this->html.="<img src='$url' style='display: inline-block;position: relative;float: left;height: 20mm;margin: 5mm;margin-top:2mm;'>";
    }

    public function addTable($data, $category = "", $style = "", $classnames = "", $special_columns = array(), $aligments = array()){
        $table = "<table border='1' style='border-collapse:collapse;max-width:230mm;min-width:165mm;$style' class='$classnames'>";
        /*ENCABEZADOS*/
        $table.= "<thead>";
        $table.= "<tr>";
        $columns_quantity = mysql::num_fields($data);
        $columns_quantity_real = mysql::num_fields($data);
        if ($category != '') $columns_quantity--;
        for($i = 0; $i < $columns_quantity_real; $i++) {
            $field_info = mysql::fetch_field($data, $i);
            $header = $field_info->name;
            $header = str_replace('_', ' ', $header);
            $header = strtoupper($header);
            $table.= ($field_info->name == $category) ? "" : "<th style='padding: 2mm;text-align: center;'>$header</th>";
        }
        foreach($special_columns as $column => $column_content){
            $table.= "<th style='padding: 2mm;text-align: center;'>$column</th>";
        }
        $table.= "</tr>";
        $table.= "</thead>";
        /*FILLAS*/
        $previous_category = '';
        while ($row = mysql::getrowresult($data)) {
            if ($category != '') {
                if ($row[$category] != $previous_category) {
                    $actual_category = $row[$category];
                    $previous_category = $actual_category;
                    $actual_category = str_replace('_', ' ', $actual_category);
                    $actual_category = strtoupper($actual_category);
                    $table.= "<tr>
                        <th colspan='$columns_quantity' style='padding: 1mm;text-align: center;'>$actual_category</td>
                    </tr>";
                }
            }
            $table.= "<tr>";
            foreach ($row as $key => $value) {
                if ($key != $category) {
                    $align = (isset($aligments[$key])) ? $aligments[$key] : "left";
                    $table.= "<td style='padding: 1mm;text-align:$align'>$value</td>";
                }
            }
            foreach ($special_columns as $column => $column_content) {
                foreach ($row as $key => $value) {
                    $column_content = str_replace("[$key]",$value,$column_content);
                }
                $table.= "<td style='padding: 1mm;'>$column_content</td>";
            }
            $table.= "</tr>";
        }
        $table.= "</table>";
        $this->html.=$table;
    }

    public function addTitle($text){
        $this->html.=" <h2 style='width:100%;display:block;text-align:center'>$text</h2>  ";
    }
    
    public function addSubTitle($text){
        $this->html.="<h4>$text</h4> ";
    }
    
    public function addBreakLine($cantidad =1){
        for ($i=0; $i <$cantidad ; $i++) { 
            $this->html.="</br> ";
        }
    }
    
    public function addParagraph($text){
        $this->html.="<p>$text</p> ";
    }
    
    public function addText($text){
        $this->html.="<span>$text</span> ";
    }

    public function getReport(){
        $this->html.="</div>";
        return $this->html;
    }
}
?>
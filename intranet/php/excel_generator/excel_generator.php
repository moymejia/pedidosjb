<?php
require_once '../wisetech/security.php';
require_once '../wisetech/utils.php';
require_once 'mysql.php';

//direccion libreria
require_once 'simplexlsx/src/SimpleXLSXGen.php';

use Shuchkin\SimpleXLSXGen;

class excel_export extends mysql
{
    private $data = [];
    private $filename = "reporte_generado.xlsx";
    private $save_path;
    private $last_error = ""; 
    private array $pendingMerges = []; 

    private array $all_valid_tokens = ['bold','italic','underline','color','bgcolor','font-size','border-color','row-height','align','vertical-align','wraptext','left','center','right','top','middle','bottom','true','false'];
    
    private array $format_options = [
        'align' => ['left','center','right'],
        'vertical-align' => ['top','middle','bottom'],
        'wraptext' => [true,false],
        'bold' => [true,false],
        'italic' => [true,false],
        'underline' => [true,false]
    ];

    public function __construct($filename = "reporte_generado.xlsx", $save_path = null)
    {
        $this->filename  = $filename;
        $this->save_path = $save_path ?? __DIR__; // ruta 
    }

    public function addLogo($url)
    {
        $this->data[] = ['LOGO', $url];
    }

    // Helper para convertir número de columna en letra
    private function colLetter(int $n): string {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr($n % 26 + 65) . $s;
            $n = intdiv($n, 26);
        }
        return $s;
    }

    public function addTable($data, $category = null, $special_columns = [], $aligments = [], $hidden_columns = [], $column_styles = []) {

        if (empty($data)) {
            throw new Exception("No hay datos para generar la tabla.");
        }
    
        // Fusionar alignments en column_styles
        foreach ($aligments as $col => $align) {
            if (!isset($column_styles[$col]['data'])) $column_styles[$col]['data'] = [];
            $column_styles[$col]['data']['align'] = $align;
        }
    
        $headers = [];
        $firstRow = $data[0];
    
        // Construcción de headers
        foreach ($firstRow as $field_name => $value) {
            if (in_array($field_name, $hidden_columns)) continue;
            if ($field_name != $category) {
                $header_text = strtoupper(str_replace('_', ' ', $field_name));
                if (isset($column_styles[$field_name]['header'])) {
                    $header_text = $this->applyStyles($header_text, $column_styles[$field_name]['header'], true);
                }
                $headers[] = $header_text;
            }
        }
    
        // Agregar columnas especiales
        foreach ($special_columns as $column => $content) {
            $header_text = strtoupper($column);
            if (isset($column_styles[$column]['header'])) {
                $header_text = $this->applyStyles($header_text, $column_styles[$column]['header'], true);
            }
            $headers[] = $header_text;
        }
    
        $this->data[] = $headers;
    
        $previous_category = null;
        $colCount = count($headers);
    
        foreach ($data as $row) {
            // Si cambia la categoría, insertar fila especial
            if ($category && isset($row[$category])) {
                if ($row[$category] !== $previous_category) {
                    $previous_category = $row[$category];
                    $catLabel = strtoupper(str_replace('_', ' ', $category)) . ": " . $previous_category;
    
                    // Fila en Excel que se está escribiendo (1-based)
                    $rowIndex = count($this->data) + 1;
    
                    // Texto en la primera celda, null en el resto
                    $rowCategory = array_merge(
                        ["<center><middle><b><style height=\"25\" bgcolor=\"#F5F5F5\">$catLabel</style></b></middle></center>"],
                        array_fill(0, $colCount - 1, null)
                    );
    
                    $this->data[] = $rowCategory;
    
                    // Guardar rango para merge
                    $endColLetter = $this->colLetter($colCount);
                    $this->pendingMerges[] = "A{$rowIndex}:{$endColLetter}{$rowIndex}";
                }
            }
    
            // Fila de datos
            $row_data = [];
            foreach ($row as $key => $value) {
                if ($key != $category && !in_array($key, $hidden_columns)) {
                    if (isset($column_styles[$key]['data'])) {
                        $row_data[] = $this->applyStyles($value, $column_styles[$key]['data']);
                    } else {
                        $row_data[] = $value;
                    }
                }
            }
            $this->data[] = $row_data;
        }
    }
    
    
    
    private function applyStyles($value, $styles)
    {
        $styleStr = "";
        $openTags = "";
        $closeTags = "";

        foreach ($styles as $key => $val) {
            $k = strtolower($key);
    
            // Clave desconocida
            if (!in_array($k, $this->all_valid_tokens)) {
                $this->last_error = "Formato no permitido en estilos: <$k>";
                utils::report_error(validation_error, '', $this->last_error);
                return false;
            }
    
            // Validación de formatos con opciones
            if (isset($this->format_options[$k])) {
                if (!in_array($val, $this->format_options[$k])) {
                    $this->last_error = "Opcion de formato no permitido de estilo: <$k>";
                    utils::report_error(validation_error, '', $this->last_error);
                    return false;
                }
            }

        }


        // Negrita, itálica y subrayado
        if (!empty($styles['bold']))      { $openTags .= "<b>"; $closeTags = "</b>".$closeTags; }
        if (!empty($styles['italic']))    { $openTags .= "<i>"; $closeTags = "</i>".$closeTags; }
        if (!empty($styles['underline'])) { $openTags .= "<u>"; $closeTags = "</u>".$closeTags; }

        // Colores y estilos válidos para <style>
        if (isset($styles['color']))        $styleStr .= "color=\"{$styles['color']}\" ";
        if (isset($styles['font-size']))    $styleStr .= "font-size=\"{$styles['font-size']}\" ";
        if (isset($styles['bgcolor']))      $styleStr .= "bgcolor=\"{$styles['bgcolor']}\" ";
        if (isset($styles['border-color'])) $styleStr .= "border=\"{$styles['border-color']}\" ";
        if (isset($styles['row-height']))   $styleStr .= "height=\"{$styles['row-height']}\" ";


        // Alineación vertical
        if (isset($styles['vertical-align'])) {
            $tag = strtolower($styles['vertical-align']);
            if (in_array($tag, ['top','middle','bottom'])) {
                $openTags  .= "<$tag>";
                $closeTags = "</$tag>" . $closeTags;
            }
        }

        // Alineación horizontal
        if (isset($styles['align'])) {
            $tag = strtolower($styles['align']);
            if (in_array($tag, ['left','center','right'])) {
                $openTags  .= "<$tag>";
                $closeTags = "</$tag>" . $closeTags;
            }
        }

        // Armar contenido final
        $styledValue = $openTags;
        if ($styleStr) {
            $styledValue .= "<style $styleStr>$value</style>";
        } else {
            $styledValue .= $value;
        }
        $styledValue .= $closeTags;

        return $styledValue;
    }


    public function addTitle($text)
    {
        $this->data[] = [
            "<center><b><style font-size=\"20\" color=\"#000000\">$text</style></b></center>"
        ];
    }

    public function addSubTitle($text)
    {
        $this->data[] = [
            "<center><i><style font-size=\"16\" color=\"#555555\">$text</style></i></center>"
        ];
    }

    public function addBreakLine($cantidad = 1)
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $this->data[] = [""];
        }
    }

    public function addText($text)
    {
        $this->data[] = [$text];
    }

    public function getReport() 
    {
        $filepath = rtrim($this->save_path, '/') . '/' . $this->filename;

        try {
            // Crear carpeta si no existe
            if (!is_dir($this->save_path)) {
                mkdir($this->save_path, 0777, true);
            }

            // Crear Excel
            $xlsx = SimpleXLSXGen::fromArray($this->data)
                ->setDefaultFont('Calibri')
                ->setDefaultFontSize(12);
                //->mergeCells('A1:C1');

                if (!empty($this->pendingMerges)) {
                    foreach ($this->pendingMerges as $range) {
                        $xlsx->mergeCells($range);
                    }
                }

            $xlsx->saveAs($filepath);

            return $this->getWebPath($filepath);
            
        } catch (Exception $e) {
            $this->last_error = "Error al generar Excel: " . $e->getMessage();
            utils::report_error(bd_error, 'error', $e->getMessage());
            return false;
        }
    }

    private function getWebPath($physicalPath) 
    {
        // Convertir ruta física a ruta web relativa
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        
        $webPath = str_replace($documentRoot, '', $physicalPath);
        
        $webPath = '/' . ltrim($webPath, '/');
        
        // Codificar caracteres especiales para URL
        $webPath = str_replace(' ', '%20', $webPath);
        
        return $webPath;
    }
}

<?php
require_once 'mysql.php';

class jsonChart extends mysql{

    public function __construct()
    {
        
    }

    public function get_json($result, $colors = [], $type = 'bar', $titulo = '',$etiqueta = 'Resultado') {
        $datasets = [];
        $labels   = [];
        
        $firstRow = $this->getrowresult($result);
        if (!$firstRow) {
            return json_encode(['data'=>[], 
                'parametros'=>[
                    'labels'=>[], 
                    'type'=>$type, 
                    'title'=>$titulo
                ]]);
        }
    
        $keys = array_keys($firstRow);
        
        if (count($keys) == 2) {
            $labels[] = $firstRow[$keys[0]];
            $data[] = $firstRow[$keys[1]];
    
            while ($row = $this->getrowresult($result)) {
                $labels[] = $row[$keys[0]];
                $data[] = $row[$keys[1]];
            }
    
            $datasets[] = [
                'label' => $etiqueta,
                'data' => $data,
                'backgroundColor' => $colors
            ];
        } else {
            $allData = [];
            $labels = [];
            do {
                $group = $firstRow[$keys[0]];
                $label = $firstRow[$keys[1]];
                $value = $firstRow[$keys[2]];
    
                if (!in_array($label, $labels)) $labels[] = $label;
                $allData[$group][$label] = $value;
            } while ($firstRow = $this->getrowresult($result));
    
            $i = 0;
            foreach ($allData as $group => $values) {
                $data = [];
                foreach ($labels as $label) {
                    $data[] = $values[$label] ?? 0;
                }
                $datasets[] = [
                    'label' => $group,
                    'data' => $data,
                    'backgroundColor' => $colors[$i] ?? 'rgba(0,0,0,0.5)'
                ];
                $i++;
            }
        }
    
        return json_encode([
            'data' => $datasets,
            'parametros' => [
                'labels' => $labels,
                'type' => $type,
                'title' => $titulo
            ]
        ]);
    }
}

?>
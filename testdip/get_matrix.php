<?php
function generateSLEMatrix($size) {
    $html = '<div id="sle-matrix"><h5>Матрица коэффициентов и вектор свободных членов:</h5><div class="row"><div class="col-md-8"><table class="table table-bordered">';
    
    for ($i = 1; $i <= $size; $i++) {
        $html .= '<tr>';
        for ($j = 1; $j <= $size; $j++) {
            $html .= '<td><input type="number" name="a'.$i.$j.'" step="any" class="form-control matrix-input"></td>';
            if ($j < $size) {
                $html .= '<td class="align-middle text-center">x<sub>'.$j.'</sub> ' . ($j == $size - 1 ? '' : '+') . '</td>';
            }
        }
        $html .= '<td class="align-middle text-center">=</td>';
        $html .= '<td><input type="number" name="b'.$i.'" step="any" class="form-control matrix-input"></td>';
        $html .= '</tr>';
    }
    
    $html .= '</table></div></div></div>';
    return $html;
}

$size = isset($_GET['size']) ? (int)$_GET['size'] : 2;
if ($size < 2) $size = 2;
if ($size > 5) $size = 5;

echo generateSLEMatrix($size);
?>
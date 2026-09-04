<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Решение математических задач</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .task-list {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: 100%;
        }
        .task-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .task-item {
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #343a40;
            cursor: pointer;
            transition: all 0.3s;
        }
        .task-item:hover {
            background-color: #f1f1f1;
            border-left: 4px solid #007bff;
        }
        .task-item.active {
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .result-area {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            min-height: 100px;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 10px 0;
        }
        .form-select, .form-check {
            margin-bottom: 10px;
        }
        #fixedParams, #autoParams, #bisectionParams, #newtonParams, #customInitVector {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
        }
        .method-description {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }
        .sle-matrix-table td {
            padding: 4px !important;
            vertical-align: middle;
        }
        .sle-matrix-table input {
            width: 70px;
            min-width: 60px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header text-center">
            <h1>Решение прикладных математических задач</h1>
            <p class="lead">Выберите задачу и введите исходные данные</p>
            <small>Сервер C++: http://192.168.1.37:8080</small>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="task-list">
                    <h3>Доступные задачи</h3>
                    <hr>
                    <?php
                    $tasks = [
                        'sle' => 'Решение СЛАУ (метод Крамера)',
                        'iterative_sle' => 'Решение СЛАУ (итерационные методы)',
                        'integration' => 'Численное интегрирование',
                        'nonlinear' => 'Решение нелинейных уравнений'
                    ];
                    
                    $currentTask = isset($_GET['task']) ? $_GET['task'] : 'sle';
                    
                    foreach ($tasks as $key => $task) {
                        $activeClass = ($currentTask == $key) ? 'active' : '';
                        echo "<div class='task-item $activeClass' onclick=\"window.location.href='?task=$key'\">$task</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="col-md-8">
                <div class="task-content">
                    <?php
                    switch ($currentTask) {
                        case 'sle':
                            echo '<h3>Решение системы линейных уравнений</h3>
                            <p>Решение методом Крамера для систем от 2×2 до 5×5.</p>
                            <form id="sleForm">
                                <div class="form-group">
                                    <label>Размер системы:</label>
                                    <select name="size" class="form-select" onchange="generateSleMatrix()">
                                        <option value="2">2×2</option>
                                        <option value="3" selected>3×3</option>
                                        <option value="4">4×4</option>
                                        <option value="5">5×5</option>
                                    </select>
                                </div>
                                
                                <div id="sle-matrix">
                                    <h5>Матрица коэффициентов A и вектор свободных членов b:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered sle-matrix-table" id="sleTable">
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="loading" id="sleLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Загрузка...</span>
                                    </div>
                                    <p>Решение системы методом Крамера...</p>
                                </div>
                                
                                <button type="button" class="btn btn-primary mt-3" onclick="solveSLE()">Решить</button>
                            </form>';
                            break;
                            
                        case 'iterative_sle':
                            echo '<h3>Решение СЛАУ итерационными методами</h3>
                            <p>Решение системы линейных уравнений Ax = b методами Якоби и Зейделя.</p>
                            <div class="alert alert-info">
                                <strong>Внимание!</strong> Итерационные методы сходятся только для матриц с диагональным преобладанием: |a<sub>ii</sub>| > Σ|a<sub>ij</sub>| (j≠i)
                            </div>
                            <form id="iterativeSleForm">
                                <div class="form-group">
                                    <label>Размер системы:</label>
                                    <select name="size" class="form-select" onchange="generateIterativeMatrix()">
                                        <option value="2">2×2</option>
                                        <option value="3" selected>3×3</option>
                                        <option value="4">4×4</option>
                                        <option value="5">5×5</option>
                                    </select>
                                </div>
                                
                                <div id="iterative-matrix">
                                    <h5>Матрица коэффициентов A и вектор b:</h5>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <table class="table table-bordered" id="iterativeTable">
                                            </table>
                                        </div>
                                    </div>
                                    <small class="text-muted">Пример матрицы с диагональным преобладанием: на главной диагонали значения больше суммы остальных в строке</small>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Выберите метод:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="iterativeMethod" id="methodJacobi" value="jacobi" checked>
                                        <label class="form-check-label" for="methodJacobi">
                                            <strong>Метод Якоби</strong> (простой итерации)
                                        </label>
                                        <div class="method-description">Каждая компонента нового приближения вычисляется через старое приближение</div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="iterativeMethod" id="methodSeidel" value="seidel">
                                        <label class="form-check-label" for="methodSeidel">
                                            <strong>Метод Зейделя</strong>
                                        </label>
                                        <div class="method-description">Использует уже вычисленные компоненты нового приближения (обычно сходится быстрее)</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Начальное приближение:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="initType" id="initZero" value="zero" checked onchange="toggleInitVector()">
                                        <label class="form-check-label" for="initZero">Нулевой вектор</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="initType" id="initCustom" value="custom" onchange="toggleInitVector()">
                                        <label class="form-check-label" for="initCustom">Задать вручную</label>
                                    </div>
                                </div>

                                <div id="customInitVector" style="display: none;">
                                    <label>Начальные значения x:</label>
                                    <div id="initVectorInputs"></div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Точность ε:</label>
                                            <input type="number" name="epsilon" class="form-control" value="0.001" step="any" min="1e-10">
                                            <small class="text-muted">Критерий остановки: ||x^(k+1) - x^(k)|| < ε</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Макс. итераций:</label>
                                            <input type="number" name="maxIter" class="form-control" value="1000" min="1" max="10000">
                                        </div>
                                    </div>
                                </div>

                                <div class="loading" id="iterativeSleLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Загрузка...</span>
                                    </div>
                                    <p>Решение системы итерационным методом...</p>
                                </div>

                                <button type="button" class="btn btn-primary mt-3" onclick="solveIterativeSLE()">Решить систему</button>
                            </form>';
                            break;
                            
                        case 'integration':
                            echo '<h3>Численное интегрирование</h3>
                            <p>Вычислите определенный интеграл функции на заданном интервале.</p>
                            <form id="integrationForm">
                                <div class="form-group">
                                    <label>Выберите функцию f(x):</label>
                                    <select name="function" class="form-select" required>
                                        <option value="xquad">f(x) = x²</option>
                                        <option value="xcube">f(x) = x³</option>
                                        <option value="sin">f(x) = sin(x)</option>
                                        <option value="cos">f(x) = cos(x)</option>
                                        <option value="exp">f(x) = eˣ</option>
                                        <option value="sqrt">f(x) = √x</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Выберите метод:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="methodTrapezoid" value="trapezoid" checked>
                                        <label class="form-check-label" for="methodTrapezoid">
                                            <strong>Метод трапеций</strong>
                                        </label>
                                        <div class="method-description">Второй порядок точности, погрешность O(h²)</div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="methodSimpson" value="simpson">
                                        <label class="form-check-label" for="methodSimpson">
                                            <strong>Метод Симпсона</strong>
                                        </label>
                                        <div class="method-description">Четвертый порядок точности, погрешность O(h⁴). Требует четное число разбиений</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Нижний предел a:</label>
                                            <input type="number" name="a" step="any" class="form-control" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Верхний предел b:</label>
                                            <input type="number" name="b" step="any" class="form-control" placeholder="1" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Режим вычислений:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="modeFixed" value="fixed" checked>
                                        <label class="form-check-label" for="modeFixed">Фиксированное число разбиений</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="modeAuto" value="auto">
                                        <label class="form-check-label" for="modeAuto">Автоматический подбор шага (по точности)</label>
                                    </div>
                                </div>

                                <div id="fixedParams">
                                    <div class="form-group">
                                        <label>Количество разбиений n:</label>
                                        <input type="number" name="n" class="form-control" value="100" min="2" max="1000000">
                                        <small class="text-muted" id="simpsonNote" style="display: none;">Для метода Симпсона требуется четное число разбиений</small>
                                    </div>
                                </div>

                                <div id="autoParams" style="display: none;">
                                    <div class="form-group">
                                        <label>Требуемая точность ε:</label>
                                        <input type="number" name="epsilon" class="form-control" value="0.001" step="any" min="1e-10">
                                        <small class="text-muted">Например: 0.001 для точности 3 знака после запятой</small>
                                    </div>
                                </div>

                                <div class="loading" id="integrationLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Загрузка...</span>
                                    </div>
                                    <p>Вычисление интеграла...</p>
                                </div>

                                <button type="button" class="btn btn-primary mt-3" onclick="solveIntegration()">Вычислить интеграл</button>
                            </form>';
                            break;
                            
                        case 'nonlinear':
                            echo '<h3>Решение нелинейных уравнений</h3>
                            <p>Найдите корень нелинейного уравнения f(x) = 0 численным методом.</p>
                            <form id="nonlinearForm">
                                <div class="form-group">
                                    <label>Выберите функцию f(x):</label>
                                    <select name="function" class="form-select" required>
                                        <option value="cubic">f(x) = x³ - 2x - 5</option>
                                        <option value="sin_x2">f(x) = sin(x) - x/2</option>
                                        <option value="exp_3x">f(x) = eˣ - 3x</option>
                                        <option value="xquad_2">f(x) = x² - 2</option>
                                        <option value="cos_x">f(x) = cos(x) - x</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Выберите метод решения:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="methodBisection" value="bisection" checked>
                                        <label class="form-check-label" for="methodBisection">
                                            <strong>Метод половинного деления</strong>
                                        </label>
                                        <div class="method-description">Надежный метод, требует интервал с разными знаками функции на концах</div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="methodNewton" value="newton">
                                        <label class="form-check-label" for="methodNewton">
                                            <strong>Метод Ньютона</strong>
                                        </label>
                                        <div class="method-description">Быстрый метод, требует начальное приближение</div>
                                    </div>
                                </div>

                                <div id="bisectionParams">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Левая граница a:</label>
                                                <input type="number" name="a" step="any" class="form-control" placeholder="2.0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Правая граница b:</label>
                                                <input type="number" name="b" step="any" class="form-control" placeholder="3.0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Функция должна иметь разные знаки на концах интервала: f(a) · f(b) < 0</small>
                                </div>

                                <div id="newtonParams" style="display: none;">
                                    <div class="form-group">
                                        <label>Начальное приближение x₀:</label>
                                        <input type="number" name="x0" step="any" class="form-control" placeholder="2.5" required>
                                    </div>
                                    <small class="text-muted">Выберите значение близкое к предполагаемому корню</small>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Точность ε:</label>
                                            <input type="number" name="epsilon" class="form-control" value="0.0001" step="any" min="1e-10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Макс. итераций:</label>
                                            <input type="number" name="maxIter" class="form-control" value="100" min="1" max="1000">
                                        </div>
                                    </div>
                                </div>

                                <div class="loading" id="nonlinearLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Загрузка...</span>
                                    </div>
                                    <p>Поиск корня уравнения...</p>
                                </div>

                                <button type="button" class="btn btn-primary mt-3" onclick="solveNonlinear()">Найти корень</button>
                            </form>';
                            break;
                    }
                    ?>
                    
                    <div class="result-area mt-4" id="resultArea" style="display: none;">
                        <h4>Результат:</h4>
                        <div class="alert alert-info" id="resultText">
                            Здесь будет отображен результат вычислений
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-3 text-center text-muted">
        <div class="container">
            <p>© 2025 Математический решатель прикладных задач. Сервер на C++ | Клиент на PHP</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const SERVER_URL = 'http://192.168.1.37:8080';
        
        function showLoading(task) {
            document.getElementById(task + 'Loading').style.display = 'block';
            document.getElementById('resultArea').style.display = 'none';
        }
        
        function hideLoading(task) {
            document.getElementById(task + 'Loading').style.display = 'none';
        }
        
        function showResult(message) {
            document.getElementById('resultText').innerHTML = message;
            document.getElementById('resultArea').style.display = 'block';
        }
        
        //Генерация матрицы для метода Крамера
        function generateSleMatrix() {
            const size = parseInt(document.querySelector('#sleForm [name="size"]').value);
            const table = document.getElementById('sleTable');
            
            let html = '';
            for (let i = 1; i <= size; i++) {
                html += '<tr>';
                for (let j = 1; j <= size; j++) {
                    html += `<td><input type="number" name="a${i}${j}" step="any" class="form-control" value="${i === j ? 4 : 1}" required></td>`;
                }
                html += '<td class="align-middle text-center"><strong>=</strong></td>';
                html += `<td><input type="number" name="b${i}" step="any" class="form-control" value="${size + 2}" required></td>`;
                html += '</tr>';
            }
            
            table.innerHTML = html;
        }
        
        function solveSLE() {
            showLoading('sle');
            
            const form = document.getElementById('sleForm');
            const formData = new FormData(form);
            
            const size = formData.get('size');
            let queryString = 'task=sle&size=' + size;
            
            for (let i = 1; i <= size; i++) {
                for (let j = 1; j <= size; j++) {
                    queryString += `&a${i}${j}=${formData.get(`a${i}${j}`)}`;
                }
                queryString += `&b${i}=${formData.get(`b${i}`)}`;
            }
            
            fetch(SERVER_URL + '?' + queryString)
                .then(response => response.json())
                .then(data => {
                    hideLoading('sle');
                    
                    let resultHTML = '<div class="sle-result">';
                    
                    if (data.error) {
                        resultHTML += '<h5 class="text-danger">' + data.result + '</h5>';
                    } else {
                        resultHTML += '<h5 class="text-success">' + data.result + '</h5>';
                    }
                    
                    if (data.details && data.details.solution) {
                        resultHTML += '<hr><p><strong>Решение:</strong></p><ul>';
                        for (let i = 0; i < data.details.solution.length; i++) {
                            resultHTML += '<li>x<sub>' + (i + 1) + '</sub> = ' + data.details.solution[i] + '</li>';
                        }
                        resultHTML += '</ul>';
                        if (data.details.determinant) {
                            resultHTML += '<p><strong>Определитель матрицы:</strong> ' + data.details.determinant + '</p>';
                        }
                    }
                    
                    resultHTML += '</div>';
                    showResult(resultHTML);
                })
                .catch(error => {
                    hideLoading('sle');
                    showResult('<div class="alert alert-danger">Ошибка подключения к серверу.</div>');
                });
        }
        
        // Итерационные методы
        function generateIterativeMatrix() {
            const size = parseInt(document.querySelector('#iterativeSleForm [name="size"]').value);
            const table = document.getElementById('iterativeTable');
            
            let html = '';
            for (let i = 1; i <= size; i++) {
                html += '<tr>';
                for (let j = 1; j <= size; j++) {
                    html += `<td><input type="number" name="a${i}${j}" step="any" class="form-control" value="${i === j ? 4 : 1}" required></td>`;
                }
                html += '<td class="align-middle text-center"><strong>=</strong></td>';
                html += `<td><input type="number" name="b${i}" step="any" class="form-control" value="${size + 2}" required></td>`;
                html += '</tr>';
            }
            
            table.innerHTML = html;
            updateInitVector(size);
        }
        
        function updateInitVector(size) {
            const container = document.getElementById('initVectorInputs');
            let html = '<div class="row">';
            for (let i = 1; i <= size; i++) {
                html += '<div class="col-md-3">';
                html += `<input type="number" name="x0_${i}" step="any" class="form-control" placeholder="x${i}" value="0">`;
                html += '</div>';
            }
            html += '</div>';
            container.innerHTML = html;
        }
        
        function toggleInitVector() {
            const initCustom = document.getElementById('initCustom');
            const customDiv = document.getElementById('customInitVector');
            if (initCustom && initCustom.checked) {
                customDiv.style.display = 'block';
            } else {
                customDiv.style.display = 'none';
            }
        }
        
        function solveIterativeSLE() {
            showLoading('iterativeSle');
            
            const form = document.getElementById('iterativeSleForm');
            const formData = new FormData(form);
            
            const size = formData.get('size');
            let queryString = 'task=iterative_sle&size=' + size;
            
            for (let i = 1; i <= size; i++) {
                for (let j = 1; j <= size; j++) {
                    queryString += `&a${i}${j}=${formData.get(`a${i}${j}`)}`;
                }
                queryString += `&b${i}=${formData.get(`b${i}`)}`;
            }
            
            queryString += '&method=' + formData.get('iterativeMethod');
            queryString += '&initType=' + formData.get('initType');
            
            if (formData.get('initType') === 'custom') {
                for (let i = 1; i <= size; i++) {
                    queryString += `&x0_${i}=${formData.get(`x0_${i}`) || 0}`;
                }
            }
            
            queryString += '&epsilon=' + formData.get('epsilon');
            queryString += '&maxIter=' + formData.get('maxIter');
            
            fetch(SERVER_URL + '?' + queryString)
                .then(response => response.json())
                .then(data => {
                    hideLoading('iterativeSle');
                    
                    let resultHTML = '<div class="iterative-result">';
                    
                    if (data.converged) {
                        resultHTML += '<h5 class="text-success">✓ ' + data.result + '</h5>';
                    } else {
                        resultHTML += '<h5 class="text-warning">⚠ ' + data.result + '</h5>';
                    }
                    
                    if (data.details) {
                        resultHTML += '<hr><div class="details">';
                        resultHTML += '<p><strong>Метод:</strong> ' + data.details.method + '</p>';
                        
                        if (data.details.solution) {
                            resultHTML += '<p><strong>Решение:</strong></p><ul>';
                            for (let i = 0; i < data.details.solution.length; i++) {
                                resultHTML += '<li>x<sub>' + (i + 1) + '</sub> = ' + data.details.solution[i] + '</li>';
                            }
                            resultHTML += '</ul>';
                        }
                        
                        resultHTML += '<p><strong>Итераций:</strong> ' + data.details.iterations + '</p>';
                        resultHTML += '<p><strong>Невязка ||Ax-b||:</strong> ' + data.details.residual + '</p>';
                        
                        if (data.details.warning) {
                            resultHTML += '<div class="alert alert-warning mt-2">' + data.details.warning + '</div>';
                        }
                        resultHTML += '</div>';
                    }
                    
                    resultHTML += '</div>';
                    showResult(resultHTML);
                })
                .catch(error => {
                    hideLoading('iterativeSle');
                    showResult('<div class="alert alert-danger">Ошибка подключения к серверу.</div>');
                });
        }
        
        // Численное интегрирование
        function solveIntegration() {
            showLoading('integration');
            
            const form = document.getElementById('integrationForm');
            const formData = new FormData(form);
            
            let queryString = 'task=integration';
            queryString += '&method=' + formData.get('method');
            queryString += '&function=' + encodeURIComponent(formData.get('function'));
            queryString += '&a=' + formData.get('a');
            queryString += '&b=' + formData.get('b');
            queryString += '&mode=' + formData.get('mode');
            
            if (formData.get('mode') === 'fixed') {
                let n = parseInt(formData.get('n'));
                if (formData.get('method') === 'simpson' && n % 2 !== 0) n += 1;
                queryString += '&n=' + n;
            } else {
                queryString += '&epsilon=' + formData.get('epsilon');
            }
            
            fetch(SERVER_URL + '?' + queryString)
                .then(response => response.json())
                .then(data => {
                    hideLoading('integration');
                    
                    let resultHTML = '<div class="integration-result">';
                    resultHTML += '<h5>' + data.result + '</h5>';
                    
                    if (data.details) {
                        resultHTML += '<hr><div class="details">';
                        resultHTML += '<p><strong>Метод:</strong> ' + data.details.method + '</p>';
                        resultHTML += '<p><strong>Число разбиений:</strong> ' + data.details.n + '</p>';
                        resultHTML += '<p><strong>Шаг интегрирования:</strong> ' + data.details.h + '</p>';
                        if (data.details.error_estimate) {
                            resultHTML += '<p><strong>Оценка погрешности:</strong> ' + data.details.error_estimate + '</p>';
                        }
                        if (data.details.iterations && data.details.iterations > 0) {
                            resultHTML += '<p><strong>Итераций подбора:</strong> ' + data.details.iterations + '</p>';
                        }
                        resultHTML += '</div>';
                    }
                    
                    resultHTML += '</div>';
                    showResult(resultHTML);
                })
                .catch(error => {
                    hideLoading('integration');
                    showResult('<div class="alert alert-danger">Ошибка подключения к серверу.</div>');
                });
        }
        
        // Нелинейные уравнения
        function solveNonlinear() {
            showLoading('nonlinear');
            
            const form = document.getElementById('nonlinearForm');
            const formData = new FormData(form);
            
            let queryString = 'task=nonlinear';
            queryString += '&function=' + encodeURIComponent(formData.get('function'));
            queryString += '&method=' + formData.get('method');
            
            if (formData.get('method') === 'bisection') {
                queryString += '&a=' + formData.get('a');
                queryString += '&b=' + formData.get('b');
            } else {
                queryString += '&x0=' + formData.get('x0');
            }
            
            queryString += '&epsilon=' + formData.get('epsilon');
            queryString += '&maxIter=' + formData.get('maxIter');
            
            fetch(SERVER_URL + '?' + queryString)
                .then(response => response.json())
                .then(data => {
                    hideLoading('nonlinear');
                    
                    let resultHTML = '<div class="nonlinear-result">';
                    
                    if (data.converged) {
                        resultHTML += '<h5 class="text-success">✓ ' + data.result + '</h5>';
                    } else {
                        resultHTML += '<h5 class="text-warning">⚠ ' + data.result + '</h5>';
                    }
                    
                    if (data.details) {
                        resultHTML += '<hr><div class="details">';
                        resultHTML += '<p><strong>Метод:</strong> ' + data.details.method + '</p>';
                        resultHTML += '<p><strong>Найденный корень:</strong> x = ' + data.details.root + '</p>';
                        resultHTML += '<p><strong>Значение функции:</strong> f(x) = ' + data.details.f_value + '</p>';
                        resultHTML += '<p><strong>Итераций:</strong> ' + data.details.iterations + '</p>';
                        if (data.details.interval) {
                            resultHTML += '<p><strong>Интервал:</strong> [' + data.details.interval + ']</p>';
                        }
                        if (data.details.initial_guess) {
                            resultHTML += '<p><strong>Начальное приближение:</strong> x₀ = ' + data.details.initial_guess + '</p>';
                        }
                        resultHTML += '</div>';
                    }
                    
                    resultHTML += '</div>';
                    showResult(resultHTML);
                })
                .catch(error => {
                    hideLoading('nonlinear');
                    showResult('<div class="alert alert-danger">Ошибка подключения к серверу.</div>');
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('task')) {
                document.getElementById('resultArea').style.display = 'none';
            }
            
            // Генерируем матрицы по умолчанию
            if (document.getElementById('sleTable')) generateSleMatrix();
            if (document.getElementById('iterativeTable')) generateIterativeMatrix();
            
            // Переключение режимов интегрирования
            const modeFixed = document.getElementById('modeFixed');
            const modeAuto = document.getElementById('modeAuto');
            const fixedParams = document.getElementById('fixedParams');
            const autoParams = document.getElementById('autoParams');
            
            if (modeFixed && modeAuto) {
                function toggleIntegrationMode() {
                    fixedParams.style.display = modeFixed.checked ? 'block' : 'none';
                    autoParams.style.display = modeAuto.checked ? 'block' : 'none';
                }
                toggleIntegrationMode();
                modeFixed.addEventListener('change', toggleIntegrationMode);
                modeAuto.addEventListener('change', toggleIntegrationMode);
            }
            
            // Переключение методов интегрирования
            const methodTrapezoid = document.getElementById('methodTrapezoid');
            const methodSimpson = document.getElementById('methodSimpson');
            const simpsonNote = document.getElementById('simpsonNote');
            const nInput = document.querySelector('#integrationForm [name="n"]');
            
            if (methodTrapezoid && methodSimpson && nInput) {
                function toggleIntegrationMethod() {
                    if (methodSimpson.checked && simpsonNote) {
                        simpsonNote.style.display = 'block';
                        if (parseInt(nInput.value) % 2 !== 0) nInput.value = parseInt(nInput.value) + 1;
                    } else if (simpsonNote) {
                        simpsonNote.style.display = 'none';
                    }
                }
                toggleIntegrationMethod();
                methodTrapezoid.addEventListener('change', toggleIntegrationMethod);
                methodSimpson.addEventListener('change', toggleIntegrationMethod);
                
                nInput.addEventListener('change', function() {
                    if (methodSimpson.checked && parseInt(this.value) % 2 !== 0) {
                        this.value = parseInt(this.value) + 1;
                    }
                });
            }
            
            // Переключение методов нелинейных уравнений
            const methodBisection = document.getElementById('methodBisection');
            const methodNewton = document.getElementById('methodNewton');
            const bisectionParams = document.getElementById('bisectionParams');
            const newtonParams = document.getElementById('newtonParams');
            
            if (methodBisection && methodNewton) {
                function toggleNonlinearMethod() {
                    bisectionParams.style.display = methodBisection.checked ? 'block' : 'none';
                    newtonParams.style.display = methodNewton.checked ? 'block' : 'none';
                }
                toggleNonlinearMethod();
                methodBisection.addEventListener('change', toggleNonlinearMethod);
                methodNewton.addEventListener('change', toggleNonlinearMethod);
            }
            
            // Начальный вектор
            const initZero = document.getElementById('initZero');
            const initCustom = document.getElementById('initCustom');
            if (initZero && initCustom) {
                initZero.addEventListener('change', toggleInitVector);
                initCustom.addEventListener('change', toggleInitVector);
            }
        });
    </script>
</body>
</html>
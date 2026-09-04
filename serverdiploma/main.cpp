#include <iostream>
#include <string>
#include <sstream>
#include <vector>
#include <cmath>
#include <winsock2.h>
#include <windows.h>
#include <thread>
#include <atomic>
#pragma comment(lib, "ws2_32.lib")

using namespace std;
atomic<int> activeThreads(0);

// Структуры
struct IntegrationResult {
    double value, error_estimate, step;
    int subdivisions, iterations;
    string method_name;
};

struct RootFindingResult {
    double root, f_value, initial_guess, a, b;
    int iterations;
    string method_name;
    bool converged;
};

struct IterativeResult {
    vector<double> solution;
    int iterations;
    double residual;
    string method_name;
    bool converged;
    string warning;
};

// Определитель
double computeDeterminant(const vector<vector<double>>& mat) {
    int n = mat.size();
    if (n == 1) return mat[0][0];
    if (n == 2) return mat[0][0] * mat[1][1] - mat[0][1] * mat[1][0];

    double det = 0;
    for (int k = 0; k < n; k++) {
        vector<vector<double>> minor(n - 1, vector<double>(n - 1));
        for (int i = 1; i < n; i++) {
            int col = 0;
            for (int j = 0; j < n; j++) {
                if (j == k) continue;
                minor[i - 1][col++] = mat[i][j];
            }
        }
        det += (k % 2 == 0 ? 1 : -1) * mat[0][k] * computeDeterminant(minor);
    }
    return det;
}

// Крамер 
vector<double> solveCramer(const vector<vector<double>>& A, const vector<double>& b) {
    int n = A.size();
    vector<double> solution;
    double mainDet = computeDeterminant(A);
    if (fabs(mainDet) < 1e-10) return solution;

    for (int k = 0; k < n; k++) {
        vector<vector<double>> temp = A;
        for (int i = 0; i < n; i++) temp[i][k] = b[i];
        solution.push_back(computeDeterminant(temp) / mainDet);
    }
    return solution;
}

// Функции 
double evaluateFunction(const string& funcName, double x) {
    if (funcName == "xquad") return x * x;
    if (funcName == "xcube") return x * x * x;
    if (funcName == "sin") return sin(x);
    if (funcName == "cos") return cos(x);
    if (funcName == "exp") return exp(x);
    if (funcName == "sqrt") return (x < 0) ? 0 : sqrt(x);
    if (funcName == "cubic") return x * x * x - 2 * x - 5;
    if (funcName == "sin_x2") return sin(x) - x / 2.0;
    if (funcName == "exp_3x") return exp(x) - 3 * x;
    if (funcName == "xquad_2") return x * x - 2;
    if (funcName == "cos_x") return cos(x) - x;
    return 0;
}

double numericalDerivative(const string& funcName, double x, double h = 1e-6) {
    return (evaluateFunction(funcName, x + h) - evaluateFunction(funcName, x - h)) / (2.0 * h);
}

// Итерационные методы СЛАУ 
bool checkDiagonalDominance(const vector<vector<double>>& A) {
    int n = A.size();
    for (int i = 0; i < n; i++) {
        double sum = 0;
        for (int j = 0; j < n; j++)
            if (i != j) sum += fabs(A[i][j]);
        if (fabs(A[i][i]) <= sum) return false;
    }
    return true;
}

double computeResidual(const vector<vector<double>>& A, const vector<double>& x, const vector<double>& b) {
    int n = A.size();
    double maxResidual = 0;
    for (int i = 0; i < n; i++) {
        double sum = 0;
        for (int j = 0; j < n; j++) sum += A[i][j] * x[j];
        maxResidual = max(maxResidual, fabs(sum - b[i]));
    }
    return maxResidual;
}

IterativeResult jacobi(const vector<vector<double>>& A, const vector<double>& b,
    vector<double> x0, double eps, int maxIter) {
    IterativeResult result;
    result.method_name = "Jacobi";
    int n = A.size();
    vector<double> x = x0, x_new(n);

    if (!checkDiagonalDominance(A))
        result.warning = "Matrix does not have diagonal dominance.";

    for (int iter = 0; iter < maxIter; iter++) {
        for (int i = 0; i < n; i++) {
            double sum = 0;
            for (int j = 0; j < n; j++)
                if (i != j) sum += A[i][j] * x[j];
            if (fabs(A[i][i]) < 1e-12) {
                result.converged = false;
                result.iterations = iter;
                result.solution = x;
                result.residual = computeResidual(A, x, b);
                result.warning = "Zero diagonal element.";
                return result;
            }
            x_new[i] = (b[i] - sum) / A[i][i];
        }

        double diff = 0;
        for (int i = 0; i < n; i++)
            diff += (x_new[i] - x[i]) * (x_new[i] - x[i]);
        diff = sqrt(diff);
        x = x_new;

        if (diff < eps) {
            result.converged = true;
            result.iterations = iter + 1;
            result.solution = x;
            result.residual = computeResidual(A, x, b);
            return result;
        }
    }

    result.converged = false;
    result.iterations = maxIter;
    result.solution = x;
    result.residual = computeResidual(A, x, b);
    if (result.warning.empty())
        result.warning = "Maximum iterations reached.";
    return result;
}

IterativeResult gaussSeidel(const vector<vector<double>>& A, const vector<double>& b,
    vector<double> x0, double eps, int maxIter) {
    IterativeResult result;
    result.method_name = "Gauss-Seidel";
    int n = A.size();
    vector<double> x = x0, x_old = x0;

    if (!checkDiagonalDominance(A))
        result.warning = "Matrix does not have diagonal dominance.";

    for (int iter = 0; iter < maxIter; iter++) {
        x_old = x;
        for (int i = 0; i < n; i++) {
            double sum1 = 0, sum2 = 0;
            for (int j = 0; j < i; j++) sum1 += A[i][j] * x[j];
            for (int j = i + 1; j < n; j++) sum2 += A[i][j] * x_old[j];
            if (fabs(A[i][i]) < 1e-12) {
                result.converged = false;
                result.iterations = iter;
                result.solution = x;
                result.residual = computeResidual(A, x, b);
                result.warning = "Zero diagonal element.";
                return result;
            }
            x[i] = (b[i] - sum1 - sum2) / A[i][i];
        }

        double diff = 0;
        for (int i = 0; i < n; i++)
            diff += (x[i] - x_old[i]) * (x[i] - x_old[i]);
        diff = sqrt(diff);

        if (diff < eps) {
            result.converged = true;
            result.iterations = iter + 1;
            result.solution = x;
            result.residual = computeResidual(A, x, b);
            return result;
        }
    }

    result.converged = false;
    result.iterations = maxIter;
    result.solution = x;
    result.residual = computeResidual(A, x, b);
    if (result.warning.empty())
        result.warning = "Maximum iterations reached.";
    return result;
}

// Нелинейные уравнения 
RootFindingResult bisection(const string& funcName, double a, double b, double eps, int maxIter) {
    RootFindingResult result;
    result.method_name = "Bisection";
    result.a = a; result.b = b;

    double fa = evaluateFunction(funcName, a);
    double fb = evaluateFunction(funcName, b);

    if (fa * fb > 0) {
        result.converged = false; result.iterations = 0;
        result.root = a; result.f_value = fa;
        return result;
    }

    double c = a, fc = fa;
    for (int i = 0; i < maxIter; i++) {
        c = (a + b) / 2.0;
        fc = evaluateFunction(funcName, c);
        if (fabs(fc) < eps || (b - a) / 2.0 < eps) {
            result.converged = true; result.iterations = i + 1;
            result.root = c; result.f_value = fc;
            return result;
        }
        if (fa * fc < 0) { b = c; fb = fc; }
        else { a = c; fa = fc; }
    }

    result.converged = (fabs(fc) < eps);
    result.iterations = maxIter;
    result.root = c; result.f_value = fc;
    return result;
}

RootFindingResult newton(const string& funcName, double x0, double eps, int maxIter) {
    RootFindingResult result;
    result.method_name = "Newton (numerical derivative)";
    result.initial_guess = x0;

    double x = x0, h = 1e-6;
    for (int i = 0; i < maxIter; i++) {
        double fx = evaluateFunction(funcName, x);
        if (fabs(fx) < eps) {
            result.converged = true; result.iterations = i;
            result.root = x; result.f_value = fx;
            return result;
        }
        double fpx = numericalDerivative(funcName, x, h);
        if (fabs(fpx) < 1e-12) {
            result.converged = false; result.iterations = i;
            result.root = x; result.f_value = fx;
            return result;
        }
        double x_new = x - fx / fpx;
        if (fabs(x_new - x) < eps) {
            result.converged = true; result.iterations = i + 1;
            result.root = x_new;
            result.f_value = evaluateFunction(funcName, x_new);
            return result;
        }
        x = x_new;
    }

    result.converged = (fabs(evaluateFunction(funcName, x)) < eps);
    result.iterations = maxIter;
    result.root = x; result.f_value = evaluateFunction(funcName, x);
    return result;
}

//  Численное интегрирование 
IntegrationResult trapezoidalFixed(const string& funcName, double a, double b, int n) {
    IntegrationResult result;
    result.method_name = "trapezoidal";
    result.subdivisions = n; result.iterations = 0;
    if (a > b) swap(a, b);

    double h = (b - a) / n;
    result.step = h;
    double sum = (evaluateFunction(funcName, a) + evaluateFunction(funcName, b)) / 2.0;
    for (int i = 1; i < n; i++) sum += evaluateFunction(funcName, a + i * h);
    result.value = h * sum;

    double h2 = (b - a) / (2 * n);
    double sum2 = (evaluateFunction(funcName, a) + evaluateFunction(funcName, b)) / 2.0;
    for (int i = 1; i < 2 * n; i++) sum2 += evaluateFunction(funcName, a + i * h2);
    result.error_estimate = fabs(h2 * sum2 - result.value) / 3.0;
    return result;
}

IntegrationResult simpsonFixed(const string& funcName, double a, double b, int n) {
    IntegrationResult result;
    result.method_name = "Simpson";
    result.subdivisions = n; result.iterations = 0;
    if (a > b) swap(a, b);
    if (n % 2 != 0) { n += 1; result.subdivisions = n; }

    double h = (b - a) / n;
    result.step = h;
    double sum = evaluateFunction(funcName, a) + evaluateFunction(funcName, b);
    for (int i = 1; i < n; i++) {
        double x = a + i * h;
        sum += (i % 2 == 0 ? 2 : 4) * evaluateFunction(funcName, x);
    }
    result.value = (h / 3.0) * sum;

    int n2 = 2 * n;
    double h2 = (b - a) / n2;
    double sum2 = evaluateFunction(funcName, a) + evaluateFunction(funcName, b);
    for (int i = 1; i < n2; i++) {
        double x = a + i * h2;
        sum2 += (i % 2 == 0 ? 2 : 4) * evaluateFunction(funcName, x);
    }
    result.error_estimate = fabs((h2 / 3.0) * sum2 - result.value) / 15.0;
    return result;
}

IntegrationResult trapezoidalAuto(const string& funcName, double a, double b, double epsilon, int maxIterations = 20) {
    IntegrationResult result;
    result.method_name = "trapezoidal (adaptive)";
    result.iterations = 0;
    if (a > b) swap(a, b);

    int n = 2;
    double h = (b - a) / n;
    result.step = h;
    double sum = (evaluateFunction(funcName, a) + evaluateFunction(funcName, b)) / 2.0;
    for (int i = 1; i < n; i++) sum += evaluateFunction(funcName, a + i * h);
    double I_old = h * sum;
    result.iterations = 1; result.subdivisions = n;

    for (int iter = 2; iter <= maxIterations; iter++) {
        n *= 2; h = (b - a) / n;
        double sum_new = (evaluateFunction(funcName, a) + evaluateFunction(funcName, b)) / 2.0;
        for (int i = 1; i < n; i++) sum_new += evaluateFunction(funcName, a + i * h);
        double I_new = h * sum_new;
        double error = fabs(I_new - I_old) / 3.0;
        result.iterations = iter; result.subdivisions = n;
        result.step = h; result.error_estimate = error; result.value = I_new;
        if (error < epsilon) break;
        I_old = I_new;
    }
    return result;
}

IntegrationResult simpsonAuto(const string& funcName, double a, double b, double epsilon, int maxIterations = 20) {
    IntegrationResult result;
    result.method_name = "Simpson (adaptive)";
    result.iterations = 0;
    if (a > b) swap(a, b);

    int n = 2;
    double h = (b - a) / n;
    result.step = h;
    double sum = evaluateFunction(funcName, a) + evaluateFunction(funcName, b);
    for (int i = 1; i < n; i++) {
        double x = a + i * h;
        sum += (i % 2 == 0 ? 2 : 4) * evaluateFunction(funcName, x);
    }
    double I_old = (h / 3.0) * sum;
    result.iterations = 1; result.subdivisions = n;

    for (int iter = 2; iter <= maxIterations; iter++) {
        n *= 2; h = (b - a) / n;
        double sum_new = evaluateFunction(funcName, a) + evaluateFunction(funcName, b);
        for (int i = 1; i < n; i++) {
            double x = a + i * h;
            sum_new += (i % 2 == 0 ? 2 : 4) * evaluateFunction(funcName, x);
        }
        double I_new = (h / 3.0) * sum_new;
        double error = fabs(I_new - I_old) / 15.0;
        result.iterations = iter; result.subdivisions = n;
        result.step = h; result.error_estimate = error; result.value = I_new;
        if (error < epsilon) break;
        I_old = I_new;
    }
    return result;
}

//  Парсинг 
string getParamValue(const string& request, const string& paramName) {
    string searchStr = paramName + "=";
    size_t pos = request.find(searchStr);
    if (pos == string::npos) return "";
    if (pos > 0) {
        char prev = request[pos - 1];
        if (prev != '&' && prev != '?' && prev != ' ') {
            searchStr = "&" + paramName + "=";
            pos = request.find(searchStr);
            if (pos != string::npos) pos += 1;
        }
    }
    if (pos == string::npos) return "";

    pos += paramName.length() + 1;
    size_t end = request.find("&", pos);
    if (end == string::npos) {
        end = request.find(" ", pos);
        if (end == string::npos) end = request.length();
    }

    string value = request.substr(pos, end - pos);
    while (!value.empty() && (value.back() == '\r' || value.back() == '\n'))
        value.pop_back();
    return value;
}

double safeStod(const string& str, double defaultValue = 0.0) {
    if (str.empty()) return defaultValue;
    try {
        string cleaned = str;
        for (char& c : cleaned) if (c == ',') c = '.';
        return stod(cleaned);
    }
    catch (...) { return defaultValue; }
}

int safeStoi(const string& str, int defaultValue = 0) {
    if (str.empty()) return defaultValue;
    try { return stoi(str); }
    catch (...) { return defaultValue; }
}

// Понимаем запрос
string handleRequest(const string& request) {
    stringstream response;

    if (request.find("task=sle") != string::npos) {
        int size = safeStoi(getParamValue(request, "size"), 3);
        if (size < 2) size = 2;
        if (size > 5) size = 5;

        vector<vector<double>> A(size, vector<double>(size, 0));
        vector<double> b(size, 0);

        for (int i = 1; i <= size; i++) {
            for (int j = 1; j <= size; j++) {
                A[i - 1][j - 1] = safeStod(getParamValue(request, "a" + to_string(i) + to_string(j)), 0.0);
            }
            b[i - 1] = safeStod(getParamValue(request, "b" + to_string(i)), 0.0);
        }

        vector<double> solution = solveCramer(A, b);
        double det = computeDeterminant(A);

        response << "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n";
        if (solution.empty()) {
            response << "{\"result\": \"No solution (detA = 0)\", \"error\": true, \"details\": {\"determinant\": " << det << "}}";
        }
        else {
            response << "{\"result\": \"SLE solved (" << size << "x" << size << ")\", \"error\": false, \"details\": {";
            response << "\"determinant\": " << det << ", \"solution\": [";
            for (int i = 0; i < solution.size(); i++) {
                if (i > 0) response << ", ";
                response << solution[i];
            }
            response << "]}}";
        }
    }
    else if (request.find("task=iterative_sle") != string::npos) {
        int size = safeStoi(getParamValue(request, "size"), 3);
        if (size < 2) size = 2;
        if (size > 5) size = 5;

        vector<vector<double>> A(size, vector<double>(size, 0));
        vector<double> b(size, 0), x0(size, 0);

        for (int i = 1; i <= size; i++) {
            for (int j = 1; j <= size; j++)
                A[i - 1][j - 1] = safeStod(getParamValue(request, "a" + to_string(i) + to_string(j)), 0.0);
            b[i - 1] = safeStod(getParamValue(request, "b" + to_string(i)), 0.0);
        }

        string method = getParamValue(request, "method");
        string initType = getParamValue(request, "initType");
        double epsilon = safeStod(getParamValue(request, "epsilon"), 0.001);
        int maxIter = safeStoi(getParamValue(request, "maxIter"), 1000);

        if (initType == "custom") {
            for (int i = 1; i <= size; i++)
                x0[i - 1] = safeStod(getParamValue(request, "x0_" + to_string(i)), 0.0);
        }

        IterativeResult result = (method == "seidel") ?
            gaussSeidel(A, b, x0, epsilon, maxIter) :
            jacobi(A, b, x0, epsilon, maxIter);

        stringstream ss;
        ss << (result.converged ? "Solution found in " : "Method did not converge. Last approximation after ")
            << result.iterations << " iterations";

        response << "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n";
        response << "{\"result\": \"" << ss.str() << "\", \"converged\": " << (result.converged ? "true" : "false")
            << ", \"details\": {\"method\": \"" << result.method_name << "\", \"solution\": [";
        for (int i = 0; i < result.solution.size(); i++) {
            if (i > 0) response << ", ";
            response << result.solution[i];
        }
        response << "], \"iterations\": " << result.iterations << ", \"residual\": " << result.residual;
        if (!result.warning.empty())
            response << ", \"warning\": \"" << result.warning << "\"";
        response << "}}";
    }
    else if (request.find("task=integration") != string::npos) {
        string funcName = getParamValue(request, "function");
        string method = getParamValue(request, "method");
        string mode = getParamValue(request, "mode");
        double a = safeStod(getParamValue(request, "a"), 0.0);
        double b = safeStod(getParamValue(request, "b"), 1.0);
        if (funcName.empty()) funcName = "xquad";

        IntegrationResult result;
        if (mode == "auto") {
            double epsilon = safeStod(getParamValue(request, "epsilon"), 0.001);
            if (epsilon < 1e-10) epsilon = 1e-10;
            result = (method == "simpson") ? simpsonAuto(funcName, a, b, epsilon) : trapezoidalAuto(funcName, a, b, epsilon);
        }
        else {
            int n = safeStoi(getParamValue(request, "n"), 100);
            if (n < 2) n = 2;
            result = (method == "simpson") ? simpsonFixed(funcName, a, b, n) : trapezoidalFixed(funcName, a, b, n);
        }

        stringstream ss;
        ss << "Integral solved" ;

        response << "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n";
        response << "{\"result\": \"" << ss.str() << " = " << result.value << "\", \"details\": {";
        response << "\"method\": \"" << result.method_name << "\", \"n\": " << result.subdivisions
            << ", \"h\": " << result.step << ", \"error_estimate\": " << result.error_estimate;
        if (result.iterations > 0)
            response << ", \"iterations\": " << result.iterations;
        response << "}}";
    }
    else if (request.find("task=nonlinear") != string::npos) {
        string funcName = getParamValue(request, "function");
        string method = getParamValue(request, "method");
        double epsilon = safeStod(getParamValue(request, "epsilon"), 0.0001);
        int maxIter = safeStoi(getParamValue(request, "maxIter"), 100);
        if (funcName.empty()) funcName = "cubic";

        RootFindingResult result;
        if (method == "newton") {
            double x0 = safeStod(getParamValue(request, "x0"), 1.0);
            result = newton(funcName, x0, epsilon, maxIter);
        }
        else {
            double a = safeStod(getParamValue(request, "a"), 2.0);
            double b = safeStod(getParamValue(request, "b"), 3.0);
            result = bisection(funcName, a, b, epsilon, maxIter);
        }

        stringstream ss;
        ss << (result.converged ? "Root found: x = " : "Method did not converge. Last approximation: x = ")
            << result.root;

        response << "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nAccess-Control-Allow-Origin: *\r\n\r\n";
        response << "{\"result\": \"" << ss.str() << "\", \"converged\": " << (result.converged ? "true" : "false")
            << ", \"details\": {\"method\": \"" << result.method_name << "\", \"root\": " << result.root
            << ", \"f_value\": " << result.f_value << ", \"iterations\": " << result.iterations;
        if (method == "bisection")
            response << ", \"interval\": \"" << result.a << ", " << result.b << "\"";
        else
            response << ", \"initial_guess\": " << result.initial_guess;
        response << "}}";
    }
    else {
        response << "HTTP/1.1 200 OK\r\n\r\n";
        response << "Server is working. Available tasks: sle, iterative_sle, integration, nonlinear";
    }

    return response.str();
}

void handleClient(SOCKET clientSocket) {
    activeThreads++;
    cout << "Thread " << this_thread::get_id() << " started. Active: " << activeThreads.load() << endl;

    char buffer[4096];
    int bytesReceived = recv(clientSocket, buffer, sizeof(buffer) - 1, 0);

    if (bytesReceived > 0) {
        buffer[bytesReceived] = '\0';
        string request(buffer);
        cout << "Thread " << this_thread::get_id() << " processing: ";
        cout << request.substr(0, min(150, (int)request.length())) << "..." << endl;

        string response = handleRequest(request);
        send(clientSocket, response.c_str(), response.length(), 0);
    }

    closesocket(clientSocket);
    activeThreads--;
    cout << "Thread " << this_thread::get_id() << " finished. Active: " << activeThreads.load() << endl;
}

int main() {
    setlocale(LC_ALL, "C");
    SetConsoleCP(1251);
    SetConsoleOutputCP(1251);

    WSADATA wsaData;
    WSAStartup(MAKEWORD(2, 2), &wsaData);

    SOCKET serverSocket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
    sockaddr_in serverAddr;
    serverAddr.sin_family = AF_INET;
    serverAddr.sin_addr.s_addr = INADDR_ANY;
    serverAddr.sin_port = htons(8080);

    bind(serverSocket, (sockaddr*)&serverAddr, sizeof(serverAddr));
    listen(serverSocket, SOMAXCONN);

    cout << "Server started on port 8080..." << endl;
    cout << "Waiting for connections..." << endl;

    while (true) {
        sockaddr_in clientAddr;
        int clientSize = sizeof(clientAddr);
        SOCKET clientSocket = accept(serverSocket, (sockaddr*)&clientAddr, &clientSize);

        if (clientSocket == INVALID_SOCKET) continue;

        thread clientThread(handleClient, clientSocket);
        clientThread.detach();
        cout << "New client connected. Active threads: " << activeThreads.load() << endl;
    }

    closesocket(serverSocket);
    WSACleanup();
    return 0;
}
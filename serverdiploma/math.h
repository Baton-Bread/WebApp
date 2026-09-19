// Определения мат. функции

#pragma once
#include <vector>
#include <string>

using namespace std;

struct IntegrationResult;

struct RootFindingResult;

struct IterativeResult;

double computeDeterminant(const vector<vector<double>>& mat); // Определитель

double evaluateFunction(const string& funcName, double x); // Какую функцию для решения выбираем

double numericalDerivative(const string& funcName, double x, double h = 1e-6); // Дифференцирование через конечную разницу

bool checkDiagonalDominance(const vector<vector<double>>& A); // Проверить диагональное преобладание матрицы

double computeResidual(const vector<vector<double>>& A, const vector<double>& x, const vector<double>& b); // Невязка


// СЛАУ
vector<double> solveCramer(const vector<vector<double>>& A, const vector<double>& b); // Метод Крамера
IterativeResult jacobi(const vector<vector<double>>& A, const vector<double>& b, 
    vector<double> x0, double eps, int maxIter); // Метод Якоби
IterativeResult gaussSeidel(const vector<vector<double>>& A, const vector<double>& b,
    vector<double> x0, double eps, int maxIter); // Метод Гаусс-Зейделя

// Нелинейные уравнения
RootFindingResult bisection(const string& funcName, double a, double b, double eps, int maxIter); // Метод бисекции
RootFindingResult newton(const string& funcName, double x0, double eps, int maxIter); // Метод Ньютона

// Интегрирование
IntegrationResult trapezoidalFixed(const string& funcName, double a, double b, int n); // Метод Трапеции (ручное задание шага)
IntegrationResult simpsonFixed(const string& funcName, double a, double b, int n); // Метод Симпсона (ручное задание шага)
IntegrationResult trapezoidalAuto(const string& funcName, double a, double b, double epsilon, int maxIterations = 20); // Метод Трапеции (Автоматическое задание шага)
IntegrationResult simpsonAuto(const string& funcName, double a, double b, double epsilon, int maxIterations = 20); // Метод Симпсона (Автоматическое задание шага)
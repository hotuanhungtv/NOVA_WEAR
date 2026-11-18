<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller gốc: nạp helper dùng chung + kết nối CSDL.
 * Mọi controller của NOVA Wear đều kế thừa lớp này.
 */
abstract class BaseController extends Controller
{
    /** @var list<string> */
    protected $helpers = ['url', 'form', 'text', 'nova'];

    protected BaseConnection $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->db = \Config\Database::connect();
    }

    protected function customerId(): int
    {
        return (int) session()->get('customer_id');
    }

    /** Gom lỗi validation thành mảng để hiện ở form. */
    protected function validationErrors(): array
    {
        return array_values($this->validator ? $this->validator->getErrors() : []);
    }
}

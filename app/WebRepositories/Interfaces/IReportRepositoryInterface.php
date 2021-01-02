<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IReportRepositoryInterface
{
    public function SalesReport();

    public function PurchaseReport();

    public function SalesReportByVehicle();
}

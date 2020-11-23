<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * @var ISaleRepositoryInterface
     */
    private $saleRepository;

    public function __construct(ISaleRepositoryInterface $saleRepository)
   {
       $this->saleRepository = $saleRepository;
   }

    public function index()
    {
        return $this->saleRepository->index();
    }


    public function create()
    {
        return $this->saleRepository->create();
    }


    public function store(Request $request)
    {
        $this->saleRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        //
    }
}

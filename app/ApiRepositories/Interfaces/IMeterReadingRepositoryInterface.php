<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\MeterReadingRequest;
use Illuminate\Http\Request;

interface IMeterReadingRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(MeterReadingRequest $meterReadingRequest,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\WebRepositories\Interfaces\ICountryRepositoryInterface;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * @var ICountryRepositoryInterface
     */
    private $countryRepository;

    public function __construct(ICountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function index()
    {
        return $this->countryRepository->index();
    }


    public function create()
    {
        //
    }


    public function store(CountryRequest $countryRequest)
    {
        return $this->countryRepository->store($countryRequest);
    }


    public function show(Country $country)
    {
        //
    }


    public function edit($Id)
    {
        return $this->countryRepository->edit($Id);
    }


    public function update(Request $request, $Id)
    {
        return $this->countryRepository->update($request, $Id);
    }


    public function destroy(Request $request, $Id)
    {
        return $this->countryRepository->delete($request, $Id);
    }
}

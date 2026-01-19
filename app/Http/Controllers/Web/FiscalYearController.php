<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Requests\FiscalYear\FiscalYearRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FiscalYearController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.fiscalYear.';

    public function __construct(
        protected FiscalYearService $fiscalYearService
    ){}

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function index()
    {
        $this->authorize('fiscal_year');
        try{
            $fiscalYears = $this->fiscalYearService->getAllFiscalYears();

            return view($this->view.'index', compact('fiscalYears'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function create()
    {
        $this->authorize('fiscal_year');

        try{
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            return view($this->view.'create',compact('isBsEnabled'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function store(FiscalYearRequest $request)
    {
        try{
            $validatedData = $request->validated();


            if(AppHelper::ifDateInBsEnabled()){

                // Extract years from the 'year' field
//                $titleYears = explode('/', $validatedData['year']);
//                if (count($titleYears) !== 2) {
//                    return redirect()->back()->with('danger', __('message.invalid_format'));
//                }
//
//                $startYear = intval($titleYears[0]);
//                $endYear = intval($titleYears[1]);
//
//                // Check if start_date is in the correct year
//                if (substr($validatedData['start_date'], 0, 4) != $startYear) {
//                    return redirect()->back()->with('danger', __('message.start_date_mismatch'));
//                }
//
//                // Check if end_date is in the correct year
//                if (substr($validatedData['end_date'], 0, 4) != $endYear) {
//                    return redirect()->back()->with('danger', __('message.end_date_mismatch'));
//                }


                $validatedData['start_date'] = AppHelper::getEnglishDate($validatedData['start_date']);
                $validatedData['end_date'] = AppHelper::getEnglishDate($validatedData['end_date']);
            }

            $startDate = new \DateTime($validatedData['start_date']);
            $endDate = new \DateTime($validatedData['end_date']);

            $dateDifference = $startDate->diff($endDate)->days;

            if ($dateDifference < 360) {
                return redirect()->back()->with('danger', __('message.minimum_duration'));
            }

            if ($this->fiscalYearService->checkFiscalYear($validatedData['start_date'], $validatedData['end_date'])) {
                return redirect()->back()->with('danger', __('message.overlaps_existing'));
            }


            $this->fiscalYearService->storeFiscalYear($validatedData);
            return redirect()->route('admin.fiscal_year.index')->with('success', __('message.fiscal_year_created'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function show($id)
    {
        $this->authorize('fiscal_year');

        try{

            $fiscalYear = $this->fiscalYearService->findFiscalYearById($id);


            return view($this->view.'show', compact('fiscalYear'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse|Response
     */
    public function edit($id)
    {
        $this->authorize('fiscal_year');

        try{
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $fiscalYearDetail = $this->fiscalYearService->findFiscalYearById($id);
            return view($this->view.'edit', compact('fiscalYearDetail','isBsEnabled'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return RedirectResponse|Response
     */
    public function update(FiscalYearRequest $request, $id)
    {

        try{
            $validatedData = $request->validated();

            if(AppHelper::ifDateInBsEnabled()){
                // Extract years from the 'year' field
//                $titleYears = explode('/', $validatedData['year']);
//                if (count($titleYears) !== 2) {
//                    return redirect()->back()->with('danger', __('message.invalid_format'));
//                }
//
//                $startYear = intval($titleYears[0]);
//                $endYear = intval($titleYears[1]);
//
//                // Check if start_date is in the correct year
//                if (substr($validatedData['start_date'], 0, 4) != $startYear) {
//                    return redirect()->back()->with('danger', __('message.start_date_mismatch'));
//                }
//
//                // Check if end_date is in the correct year
//                if (substr($validatedData['end_date'], 0, 4) != $endYear) {
//                    return redirect()->back()->with('danger', __('message.end_date_mismatch'));
//                }

                $validatedData['start_date'] = AppHelper::getEnglishDate($validatedData['start_date']);
                $validatedData['end_date'] = AppHelper::getEnglishDate($validatedData['end_date']);
            }

            $startDate = new \DateTime($validatedData['start_date']);
            $endDate = new \DateTime($validatedData['end_date']);

            $dateDifference = $startDate->diff($endDate)->days;

            if ($dateDifference < 360) {
                return redirect()->back()->with('danger', __('message.minimum_duration'));
            }

            if ($this->fiscalYearService->checkFiscalYear($validatedData['start_date'], $validatedData['end_date'], $id)) {
                return redirect()->back()->with('danger', __('message.overlaps_existing'));
            }

            $this->fiscalYearService->updateFiscalYear($id,$validatedData);
            return redirect()->route('admin.fiscal_year.index')
                ->with('success', __('message.fiscal_year_updated'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('fiscal_year');

        try{
            DB::beginTransaction();
            $this->fiscalYearService->deleteFiscalYear($id);
            DB::commit();
            return redirect()->back()->with('success',  __('message.fiscal_year_deleted'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleCurrentStatus($id)
    {
        $this->authorize('fiscal_year');
        try {

            DB::beginTransaction();
            $this->fiscalYearService->changeCurrentStatus($id);
            DB::commit();
            return redirect()
                ->back()
                ->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}

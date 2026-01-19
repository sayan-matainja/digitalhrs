<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Imports\HolidaysImport;
use App\Requests\Holiday\HolidayRequest;
use App\Services\Holiday\HolidayService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class HolidayController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.holiday.';

    private HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index(Request $request)
    {
        $this->authorize('list_holiday');
        try {
            $filterParameters['event_year'] = $request->event_year ?? Carbon::now()->format('Y');
            $filterParameters['event'] = $request->event ?? null;
            $filterParameters['month'] = $request->month ?? null;
            if (AppHelper::ifDateInBsEnabled()) {
                $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                $filterParameters['event_year'] = $request->event_year ?? $nepaliDate['year'];
            }
            $months = AppHelper::MONTHS;
            $select = ['id', 'event', 'event_date', 'is_active'];
            $holidays = $this->holidayService->getAllHolidayLists($filterParameters, $select);
            return view($this->view . 'index', compact('holidays',
                'filterParameters',
                'months'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        $this->authorize('create_holiday');
        try {
            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $this->authorize('create_holiday');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->holidayService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.holidays.index')->with('success',  __('message.holidays_added'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $this->authorize('show_holiday');
            $holiday = $this->holidayService->findHolidayDetailById($id);
            $holiday->event_date = AppHelper::formatDateForView($holiday->event_date);
            return response()->json([
                'data' => $holiday,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function edit($id): Factory|View|RedirectResponse|Application
    {
        $this->authorize('edit_holiday');
        try {
            $holidayDetail = $this->holidayService->findHolidayDetailById($id);
            if (AppHelper::ifDateInBsEnabled()) {
                $holidayDetail['event_date'] = AppHelper::dateInYmdFormatEngToNep($holidayDetail['event_date']);
            }
            return view($this->view . 'edit', compact('holidayDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(HolidayRequest $request, $id): RedirectResponse
    {
        $this->authorize('edit_holiday');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->holidayService->update($validatedData, $id);
            DB::commit();
            return redirect()->route('admin.holidays.index')->with('success', __('message.holidays_updated'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleStatus($id): RedirectResponse
    {
        $this->authorize('edit_holiday');
        try {
            DB::beginTransaction();
            $this->holidayService->toggleHolidayStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.holiday_status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id): RedirectResponse
    {
        $this->authorize('delete_holiday');
        try {
            DB::beginTransaction();
            $this->holidayService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.holidays_removed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function holidayImport(): Factory|View|Application
    {
        $this->authorize('import_holiday');
        return view($this->view . 'importHolidays');
    }

    /**
     * @throws AuthorizationException
     */
    public function importHolidays(Request $request)
    {
        $this->authorize('import_holiday');
        try {
            $validate = $request->validate([
                'file' => 'required|file|mimes:csv,txt'
            ]);
            $holidayCSV = $request->file;
            $handle = fopen($holidayCSV, "r");
            $header = fgetcsv($handle, 0, ',');
            $countHeader = count($header);

            if ($countHeader < 5 && in_array('event', $header) && in_array('event_date', $header) && in_array('note', $header) && in_array('is_public_holiday', $header)) {

                Excel::import(new HolidaysImport, $holidayCSV);
                return redirect()->route('admin.holidays.index')->with('success', __('message.holidays_imported'));
            } else {
                return redirect()->route('admin.holidays.index')->with('danger', __('message.holidays_import_error'));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}

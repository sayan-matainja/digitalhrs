<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\AppSettingRepository;
use App\Repositories\AttendanceSettingRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\PaymentCurrencyRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Traits\CustomAuthorizesRequests;
use Database\Seeders\EmployeeCodeSeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AttendanceSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.attendanceSetting.';


    public function __construct(protected AttendanceSettingRepository $attendanceSettingRepository
    )
    {}

    public function index()
    {
        try {
            $select=['*'];
            $attendanceSettings = $this->attendanceSettingRepository->getAll($select);

            return view($this->view . 'index', compact('attendanceSettings'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(Request $request)
    {
        $this->authorize('attendance_setting');

        try {
            $rules = [
                'attendance_method' => 'sometimes|array',
                'attendance_method.*' => 'sometimes|string|in:default,biometric,nfc,qr',
                'attendance_limit' => 'sometimes|integer|min:1',
                'attendance_note' => 'sometimes|in:0,1',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }

            $values = $request->input('attendance_method', []);
            if (in_array('default', $values) && in_array('biometric', $values)) {
                return response()->json([
                    'success' => false,
                    'errors'  => [
                        'attendance_method' => ['Cannot select both "default" and "biometric".']
                    ]
                ], 422);
            }

            DB::beginTransaction();

            $attendanceSettings = $this->attendanceSettingRepository->getAll();

            foreach ($attendanceSettings as $attendanceSetting) {
                $slug = $attendanceSetting->slug;
                $data = [];

                if ($slug === 'attendance_method' && $request->has('attendance_method')) {
                    $data['values'] = $values;
                } elseif ($slug === 'attendance_limit' && $request->has('attendance_limit')) {
                    $data['value'] = $request->input('attendance_limit');
                } elseif ($slug === 'attendance_note' && $request->has('attendance_note')) {
                    $data['status'] = $request->input('attendance_note');
                }

                if (!empty($data)) {
                    $this->attendanceSettingRepository->update($attendanceSetting, $data);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('message.attendance_setting_update')
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }


    public function toggleStatus(Request $request, $id)
    {
        $this->authorize('attendance_setting');

        try {
            DB::beginTransaction();
            $this->attendanceSettingRepository->toggleStatus($id);
            DB::commit();

            $message = __('message.status_changed');
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $exception) {
            DB::rollBack();

            if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }



}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IdCardSetting;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\EmployeeCard\EmployeeCardRequest;
use App\Services\EmployeeCardSetting\EmployeeCardSettingService;
use App\Traits\ImageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\Exceptions\UnknownTypeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Browsershot\Browsershot;

class EmployeeCardController extends Controller
{
    use ImageService;

    public function __construct(protected UserRepository              $userRepo,
                                protected CompanyRepository           $companyRepo,
                                protected EmployeeCardSettingService  $cardSettingService,

    ){}


    /**
     * @throws Exception
     */
    public function index()
    {
        $settings = $this->cardSettingService->getAllCardSetting();

        return view('admin.employeeCard.index', compact('settings'));
    }
    /**
     * @throws Exception
     */
    public function create()
    {
        $availableFields = [
            'phone'         => 'Phone',
            'email'         => 'Email',
            'department'    => 'Department',
            'joining_date'  => 'Joining Date',
            'employee_code' => 'Employee Code',
            'blood_group'       => 'Blood Group',
            'address'       => 'Address',
        ];

        return view('admin.employeeCard.create', compact( 'availableFields'));
    }

    /**
     * @throws Exception
     */
    public function save(EmployeeCardRequest $request)
    {
        try
        {
            $validatedData =$request->validated();

            $validatedData['slug'] = Str::slug($validatedData['title']);

            $filledCount = collect($validatedData['extra_fields_order'])->filter()->count();

            if ($filledCount < 1 || $filledCount > 4) {
                return back()->withErrors(['error' => 'Please select 1 to 4 extra fields.']);
            }

            $values = array_filter($validatedData['extra_fields_order']);
            if (count($values) !== count(array_unique($values))) {
                return back()->withErrors(['error' => 'Duplicate fields are not allowed.']);
            }

            DB::beginTransaction();
                $this->cardSettingService->saveCardTemplate($validatedData);
            DB::commit();
            return redirect()->route('admin.card.template-list')->with('success', 'ID Card settings saved successfully!');
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function edit($id)
    {
        $setting = $this->cardSettingService->findCardSettingById($id);

        $availableFields = [
            'phone'         => 'Phone',
            'email'         => 'Email',
            'department'    => 'Department',
            'joining_date'  => 'Joining Date',
            'employee_code' => 'Employee Code',
            'blood_group'       => 'Blood Group',
            'address'       => 'Address',
        ];
        $imagePath = IdCardSetting::UPLOAD_PATH;

        return view('admin.employeeCard.edit', compact('setting', 'availableFields','imagePath'));
    }

    /**
     * @throws Exception
     */
    public function update(EmployeeCardRequest $request, $id)
    {
        try
        {
            $validatedData =$request->validated();

            $filledCount = collect($validatedData['extra_fields_order'])->filter()->count();

            if ($filledCount < 1 || $filledCount > 4) {
                return back()->withErrors(['error' => 'Please select 1 to 4 extra fields.']);
            }

            $values = array_filter($validatedData['extra_fields_order']);
            if (count($values) !== count(array_unique($values))) {
                return back()->withErrors(['error' => 'Duplicate fields are not allowed.']);
            }
            DB::beginTransaction();
                $this->cardSettingService->updateCardTemplate($id,$validatedData);
            DB::commit();
            return redirect()->route('admin.card.template-list')->with('success', 'ID Card template updated successfully!');
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }


    public function delete($id)
    {
        try
        {
            DB::beginTransaction();
            $this->cardSettingService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', 'ID card template successfully deleted.');
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function toggleIsActive($id)
    {
        try
        {
            DB::beginTransaction();
            $this->cardSettingService->toggleIsActive($id);
            DB::commit();
            return redirect()->back()->with('success', 'ID card template is active status changed.');
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }
     public function makeDefault($id)
    {
        try
        {
            DB::beginTransaction();
            $this->cardSettingService->makeDefault($id);
            DB::commit();
            return redirect()->back()->with('success', 'ID card template made as default.');
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    /**
     * @throws UnknownTypeException
     * @throws Exception
     */
    public function show($id)
    {

        $settings = $this->cardSettingService->findCardSettingById($id);
        $assetPath = IdCardSetting::UPLOAD_PATH;
        $orderedFields = array_values($settings->extra_fields_order ?? []);

        if (empty($orderedFields)) {
            $orderedFields = ['department', 'email', 'phone', 'joining_date'];
        }

        $employeeData = [
            'name'          => 'John Snow',
            'employee_code' => 'EMP-00000',
            'department'    => 'Head Office',
            'designation'   => 'Software Engineer',
            'phone'         => '98********',
            'email'         => 'john.snow@example.com',
            'dob'            => '1990-12-12',
            'blood_group'   =>  'O+ve',
            'join_date'     => '2024-01-25',
            'photo'         => asset('assets/images/img.png'),
        ];

        $assets = [
            'front_logo'      => $settings->front_logo ? asset($assetPath . $settings->front_logo) : asset('assets/images/demo.png'),
            'back_logo'       => $settings->back_logo ? asset($assetPath . $settings->back_logo) : asset('assets/images/demo.png'),
            'signature_image' => $settings->signature_image ? asset($assetPath . $settings->signature_image) : asset('assets/images/signature.png'),
            'footer_text'     => $settings->footer_text ?? 'www.company.com',
        ];

        $qrCode = null;
        $barcode = null;

        $graphValue = 'EMP-00000';

        if ($settings->graph_type === 'qr') {
            $graphColor = '#000000';  // This fixes orange QR!
            list($r, $g, $b) = sscanf($graphColor, "#%02x%02x%02x");

            $qrCode = QrCode::format('svg')
                ->size(420)
                ->margin(1)
                ->color($r, $g, $b)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($graphValue);


        } elseif ($settings->graph_type === 'barcode') {

            $generator = new BarcodeGeneratorSVG();
            $svg = $generator->getBarcode(
                $graphValue,
                $generator::TYPE_CODE_128,
                5, 200,
                '#000000'
            );
            $barcode = str_replace('<svg ', '<svg style="background:transparent;" ', $svg);

        }

        return view('admin.employeeCard.card', [
            'employee'    => (object)$employeeData,
            'choices'     => $settings,
            'extra_fields'=> $orderedFields,
            'assets'      => (object) $assets,
            'qrCode'      => $qrCode,
            'barcode'     => $barcode,
        ]);
    }

    /**
     * @throws UnknownTypeException
     * @throws Exception
     */
    public function viewCard($employeeCode)
    {
        $employee = $this->userRepo->findByEmployeeCode(
            $employeeCode,
            ['id', 'name', 'avatar', 'employee_code', 'department_id', 'post_id', 'phone', 'email', 'dob', 'joining_date'],
            ['department:id,dept_name', 'post:id,post_name']
        );

        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        $settings = $this->cardSettingService->defaultCardSetting();
        if(!isset($settings)){
            return redirect()->back()->with('danger','Select default template to view Id Card.');
        }
        $assetPath = IdCardSetting::UPLOAD_PATH;
        $imagePath = User::AVATAR_UPLOAD_PATH;
        $orderedFields = array_values($settings->extra_fields_order ?? []);

        if (empty($orderedFields)) {
            $orderedFields = ['department', 'email', 'phone', 'joining_date'];
        }

        $employeeData = [
            'name'          => $employee->name,
            'employee_code' => $employee->employee_code,
            'department'    => $employee->department?->dept_name ?? '',
            'designation'   => $employee->post?->post_name ?? '',
            'phone'         => $employee->phone ?? '',
            'email'         => $employee->email ?? '',
            'dob'            => $employee->dob ?? '',
            'blood_group'   =>  '',
            'join_date'     => $employee->joining_date ? $employee->joining_date : '',
            'photo'         => $employee->avatar
                ? asset($imagePath . $employee->avatar)
                : asset('assets/images/img.png'),
        ];

        $assets = [
            'front_logo'      => $settings->front_logo ? asset($assetPath . $settings->front_logo) : asset('assets/images/demo.png'),
            'back_logo'       => $settings->back_logo ? asset($assetPath . $settings->back_logo) : asset('assets/images/demo.png'),
            'signature_image' => $settings->signature_image ? asset($assetPath . $settings->signature_image) : asset('assets/images/signature.png'),
            'footer_text'     => $settings->footer_text ?? 'www.company.com',
        ];

        $qrCode = null;
        $barcode = null;

        $graphValue = $employee->{$settings->graph_field ?? 'employee_code'} ?? $employee->employee_code;

        if ($settings->graph_type === 'qr') {
            $graphColor = '#000000';  // This fixes orange QR!
            list($r, $g, $b) = sscanf($graphColor, "#%02x%02x%02x");

            $qrCode = QrCode::format('svg')
                ->size(420)
                ->margin(1)
                ->color($r, $g, $b)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($graphValue);

        } elseif ($settings->graph_type === 'barcode') {
            $generator = new BarcodeGeneratorSVG();
            $svg = $generator->getBarcode(
                $graphValue,
                $generator::TYPE_CODE_128,
                5, 200,
                '#000000'
            );
            $barcode = str_replace('<svg ', '<svg style="background:transparent;" ', $svg);

        }

        return view('admin.employeeCard.card', [
            'employee'    => (object) $employeeData,
            'choices'     => $settings,
            'extra_fields'=> $orderedFields,
            'assets'      => (object) $assets,
            'qrCode'      => $qrCode,
            'barcode'     => $barcode,
        ]);
    }

    /**
     * @throws UnknownTypeException
     */
    public function downloadCard($employeeCode)
    {
        $employee = $this->userRepo->findByEmployeeCode(
            $employeeCode,
            ['id', 'name', 'avatar', 'employee_code', 'department_id', 'post_id', 'phone', 'email', 'dob', 'joining_date'],
            ['department:id,dept_name', 'post:id,post_name']
        );

        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        $settings = $this->cardSettingService->defaultCardSetting();
        if (!isset($settings)) {
            return redirect()->back()->with('danger', 'Select default template to view Id Card.');
        }

        $assetPath = IdCardSetting::UPLOAD_PATH;
        $imagePath = User::AVATAR_UPLOAD_PATH;
        $orderedFields = array_values($settings->extra_fields_order ?? []);

        if (empty($orderedFields)) {
            $orderedFields = ['department', 'email', 'phone', 'joining_date'];
        }

        $employeeData = [
            'name'          => $employee->name,
            'employee_code' => $employee->employee_code,
            'department'    => $employee->department?->dept_name ?? '',
            'designation'   => $employee->post?->post_name ?? '',
            'phone'         => $employee->phone ?? '',
            'email'         => $employee->email ?? '',
            'dob'            => $employee->dob ?? '',
            'blood_group'   => '',
            'join_date'     => $employee->joining_date ? $employee->joining_date : '',
            'photo'         => $employee->avatar
                ? asset($imagePath . $employee->avatar)
                : asset('assets/images/img.png'),
        ];

        $assets = [
            'front_logo'      => $settings->front_logo ? asset($assetPath . $settings->front_logo) : asset('assets/images/demo.png'),
            'back_logo'       => $settings->back_logo ? asset($assetPath . $settings->back_logo) : asset('assets/images/demo.png'),
            'signature_image' => $settings->signature_image ? asset($assetPath . $settings->signature_image) : asset('assets/images/signature.png'),
            'footer_text'     => $settings->footer_text ?? 'www.company.com',
        ];

        $qrCode = null;
        $barcode = null;

        $graphValue = $employee->{$settings->graph_field ?? 'employee_code'} ?? $employee->employee_code;

        if ($settings->graph_type === 'qr') {
            $graphColor = '#000000';  // This fixes orange QR!
            list($r, $g, $b) = sscanf($graphColor, "#%02x%02x%02x");

            $qrCode = QrCode::format('svg')
                ->size(420)
                ->margin(1)
                ->color($r, $g, $b)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($graphValue);

        } elseif ($settings->graph_type === 'barcode') {
            $generator = new BarcodeGeneratorSVG();
            $svg = $generator->getBarcode(
                $graphValue,
                $generator::TYPE_CODE_128,
                5, 200,
                '#000000'
            );
            $barcode = str_replace('<svg ', '<svg style="background:transparent;" ', $svg);

        }

        // Generate PDF
        $pdf = Pdf::loadView('admin.employeeCard.card', [
            'employee'     => (object) $employeeData,
            'choices'      => $settings,
            'extra_fields' => $orderedFields,
            'assets'       => (object) $assets,
            'qrCode'       => $qrCode,
            'barcode'      => $barcode,
        ]);

        return $pdf->download('employee-card-' . $employee->employee_code . '.pdf');
    }
}

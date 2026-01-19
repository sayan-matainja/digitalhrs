<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\ThemeColor\ThemeColorRequest;
use App\Services\ThemeSetting\ThemeSettingService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ThemeSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.themeColor.';


    public function __construct(protected ThemeSettingService $themeSettingService)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('theme_setting');
        try {
            $themeDetail = $this->themeSettingService->getAllThemes();
            return view($this->view . 'index', compact('themeDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(ThemeColorRequest $request)
    {
        $this->authorize('theme_setting');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->themeSettingService->saveTheme($validatedData);
            DB::commit();
            return redirect()->route('admin.theme-color-setting.index')->with('success', __('message.theme_color_create'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.theme-color-setting.index')
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    /**
     * @throws AuthorizationException
     */
    public function update(ThemeColorRequest $request, $id)
    {

        $this->authorize('theme_setting');
        try {

            $validatedData = $request->validated();



            DB::beginTransaction();
            $this->themeSettingService->updateTheme($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.theme-color-setting.index')
                ->with('success', __('message.theme_color_update'));

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.theme-color-setting.index')
                ->with('danger', $e->getMessage())
                ->withInput();

        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Services\Admin\AdminService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = 'admin/dashboard/';

    private UserRepository $userRepo;
    private AdminService $adminService;
    private CompanyRepository $companyRepo;

    public function __construct(UserRepository $userRepo,CompanyRepository $companyRepo, AdminService $adminService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:admin')->except('logout');
        $this->userRepo = $userRepo;
        $this->companyRepo = $companyRepo;
        $this->adminService = $adminService;
    }

    public function showAdminLoginForm(): View|Factory|Application|RedirectResponse
    {
        $select = ['logo', 'name'];
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::check()) {
            return redirect('/'); // Redirect logged-in users to their own dashboard
        }

        $companyDetail = $this->companyRepo->getCompanyDetail($select);
        return view('auth.login', compact('companyDetail'));
    }

    public function login(Request $request)
    {
        try {

            $this->validateLogin($request);

            $this->checkCredential($request); // Validates credentials and throws exceptions if invalid


            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }


            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:admin,employee',
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function username()
    {
        return 'email';
    }

    /**
     * @throws Exception
     */
    public function checkCredential($request)
    {
        $select = ['id', 'name', 'email', 'username', 'password'];
        $isAdmin = $this->checkAdmin($request);
        $loginField = $request->get('email'); // Initially assume email

        // Fetch user/admin based on user_type
        if ($isAdmin) {
            $user = $this->adminService->getAdminByAdminEmail($loginField, $select)
                ?? $this->adminService->getAdminByAdminName($loginField, $select);
        } else {
            $user = $this->userRepo->getUserByUserEmail($loginField, $select)
                ?? $this->userRepo->getUserByUserName($loginField, $select);
        }
        $request['login_type'] = $user && $user->email === $loginField ? 'email' : 'username';

        if (!$user) {
            throw new Exception(__('auth.username_not_found'));
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            throw new Exception(__('auth.invalid_credentials'));
        }

        // Adjust login field based on what was matched
        if ($request['login_type'] === 'username') {
            $request['username'] = $loginField;
        }

    }

    protected function attemptLogin(Request $request)
    {
        $isAdmin = $this->checkAdmin($request);
        $guard ='admin';

        if($isAdmin){
            return Auth::guard($guard)->attempt(
                $this->credentials($request),
                $request->boolean('remember')
            );
        }else{
            return $this->guard()->attempt(
                $this->credentials($request),
                $request->boolean('remember')
            );
        }



    }

    protected function credentials(Request $request)
    {
        return [$request['login_type'] => $request->get($request['login_type']), 'password' => $request->get('password')];
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        $isAdmin = $this->checkAdmin($request);


        if(!$isAdmin){
            $customResponse = $this->authenticated($request, Auth::guard('admin')->user());
        }else{
            $customResponse = $this->authenticated($request, Auth::guard()->user());
        }

        $redirectTo = $this->redirectTo;

        return $customResponse ?: redirect()->intended($redirectTo);
    }

    public function logout(Request $request)
    {

        if (Auth::guard('admin')->check()){
            Auth::guard('admin')->logout();
        }else{
            Auth::guard()->logout();
        }


        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login') ;
    }

    public function checkAdmin($request): bool
    {
        return $request->get('user_type') === 'admin';
    }

}


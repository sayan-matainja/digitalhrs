<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\PostRepository;
use App\Requests\Post\PostRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.post.';


    public function __construct(protected PostRepository $postRepo, protected DepartmentRepository $departmentRepo,  protected CompanyRepository $companyRepository)
    {}


    public function index(Request $request)
    {

        $this->authorize('list_post');
        try {
            $filterParameters = [
                'name' =>  $request->name ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $postSelect = ['*'];
            $with = ['department:id,dept_name','employees:id,name,post_id,avatar'];

            $posts = $this->postRepo->getAllDepartmentPosts($filterParameters,$with,$postSelect);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            return  view($this->view . 'index', compact('posts',
                'filterParameters','companyDetail'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_post');
        try {
            $with = [];
            $select = ['id', 'dept_name'];
            $departmentDetail = $this->departmentRepo->getAllActiveDepartments($with, $select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            return view($this->view . 'create', compact('departmentDetail','companyDetail'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getAllPostsByBranchId($deptId)
    {
        try {
            $with = [];
            $select = ['post_name', 'id'];
            $posts = $this->postRepo->getAllActivePostsByDepartmentId($deptId,$with,$select);
            return response()->json([
                'data' => $posts
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());;
        }
    }

    public function store(PostRequest $request)
    {
        $this->authorize('create_post');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->postRepo->store($validatedData);
            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', __('message.post_add'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_post');
        try{
            $postDetail = $this->postRepo->getPostById($id);
            $with = [];
            $select = ['id', 'dept_name'];
            $departmentDetail = $this->departmentRepo->getAllActiveDepartments($with, $select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'edit',
                compact('postDetail','departmentDetail','companyDetail')
            );
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(PostRequest $request, $id)
    {
        $this->authorize('edit_post');
        try{
            $validatedData = $request->validated();
            $postDetail = $this->postRepo->getPostById($id);
            if(!$postDetail){
                throw new \Exception('Post Detail Not Found',404);
            }
            DB::beginTransaction();
            $post = $this->postRepo->update($postDetail,$validatedData);
            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', __('message.post_update'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }

    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_post');
        try {
            DB::beginTransaction();
            $this->postRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_post');
        try {
            $postDetail = $this->postRepo->getPostById($id);
            if (!$postDetail) {
                throw new \Exception(__('message.post_not_found'), 404);
            }
            if(!($postDetail->hasEmployee->isEmpty())){
                throw new Exception(__('message.post_delete_error'),400);
            }
            DB::beginTransaction();
                $this->postRepo->delete($postDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.post_delete'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}

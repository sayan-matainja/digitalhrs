<?php

namespace App\Repositories;


use App\Models\Post;

class PostRepository
{

    public function getAllDepartmentPosts($filterParameters,$with=[],$select=['*'])
    {
       return  Post::select($select)
            ->with($with)
            ->withCount('employees')
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('post_name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
           ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->where('dept_id', $filterParameters['department_id']);
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function store($validatedData)
    {
        return Post::create($validatedData)->fresh();
    }

    public function getPostById($id)
    {
        return Post::where('id',$id)->first();
    }

    public function delete($postDetail)
    {
        return $postDetail->delete();
    }

    public function update($postDetail,$validatedData)
    {
        return $postDetail->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $postDetail = Post::where('id',$id)->first();
        return $postDetail->update([
            'is_active' => !$postDetail->is_active,
        ]);
    }

    public function getAllActivePostsByDepartmentId($deptId,$with=[],$select=['*'])
    {
        return Post::with($with)
            ->select($select)
            ->where('is_active',1)
            ->where('dept_id',$deptId)
            ->get();
    }
}

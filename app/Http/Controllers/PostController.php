<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\trait\apiResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use apiResponse;
    use apiResponse;
    public function index(){
       $posts= Post::get();

           return $this->returnData('all data',$posts,'done');
     // return response()->json($posts);

    }
    public function store(Request $request){
        $posts= Post::find($request->id);
        if (!$posts)
            return $this->returnError('000','هذا البوست غير موجود');
        return $this->returnData('post','value','تم استرجاع البيانات',);




    }
}

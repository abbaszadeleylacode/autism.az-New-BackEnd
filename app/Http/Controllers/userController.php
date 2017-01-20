<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Istifadeci;
use App\Meqale;

class userController extends Controller
{
    public function register(Request $request)
    {
    	$this->validate($request,[
                'name'=>'required',
                'surname'=>'required',
                'email'=>'required',
                'password'=>'required',

                ]);
    	if(!Istifadeci::where('email',$request->email)->first()){
    		$new=new Istifadeci;
	    	$new->name=$request->name;
	    	$new->surname=$request->surname;
	    	$new->email=$request->email;
	    	$new->password=$request->password;
            $new->gender=$request->gender;
	    	$new->user_type='0';
	    	if($request->gender==0){
	    		$new->avatar='assets/images/avatar/male.jpg';
	    	}else{
	    		$new->avatar='assets/images/avatar/female.jpg';
	    	}
            $new->about=$request->about;
	    	$new->save();
	    	return back()->with('success','Uğurla qeydiyyatdan keçdiniz! Solda yerləşən qırmızı simgəyə çıqqıldadaraq sistemə daxil ola bilərsiniz!');
    	}else{
    		return back()->with('unsuccess','E-poçt ünvanı ilə artıq qeydiyyatdan keçilib! Qeydiyyat uğursuzdur.');
    	}
    }

    public function login(Request $request)
    {

    	$user=Istifadeci::where([['email',$request->email],['password',$request->password]])->first();
    	if(is_null($user)){
            return back()->with('false','E-poçt və ya şifrə səhvdir!');
    	}else{
            if($user->user_type=="-1"){
                return back()->with('banned','İstifadəçi sistemdən uzaqlaşdırılıb!');
            }else{
                $_SESSION['user']=$user->id;
                $_SESSION['user_type']=$user->user_type;

                return back()->with('true','Portala uğurla daxil oldunuz!');
            }
    	}
    }

    public function logout()
    {
        session_destroy();
        return redirect('/');
    }


    public function myProfile()
    {
        $user=Istifadeci::find($_SESSION['user']);
        return view('pages.myprofile',compact('user'));
    }


    public function saveChanges(Request $request)
    {
        $this->validate($request,[
                'name'=>'required',
                'surname'=>'required',
                'password'=>'required',

                ]);
        $user=Istifadeci::find($_SESSION['user']);
        $user->name=$request->name;
        $user->surname=$request->surname;
        $user->password=$request->password;   
        $user->about=$request->about;   

        if($request->hasFile('photo')){
            $file=$request->file('photo');
            $filename=time().'.'.$file->getClientOriginalExtension();
            $file->move('assets/images/avatar',$filename);
            $path='assets/images/avatar/'.$filename;
            $user->avatar=$path;
        }

        $user->save();    

        return back()->with('changed','Məlumatlarınız uğurla yeniləndi!');
    }

    // ----------------Meqale functionlar-----------------------------
    public function meqaleYaz(Request $request)
    {
       $new=new Meqale;

       $new->title=$request->title;
       $new->content=$request->content;
       $new->hekim_id=$_SESSION['user'];

       if($request->hasFile('picture')){
           $file=$request->file('picture');
           $filename=time().'.'.$file->getClientOriginalExtension();
           $file->move('assets/images/posts',$filename);
           $path='assets/images/posts/'.$filename;
           $new->img=$path;
       }

       if($request->hasFile('video')){
           $file=$request->file('video');
           $filename=time().'.'.$file->getClientOriginalExtension();
           $file->move('assets/videos/',$filename);
           $path='assets/videos/'.$filename;
           $new->video=$path;
       }
       $new->status='1';
       $new->save();

       return back();

    }

    public function showPost($id)
    {
      $post=Meqale::find($id);
      return view('pages.blogpost',compact('post'));
    }
}

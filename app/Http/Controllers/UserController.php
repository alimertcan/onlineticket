<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Order;
use App\Product;
use App\Http\Requests;
use Auth;
Use Session;

class UserController extends Controller
{
    public function getSignup(){
	return view('user.signup');
	}
	
	public function postTicket(Request $request){
	$id=$request->input('id');
	$order=\App\Order::find($id);
	if($order->status==1){
	$order->status=0;
	$order->save();
	return redirect()->route('user.profile')->with('success','Successfully Send a Cancel Request!');
	}
	else{
	$order->status=1;
	$order->save();
	
	}
	return redirect()->route('user.profile')->with('success','Successfully Get Back Your Request!');
	}
	
	public function postSignup(Request $request){
	$this->validate($request,[
	 'email' => 'email|required|unique:users',
	 'password' => 'required|min:4'
	 ]);
	$users=new User([
	'email'=> $request->input('email'),
	'password' => bcrypt($request->input('password'))
	]);
	$users->save();
	
	Auth::login($users);
	
	if(Session::has('oldurl')){
	$oldurl=Session::get('oldurl');
	Session::forget('oldurl');
	return redirect()->to($oldurl);
	}
	
	return redirect()->route('user.profile');
	}
	 public function getSignin(){
	return view('user.signin');
	}
	 public function postSignin(Request $request){
	$this->validate($request,[
	 'email' => 'email|required',
	 'password' => 'required|min:4'
	 ]);
	
	if(Auth::attempt(['email'=> $request->input('email'),'password'=> $request->input('password')])){
	if(Session::has('oldurl')){
	$oldurl=Session::get('oldurl');
	Session::forget('oldurl');
	return redirect()->to($oldurl);
	}
	return redirect()->route('user.profile');
	}
	return redirect()->back();
	}
	 public function getProfile(){
	 $users = User::all();
	 $orders=Auth::user()->orders;
	 $orders->transform(function($order,$key){  //can edit each order collection  my cart is serialized 
			$order->cart=unserialize($order->cart);
			return $order;
	 });
	return view('user.profile',['orders'=>$orders],['users'=>$users]);
	}
	
	 public function getProfilestaff(){
	 $users = User::all();
	 $products =Product::all();
	 $orders=Auth::user()->orders;
	 $orders->transform(function($order,$key){  //can edit each order collection  my cart is serialized 
			$order->cart=unserialize($order->cart);
			return $order;
	 });
	return view('stafforder',['orders'=>$orders],['users'=>$users],['products'=>$products]);
	}
	
	public function getProfilestaff2($oid){
	$order1=Order::find($oid);
	$order1->payment_id=0;
	$order1->save();
	
	 $users = User::all();
	 $products =Product::all();
	 $orders=Auth::user()->orders;
	 $orders->transform(function($order,$key){  //can edit each order collection  my cart is serialized 
			$order->cart=unserialize($order->cart);
			return $order;
	 });
	return redirect()->route('stafforder');
	}
	
	
	public function poststaff(Request $request){
	$id=$request->input('id');
	$product=Product::find($id);
	if(!$product) {
    return response('Product not found', 404);
  }
	$product->stok = $request->input('stok');
	
	 $product->save(); 
	
	 return redirect()->route('stafforder',[$product->id])->with('success', 'Product Stock has been updated!');
	}
	
	 public function getLogout(){
	Auth::logout();
	return redirect()->route('user.signin');
	}
	public function geteditProfile(){
	 $users = User::all();
	 return view('user.editprofile',['users'=>$users]);
	 }
	public function posteditProfile(Request $request){
	
	$id=$request->input('id');
	$user=User::find($id);
	if(!$user) {
    return response('User not found', 404);
  }
	$user->first_name = $request->input('first_name');
	$user->last_name = $request->input('last_name');	
	 $user->save(); 
	 
	 
	 return redirect()->route('user.editprofile',[$user->id])->with('message', 'User has been updated!');
	 }
	
	 
}

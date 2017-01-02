<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use App\Cart;
use App\Order;
use App\Product;
use Stripe\Stripe;
use Stripe\Charge;
use Auth;

class ProductController extends Controller
{
    public function getIndex(){
	$products=\App\Product::all();
	return view('shop.index',['products' => $products]);
	}

	
	
	public function getticketindex($id){
	$product=\App\Product::find($id);
	return view('shop.ticket',['product' => $product]);
	}
	
	  public function getAddToCart(Request $request,$id){
	$product=\App\Product::find($id);
	$oldCart=Session::has('cart') ? Session::get('cart'):null;
	$cart = new Cart($oldCart);
	$cart->add($product, $product->id);
	
	$request ->session()->put('cart',$cart);

	return redirect()->route('product.index');
	}
	
	public function getremoveone($id){
	$oldCart=Session::has('cart') ? Session::get('cart'):null;
	$cart = new Cart($oldCart);
	$cart->removeone($id);
	
	if(count($cart->items) > 0){
	Session::put('cart',$cart);
	}
	else{
	 Session::forget('cart');
	}
	
	return redirect()->route('product.shoppingCart');
	}
	
	public function getremoveitem($id){
	$oldCart=Session::has('cart') ? Session::get('cart'):null;
	$cart = new Cart($oldCart);
	$cart->removeitem($id);
	
	if(count($cart->items) > 0){
	Session::put('cart',$cart);
	}
	else{
	 Session::forget('cart');
	}
	
	return redirect()->route('product.shoppingCart');
	}
	
	 public function getCart(){
	if(!Session::has('cart')){
		return view('shop.shopping-cart');
		}	
		$oldCart=Session::get('cart');
		$cart=new Cart($oldCart);
		return view('shop.shopping-cart',['products'=>$cart->items,'totalprice'=>$cart->totalprice]);
	}
	public function getCheckout(){
		if(!Session::has('cart')){
		return view('shop.shopping-cart');
		}
		$oldCart=Session::get('cart');
		$cart=new Cart($oldCart);
		$total=$cart->totalprice;
		return view('shop.checkout',['total'=>$total]);
	}
	public function postCheckout(Request $request){
		if(!Session::has('cart')){
		return redirect()->route('shop.shopping-cart');
		}
		$oldCart=Session::get('cart');
		$cart=new Cart($oldCart);
		
		\Stripe\Stripe::setApiKey('sk_test_mjwjvlDDBq2pOMpR0wYA9F0G');
		
		try{
			$charge=Charge::create(array(
				"amount" => $cart->totalprice * 100,
				"currency" => "usd",
				"source" => $request->input('stripeToken'), // obtained with Stripe.js
				"description" => "Test Charge"
			));
			$order=new Order();
			$order->cart= serialize($cart); 
			$order->address= $request->input('address');
			$order->name= $request->input('name');
			$order->payment_id= $charge->id;
			Auth::user()->orders()->save($order);
		} catch(\Exception $e){
			return redirect()->route('checkout')->with('error',$e->getMessage());
		}
		
		Session::forget('cart');
		return redirect()->route('product.index')->with('success','Successfully purchased products!');
		
	}
	
	public function postCheckout2(Request $request){
		if(!Session::has('cart')){
		return redirect()->route('shop.shopping-cart');
		}
		$oldCart=Session::get('cart');
		$cart=new Cart($oldCart);
		
	//	\Stripe\Stripe::setApiKey('sk_test_mjwjvlDDBq2pOMpR0wYA9F0G');
		
		try{
			/*$charge=Charge::create(array(
				"amount" => $cart->totalprice * 100,
				"currency" => "usd",
				"source" => $request->input('stripeToken'), // obtained with Stripe.js
				"description" => "Test Charge"
			));*/
			$order=new Order();
			$order->cart= serialize($cart); 
			$order->address= $request->input('address');
			$order->name= $request->input('name');
		//	$order->payment_id= $charge->id;
			Auth::user()->orders()->save($order);
		} catch(\Exception $e){
			return redirect()->route('checkout')->with('error',$e->getMessage());
		}
		
		Session::forget('cart');
		return redirect()->route('product.index')->with('success','Successfully purchased products!');
		
	}
	
	public function getTicket(Request $request){
	$search=$request->searh;
	$products=Product::where('title','LIKE',"%search%")->paginate(20);
	return response()->json([
		'model'=>$products
	]);
	}
	
	public function addTicket(Request $request){
	$this->validate($request,[
	 'title' => 'required',
	 'imagePath' => 'required|min:4',
	 'description' => 'required|min:4',
	 'price' => 'required'
	 ]);
	$products=new Product([
	'title'=> $request->input('title'),
	'imagePath'=> $request->input('imagePath'),
	'description'=> $request->input('description'),
	'price'=> $request->input('price')
	
	]);
	$products->save();
	
	return redirect()->route('product.index')->with('success','Successfully Add products!');
	
	
	}
	public function getCheckout2(){
		if(!Session::has('cart')){
		return view('shop.shopping-cart');
		}
		$oldCart=Session::get('cart');
		$cart=new Cart($oldCart);
		$total=$cart->totalprice;
		return view('shop.checkout2',['total'=>$total]);
	}
	
}

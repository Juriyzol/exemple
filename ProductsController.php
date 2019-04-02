<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Auth;
use Carbon\Carbon; 
use Session;
use Image;
use App\Category;
use App\Product;
use App\ProductsAttribute;
use App\ProductsImage;
use App\Coupon;
use App\User;
use App\Country;
use App\State;
use App\DeliveryAddress;
use App\Order;
use App\OrdersProduct;
use DB;


use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;



/*
	Правила валидации данных в админ-панели - \public\js\backend_js\matrix.form_validation.js
	
	Валидация форм на фронте - \public\js\frontend_js\main.js
	
	SET sql_mode = '';
	ALTER TABLE `delivery_addresses`  ADD `order_id` INT(50) NOT NULL  AFTER `user_id`;

*/
class ProductsController extends Controller
{
	
	/*
		Создается проверка на просмотр данной категории, изначально флаг установлен $permission = false
		если соответствие будет найдено то флажок поменяется на ТРУ.
		Это значение передается во вьюшку где далее при помощи ИФ-ов принимается решение о выводе информации.
		
		После получаем все категории при помощи запроса к модели категорий, при этом указываем что 
		так же хотим подгрузить связанные с моделью данные:
			Category::with('categories')->...
		
		Что возможно благодаря тому что в модели Категорий указана соответствующая взаимосвязь:
			public function categories() {
				return $this->hasMany('App\Category','parent_id');
			}

		Далее получаю информацию по категории, которую пользователь просматривает в данный момент.	
	*/

	
	
	
	
	public function products(Request $request, $url=null) {
		
    	$categoryCount = Category::where(['url'=>$url,'status'=>1])->count();
    	if($categoryCount==0) {
    		abort(404);
    	}					
		
		// проверка на разрешение просмотра для данного пользователя
		$permission = false;
		if ( Auth::check() ) 
		{
			$category = Category::where(['url'=>$url])->first();

			// dd( $category->id );
			
			$catPermission = DB::table('user_category')
			->select('category_id')
			->where([ 'user_id' => Auth::user()->id ])
			->get()
			->toArray();				
			
			foreach ($catPermission as $value) {
				if ($value->category_id == $category->id) {
					$permission = true;
				}
			}		
		}


		$categories = Category::with('categories')->where(['parent_id' => 0])->get()->toArray();
		
		foreach ($categories as &$category) {
			$category['categories'] = Category::with('categories')->where(['parent_id' => $category['id']])->orderBy("name")->get()->toArray();		
		}



    	$categoryDetails = Category::where(['url'=>$url])->first();
		
		$productInCategory = DB::table('product_category')
		->select('product_id')
		->where( ['category_id' => $categoryDetails->id] )
		->get()->toArray();			
		
		$catIDArr = [];
		foreach ($productInCategory as $value) {
			$catIDArr[] = $value->product_id;
		}			
		
		// dd( $catIDArr );
		
		
		$subCategories = Category::where(['parent_id'=>$categoryDetails->id])->get();		
		
		$productInSubCategories = [];
		foreach ($subCategories as $value) {
			$productInSubCategories[] = DB::table('product_category')
			->select('product_id')
			->where( ['category_id' => $value->id] )
			->get()->toArray();	
		}
		
		// $subCatIDArr = [];
		foreach ($productInSubCategories as $value) {
			foreach ($value as $val) {
				$catIDArr[] = $val->product_id;
			}
		}		
		
		// dd( $catIDArr );
		

		$subSubCategories = [];
		foreach ($subCategories as $value) {
			// dump( $value->id );
			$subSubCategories[] = Category::where(['parent_id'=>$value->id])->get();
		}

		// dd( $subSubCategories );


		$productInSubSubCategories = [];
		foreach ($subSubCategories as $value) {
			foreach ($value as $val) {
				// dump( $val );
				
				$productInSubSubCategories[] = DB::table('product_category')
				->select('product_id')
				->where( ['category_id' => $val->id] )
				->get()->toArray();
			}
		}

		// dd( $productInSubSubCategories );



		// $subCatIDArr = [];
		foreach ($productInSubSubCategories as $value) {
			foreach ($value as $val) {
				$catIDArr[] = $val->product_id;
			}
		}

		// dd( $catIDArr );


		$productsAll = Product::whereIn('id', $catIDArr)->where('status','1')->paginate(6);

		// dd( $productsAll );


		return view('products.listing')->with(
			// compact('categories', 'productsAll', 'categoryDetails', 'permission', 'filter', 'partsSubCategories')
			compact('categories', 'productsAll', 'categoryDetails', 'permission')
		);
		
	}
	
	
	
	
	
	
	
	
	/*
		Проверка на ниличие прав на просмотр товара данной категории
	*/
    public function product($id = null) {

        // Show 404 Page if Product is disabled
        $productCount = Product::where(['id'=>$id,'status'=>1])->count();
        if($productCount==0){
            abort(404);
        }

        // Детали по продукту
        $productDetails = Product::with('attributes')->where('id', $id)->first();
		
		// проверка на разрешение просмотра для данного пользователя 
		$permission = false;
		if ( Auth::check() ) {
			
			// к каким категориям привязан продукт
			$catID = DB::table('product_category')
			->select('category_id')
			->where( ['product_id' => $productDetails->id] )
			// ->first()->category_id;
			->get();
			
			// dd( $catID );
			
			// к каким категориям открыт доступ пользователю
			$catPermission = DB::table('user_category')
			->select('category_id')
			->where( ['user_id' => Auth::user()->id] )
			->get()
			->toArray();		
			
			// dd( $catPermission );
			
			
			foreach ($catPermission as $permissionValue) {
				
				foreach ($catID as $catValue) {

					if ($permissionValue->category_id == $catValue->category_id) {
						$permission = true;
					}
				}
			}		
		}		

		// dd( $permission );
		
		// информация нужна для формирования левого меню
		$categoryDetails = Category::where(['id'=>$productDetails->category_id])->first();
		// $categoryDetails = Category::where(['id'=>$catID])->first();
		
		// запрос на другие продукты из этой же категории
        // $relatedProducts = Product::where('id','!=',$id)->where(['category_id' => $productDetails->category_id])->get();
        $relatedProducts = Product::where('id','!=',$id)->get();

        
		
		
		
        // Get Product Alt Images
        $productAltImages = ProductsImage::where('product_id',$id)->get();

		
        
        $categories = Category::with('categories')->where(['parent_id' => 0])->get();
		foreach ($categories as &$category) {
		 	$category['categories'] = Category::with('categories')->where(['parent_id' => $category['id']])->orderBy("name")->get()->toArray();		
		}


        $total_stock = ProductsAttribute::where('product_id',$id)->sum('stock');

        return view('products.detail')->with(compact('productDetails','categories','productAltImages','total_stock','relatedProducts', 'permission', 'categoryDetails'));
    }
	
	
	
	
	public function addProduct(Request $request) {

		if ($request->isMethod('post')) {
			
			$data = $request->all();			
			
			// $image_tmp = Input::file('image');
			// dd( $image_tmp->getClientOriginalName() );
			

			$product = new Product;
			$product->category_id = $data['category_id'][0];
			$product->product_name = $data['product_name'];
			$product->product_code = $data['product_code'];
			
			// $product->save();
			// dd( '--' );
			
			
			// $product->product_color = $data['product_color'];
			if( !empty($data['description']) ) {
				$product->description = $data['description'];
			}else{
				$product->description = '';	
			}
			
			
			$product->price = $data['price'];

            if( empty($data['stock']) ) {
                $product->single_stock = 0;
            }else{
                $product->single_stock = $data['stock'];
            }


			// Upload Image
            if ($request->hasFile('image') ) {
				
            	$image_tmp = Input::file('image');
				
                if ($image_tmp->isValid()) {
                    // Upload Images after Resize
                    // $extension = $image_tmp->getClientOriginalExtension();
	                // $fileName = rand(111,99999).'.'.$extension;
	                $fileName = $image_tmp->getClientOriginalName();
                    $large_image_path = 'images/backend_images/product/large'.'/'.$fileName;
                    $medium_image_path = 'images/backend_images/product/medium'.'/'.$fileName;  
                    $small_image_path = 'images/backend_images/product/small'.'/'.$fileName;  

	                Image::make($image_tmp)->save($large_image_path);
 					Image::make($image_tmp)->resize(600, 600)->save($medium_image_path);
     				Image::make($image_tmp)->resize(300, 300)->save($small_image_path);

     				$product->image = $fileName; 
                }
            }else{
				$product->image = 'no_available_image.png';
			}
			
            if( empty($data['status']) ) {
                $status='0';
            }else{
                $status='1';
            }
            $product->status = $status;
			
			
			$product->save();


			foreach ($data['category_id'] as $value) {
				DB::table('product_category')
				->insert([
					'product_id' => $product->id,
					'category_id' => $value
				]);	
			}	
			
			return redirect()->back()->with('flash_message_success', 'Product has been added successfully');
		}
		
		/*
		$categories = Category::where(['parent_id' => 0])->get();

		$categories_drop_down = "<option value='' selected disabled>Select</option>";
		foreach($categories as $cat){
			$categories_drop_down .= "<option value='".$cat->id."'>".$cat->name."</option>";
			$sub_categories = Category::where(['parent_id' => $cat->id])->get();
			foreach($sub_categories as $sub_cat){
				$categories_drop_down .= "<option value='".$sub_cat->id."'>&nbsp;&nbsp;--&nbsp;".$sub_cat->name."</option>";	
			}	
		}
		*/
		
		$categories = Category::with('categories')->where(['parent_id' => 0])->get()->toArray();
		
		foreach ($categories as &$category) {
			
			$category['categories'] = Category::with('categories')->where(['parent_id' => $category['id']])->get()->toArray();
			
			foreach ($category['categories'] as &$cat) {
				$cat['categories'] = Category::with('categories')->where(['parent_id' => $cat['id']])->get()->toArray();		
			}			
		}		
		
		// dd( $categories );
		
		//echo "<pre>"; print_r($categories_drop_down); die;

		return view('admin.products.add_product')->with(compact('categories'));
	}  




	public function deleteProductImage($id=null){

		// Get Product Image
		$productImage = Product::where('id',$id)->first();

		// Get Product Image Paths
		$large_image_path = 'images/backend_images/product/large/';
		$medium_image_path = 'images/backend_images/product/medium/';
		$small_image_path = 'images/backend_images/product/small/';

		// Delete Large Image if not exists in Folder
        if(file_exists($large_image_path.$productImage->image)){
            unlink($large_image_path.$productImage->image);
        }

        // Delete Medium Image if not exists in Folder
        if(file_exists($medium_image_path.$productImage->image)){
            unlink($medium_image_path.$productImage->image);
        }

        // Delete Small Image if not exists in Folder
        if(file_exists($small_image_path.$productImage->image)){
            unlink($small_image_path.$productImage->image);
        }

        // Delete Image from Products table
        Product::where(['id'=>$id])->update(['image'=>'']);

        return redirect()->back()->with('flash_message_success', 'Product image has been deleted successfully');
	}


    public function deleteProductAltImage($id=null){

        // Get Product Image
        $productImage = ProductsImage::where('id',$id)->first();

        // Get Product Image Paths
        $large_image_path = 'images/backend_images/product/large/';
        $medium_image_path = 'images/backend_images/product/medium/';
        $small_image_path = 'images/backend_images/product/small/';

        // Delete Large Image if not exists in Folder
        if(file_exists($large_image_path.$productImage->image)){
            unlink($large_image_path.$productImage->image);
        }

        // Delete Medium Image if not exists in Folder
        if(file_exists($medium_image_path.$productImage->image)){
            unlink($medium_image_path.$productImage->image);
        }

        // Delete Small Image if not exists in Folder
        if(file_exists($small_image_path.$productImage->image)){
            unlink($small_image_path.$productImage->image);
        }

        // Delete Image from Products Images table
        ProductsImage::where(['id'=>$id])->delete();

        return redirect()->back()->with('flash_message_success', 'Product alternate mage has been deleted successfully');
    }


    public function addImages(Request $request, $id = null) {
		
        $productDetails = Product::where(['id' => $id])->first();

        $categoryDetails = Category::where(['id'=>$productDetails->category_id])->first();
        $category_name = $categoryDetails->name;

        if( $request->isMethod('post') ) {
			
            $data = $request->all();
            if ($request->hasFile('image')) {
                $files = $request->file('image');
                foreach($files as $file){
                    // Upload Images after Resize
                    $image = new ProductsImage;
                    $extension = $file->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/product/large'.'/'.$fileName;
                    $medium_image_path = 'images/backend_images/product/medium'.'/'.$fileName;  
                    $small_image_path = 'images/backend_images/product/small'.'/'.$fileName;  
                    Image::make($file)->save($large_image_path);
                    Image::make($file)->resize(600, 600)->save($medium_image_path);
                    Image::make($file)->resize(300, 300)->save($small_image_path);
                    $image->image = $fileName;  
                    $image->product_id = $data['product_id'];
                    $image->save();
                }   
            }

            return redirect('admin/add-images/'.$id)->with('flash_message_success', 'Product Images has been added successfully');

        }

        $productImages = ProductsImage::where(['product_id' => $id])->orderBy('id','DESC')->get();

        $title = "Add Images";
        return view('admin.products.add_images')->with(compact('title','productDetails','category_name','productImages'));
    }














	/*
		Получаем данные по авторизированному пользователю.
		Получаем данные по самому пользователю.
		Получаем список стран (он очень большой - нужно заменить его чем-то более актуальным).
		
		Проверяю существует ли адрес доставки привязанный к пользователю с данным идентификатором.
		Если адрес пустой то переменная $shippingDetails остается пустой, если адрес или адреса существуют то 
		загружаю массив адресов в переменную $shippingDetails
		
		Проверяю сессионный ИД сгенерированный ранее. По этому сессионному ИД обновляю данные в таблице корзины, добавляя почту пользователя.
		
		
		все нижеследующее происходит после отправки формы:
		
		Если при отправке запроса какие либо из данных пустые - вернутся на страницу с сообщением об ошибке. Проверка идет по полям 
		данных как по Билингу так и по Шипингу - во вьюшке все эти данные размещены в одной форме.
		При нажатии на чекбокс срабатывает обработчик по клику, логика обработки прописана в \public\js\frontend_js\main.js
		примерно в 274 строчке..
		$("#shipping_name").val($("#billing_name").val());
		
		Если валидация прошла успешно то обновляем билинг данные в таблице пользователя..
		
		ищу адрес доставки по идентификатору пользователя и отправленному адресу на доставку
		
		если в таблице адресов доставок существует запись с таким идентификатором пользователя и с таким 
		отправленным адресом доставки то помещаю эти данные в сессию и передаю управление на следующий метод - orderReview
		
		если же такой записи в БД НЕ существует, то создаю новую запись и далее помещаю эти данные в сессию и передаю управление на следующий метод - orderReview
	*/
    public function checkout(Request $request) {
		
        $user_id = Auth::user()->id;
        $user_email = Auth::user()->email;
        $userDetails = User::find($user_id);
        $countries = Country::get();
        // $states = State::get();
        $usaStates = State::where(['country_id'=>2])->get();
        $canadianStates = State::where(['country_id'=>1])->get();
		// dd($user_id.' - '.$user_email.' - '.$userDetails);
		
		
		// dd($canadianStates);
		
		
        // Проверка существования адреса доставки в БД для данного пользователя
        $shippingCount = DeliveryAddress::where('user_id', $user_id)->count();
        $shippingDetails = array();
        if($shippingCount > 0) {
            $shippingDetails = DeliveryAddress::where('user_id', $user_id)->get();
        }

		
		// dd( $shippingDetails );


        // Update cart table with user email
        $session_id = Session::get('session_id');
        DB::table('cart')->where(['session_id'=>$session_id])->update(['user_email'=>$user_email]);
		
		
		
        if($request->isMethod('post')) {
			
            $data = $request->all();
			
			// $data['billing_state'] = $data['billing_usa_states'] ? $data['billing_usa_states'] : $data['billing_canadian_states'];
			
			// dd( $data );
			
            
            // Если при отправке запроса какие либо из данных пустые - вернутся на страницу с сообщением об ошибке.
			// Проверяются данные и по билингу и по шипингу.
            if( empty($data['billing_name']) || 
				empty($data['billing_address']) || 
				empty($data['billing_city']) ||				
				empty($data['billing_state']) ||				
				empty($data['billing_country']) || 
				empty($data['billing_pincode']) || 
				empty($data['billing_mobile']) || 
				
				empty($data['shipping_name']) || 
				empty($data['shipping_address']) || 
				empty($data['shipping_city']) || 
				empty($data['shipping_state']) || 
				empty($data['shipping_country']) || 
				empty($data['shipping_pincode']) || 
				empty($data['shipping_mobile'])
			){    
				return redirect()->back()->with('flash_message_error', 'Please fill all fields to Checkout!');
            }



			// dd('stop');


            // Обновяем данные самого пользователя..?
            User::where('id',$user_id)->update([
				// 'name'=>$data['billing_name'],
				'address'=>$data['billing_address'],
				'city'=>$data['billing_city'],
				'state'=>$data['billing_state'],
				'pincode'=>$data['billing_pincode'],
				'country'=>$data['billing_country'],
				'mobile'=>$data['billing_mobile']
			]);


			// ищу адрес доставки по идентификатору пользователя и отправленному адресу на доставку 
			$delAdresses = DeliveryAddress::where( [
				'user_id' => $user_id, 
				'country' => $data['shipping_country'], 
				'state' => $data['shipping_state'], 
				'city' => $data['shipping_city'], 
				'address' => $data['shipping_address'] 
			])->first();


			// dd( $delAdresses );


            if( $delAdresses ) {
				
				Session::forget('delAdresses');
				Session::put('delAdresses', $delAdresses);
				
				return redirect()->action('ProductsController@orderReview');

            }else{
                // Add New Shipping Address
                $shipping = new DeliveryAddress;
                $shipping->user_id = $user_id;
                $shipping->user_email = $user_email;
                $shipping->name = $data['shipping_name'];
                $shipping->address = $data['shipping_address'];
                $shipping->city = $data['shipping_city'];
                $shipping->state = $data['shipping_state'];
                $shipping->pincode = $data['shipping_pincode'];
                $shipping->country = $data['shipping_country'];
                $shipping->mobile = $data['shipping_mobile'];
                $shipping->save();				
				
				dump('new shiping adress');
				
				Session::forget('delAdresses');
				Session::put('delAdresses', $shipping);
				
				return redirect()->action('ProductsController@orderReview');
            }
			
            // return redirect()->action('ProductsController@orderReview');
        }

        return view('products.checkout')->with(compact('userDetails','countries','shippingDetails', 'usaStates', 'canadianStates'));
    }

	
 
	
	
	
// ------------------------------------------------ логика для Работы со страйпом --------------------------------------------------------------------	

	
	
	
	/*
		Данные попадают сюда из формы со страницы order_review
		
		Получаем данные из формы, делим данные на сто для того что бы получить эквивалент целых чисел, 
		поскольку с фронта данные приходят в формате 100 = 1у.е.
		
		Проверяю существует ли пользователь с таким Емейлом и если да то создаю для него ордер и 
		перенаправляю на завершающую страничку.
		
		Если пользователь еще не зарегестрирован в нашем Страйпе, то создаем нового пользователя и 
		создаем платеж для него.
	*/
	public function charge(Request $request) {	
		
		$stripeEmail = $request->stripeEmail;
		$stripeToken = $request->stripeToken;
		$amount = (int)$request->amount;
		
		$priceDatainJSON = $request->priceDatainJSON;
		$shippingID = $request->shippingID;
		// $priceData = json_decode($priceDatainJSON);
		
		// dd( $shippingID );
		
		
		$grandTotalForCreateOrder = $amount / 100;
		
		$existCustomer = $this->getCustomerByEmail( $stripeEmail );

		
		if ( count($existCustomer->data) > 0 ) {
			
			$answer = $this->chargeCustomer( $existCustomer->data[0]->id, $amount, $currency='usd' );
			
			// return $answer;
			if( $answer ) {
				
				$this->createStripeOrder( $grandTotalForCreateOrder, $priceDatainJSON, $shippingID );
				
				Session::put('existCustomer', $existCustomer); // передаю информацию по юзеру страйпа в thanks(Request $request)
				
				return redirect('/thanks');
			}
			
		} else {
			
			$newCustomer = $this->createCustomer($stripeEmail, $stripeToken);			
			
			$answer = $this->chargeCustomer( $newCustomer->id, $amount, $currency='usd' );			
			
			// return $answer;
			if( $answer ) {
				
				Session::put('existCustomer', $existCustomer); // передаю информацию по юзеру страйпа в thanks(Request $request)
				
				$this->createStripeOrder( $grandTotalForCreateOrder, $priceDatainJSON, $shippingID );
				
				return redirect('/thanks');
			}
		}

    }

	public function chargeCustomer( $customerID, $amount, $currency ) {
		
		try {
			Stripe::setApiKey( env('STRIPE_SECRET_KEY') );

			$charge = Charge::create(array(
				'customer' => $customerID,
				'amount' => $amount,
				'currency' => $currency
			));

			return 'Charge successful, you get the course!';
			
		} catch (\Exception $ex) {
			
			return $ex->getMessage();
		}		
	}

	/*
		https://stripe.com/docs/api/customers/list - документация
		
		sobakas@gmail.com
	*/
	public function getCustomerByEmail($stripeEmail) {
		
		Stripe::setApiKey( env('STRIPE_SECRET_KEY') );

		$customer = Customer::all( ["email" => $stripeEmail] );	
		
		return $customer;		
	}

	public function createCustomer ($stripeEmail, $stripeToken) {
		
		$customer = Customer::create(array(
			'email' => $stripeEmail,
			'source' => $stripeToken
		));
		
		return $customer;
	}
	
	public function getCustomer() {
		
		Stripe::setApiKey( env('STRIPE_SECRET_KEY') );
	 
		$customers = Customer::all( ["limit" => 3] );
		
		dd( $customers );				
	}

	
	
	/*
  +"total_weight": 392
  +"items_total_amount": 155
  +"shipping_cost": 0
  +"tax_type": "HST"
  +"total_before_tax": 155
  +"tax_summary": 15	
	*/
	
	
	public function createStripeOrder($grandTotal, $priceDatainJSON, $shippingID ) {
		
		$user_id = Auth::user()->id;
		$user_email = Auth::user()->email;	

		// Get Shipping Address of User
		$shippingDetails = DeliveryAddress::where(['user_email' => $user_email])->first();

		if(empty(Session::get('CouponCode'))){
		   $coupon_code = ''; 
		}else{
		   $coupon_code = Session::get('CouponCode'); 
		}

		if(empty(Session::get('CouponAmount'))){
		   $coupon_amount = ''; 
		}else{
		   $coupon_amount = Session::get('CouponAmount'); 
		}	


		$order = new Order;
		$order->user_id = $user_id;
		$order->delivery_addresses_id = $shippingID;
		$order->user_email = $user_email;
		$order->name = $shippingDetails->name;
		$order->address = $shippingDetails->address;
		$order->city = $shippingDetails->city;
		$order->state = $shippingDetails->state;
		$order->pincode = $shippingDetails->pincode;
		$order->country = $shippingDetails->country;
		$order->mobile = $shippingDetails->mobile;
		$order->coupon_code = $coupon_code;
		$order->coupon_amount = $coupon_amount;
		$order->order_status = "New";
		$order->payment_method = 'Stripe';
		$order->grand_total = $grandTotal;
		$order->price_detail = $priceDatainJSON;
		$order->save();
	

		$order_id = DB::getPdo()->lastInsertId();

		$cartProducts = DB::table('cart')->where(['user_email'=>$user_email])->get();
		foreach($cartProducts as $pro){
			$cartPro = new OrdersProduct;
			$cartPro->order_id = $order_id;
			$cartPro->user_id = $user_id;
			$cartPro->product_id = $pro->product_id;
			$cartPro->product_code = $pro->product_code;
			$cartPro->product_name = $pro->product_name;
			$cartPro->product_color = $pro->product_color;
			$cartPro->product_size = $pro->size;
			$cartPro->product_price = $pro->price;
			$cartPro->product_qty = $pro->quantity;
			$cartPro->save();
		}	
		
		Session::put('order_id', $order_id);
		Session::put('grand_total', $grandTotal);		
		Session::put('cartProducts', $cartProducts);		
		Session::put('order', $order);		

	}


	
	

}










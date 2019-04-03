<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;


/*

*/
class BooksController extends Controller
{
    public function getBooks(Request $request) {
		
		$data = $request->all();
		
		
		// dd($request->pages_count);
		// ->skip($offset*$limit)->take($limit)
		
		$limit = 2;
		$offset = 0;
		$sortBy = 'id';
		$orderBy = 'desc';
		
		$query = Book::query();

		if ( $request->author ) {
			$query->where('author', '=', $request->author);
		}	

		if ( $request->title ) {
			$query->where('title', '=', $request->title);
		}	

		if ( $request->orderBy ) {
			
			$orderBy = $request->orderBy;
		}			
		
		if ( $request->pages_count['min'] ) {
			
			$query->where('pages', '>=', $request->pages_count['min']);
		}		
		
		
		
		if ( $request->sortBy ) {
			
			$sortBy = $request->sortBy;
		}	
		
		
		$response = $query->skip( $offset )->take( $limit )->orderBy( $sortBy, $orderBy )->get();
		
		
		$responseArr = $response->toArray();
		
		$responseArr['data']['onPage'] = $response->toArray();
		
		
		dd( $response->toArray() );
		
		
		dd( $query->skip( $offset )->take( $limit )->orderBy( $sortBy, $orderBy )->get() );
		
		
		
		
		/*
		$whereArr = [];
		$sortBy = 'id';
		$orderBy = 'desc';
		
		
		if ( $request->author ) {
			
			$whereArr['author'] = $request->author;
		}		
		
		if ( $request->title ) {
			
			$whereArr['title'] = $request->title;
		}		

		if ( $request->pages_count['min'] ) {
			
			$whereArr['pages'] = $request->pages_count['min'];
		}
		
		
		if ( $request->orderBy ) {
			
			$orderBy = $request->orderBy;
		}		
		

		
		
		$books = Book::where( $whereArr )->where('pages','>=', 6)->orderBy($sortBy, $orderBy)->get();
		
		
		dd( $whereArr );
		*/


		
		
		return response()->json($books);
		
		
		// return response()->json(['name' => 'Abigail', 'state' => 'CA']);

    }
}



































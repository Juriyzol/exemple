<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;


use App\Document;


/*

*/
class FrontController extends Controller
{
	
    public function allDocuments(Request $request) {		

		$documents = Document::orderBy( 'id', 'asc' )->get();
		
		return view('front.index')->with(compact('documents'));
    }

	public function viewDocument($id = null) {
		
        $document = Document::with('files')->where(['id'=>$id])->first();
		
        return view('front.viewDocument')->with(compact('document'));
    }		

}



































<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;


use App\Document;


/*

*/
class DocumentController extends Controller
{
    public function allDocuments(Request $request) {
		
		
		if ($request->isMethod('post')) {
			
			$data = $request->all();			
			
			// dd( $data );
			

			$product = new Document;
			$product->title = $data['title'];
			
			$product->save();
		}
		

		$documents = Document::orderBy( 'id', 'asc' )->get();

		
		return view('admin.allDocuments')->with(compact('documents'));
    }


	public function viewDocument($id = null) {
		
        $document = Document::with('files')->where(['id'=>$id])->first();
		
        return view('admin.viewDocument')->with(compact('document'));
    }		
	
	
	public function changeDocument(Request $request, $id = null) {
		
		if ($request->isMethod('post')) {
			
			$data = $request->all();
			
			Document::where(['id'=>$id])
			->update([
				'title'=>$data['title']
			]);
		}
		
		return redirect()->back();
    }	

	
	
	public function deleteDocument($id = null) {
		
        Document::where(['id'=>$id])->delete();
        return redirect()->back();
    }	
	
	
	

}



































<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;

use Illuminate\Http\Request;
use App\Book;


use App\Document;
use App\File;


/*
* 
*/
class FileController extends Controller
{
    public function addFile(Request $request, $documentID) {
		
		
		if ($request->isMethod('post')) {
			
			$data = $request->all();	
			
			$uniqueFileName = uniqid() . $data['uploadPDF']->getClientOriginalName();

			$data['uploadPDF']->move('files/pdf/', $uniqueFileName);
			
			
			$product = new File;
			$product->title = $data['title'];
			$product->documents_id = $documentID;
			$product->href = $uniqueFileName;
			
			$product->save();			
			

			return redirect()->back()->with('success', 'File uploaded successfully.');	
		}
    }

	
	public function deleteFile($id = null) {
		
		$file = File::where(['id'=>$id])->first();
		
		// dd( $file );
		
        if(file_exists('files/pdf/'.$file->href)) {
            unlink('files/pdf/'.$file->href);
        }
		
        File::where(['id'=>$id])->delete();
        return redirect()->back();
    }	
	

}



































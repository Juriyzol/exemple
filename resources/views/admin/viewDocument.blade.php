@extends('layouts.app')

<?php
	// dump($document);
?>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

			<tr>
			  <th scope="row"><h2>{{ $document->title }}</h2></th>
			  <td>
				<form id="changeDocumentForm" name="changeDocumentForm" action="{{ url('/admin/change-document/'.$document->id) }}" method="POST">
					{{ csrf_field() }}
					<input value="{{ $document->title }}" id="title" name="title" type="text" placeholder="Title"/>
					
					<button type="submit" class="btn btn-default">Change</button>
				</form>	
			  </td>

			</tr>

			
        </div>        
		
		
		<div class="col-md-8">



			<table class="table table-striped">
			  <thead>
				<tr>
				  <th scope="col">#</th>
				  <th scope="col">Title</th>
				  <th scope="col">PDF</th>
				  <th scope="col">Delete</th>
				</tr>
			  </thead>
			  <tbody>


				@foreach($document->files as $file)
				
					<tr>
					  <th scope="row">{{ $file->id }}</th>
					  <td>{{ $file->title }}</td>
					  <td>
						<a href="{{ asset('files/pdf/'.$file->href) }}"><button type="submit" class="btn btn-default">View</button></a>				  
					  </td>
					  <td>
						<form id="deletePDF" name="deletePDF" action="{{ url('/admin/delete-pdf/'.$file->id) }}" method="POST">
							{{ method_field('delete') }}
							{{ csrf_field() }}

							<button type="submit" class="btn btn-default">Delete</button>
						</form>					  
					  </td>

					</tr>
			
				@endforeach


			  </tbody>
			</table>

			
        </div>
		


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
		
			
			<h2>Add PDF</h2>
			<form id="addFileForm" enctype="multipart/form-data" name="addFileForm" action="{{ url('/admin/add-file/'.$document->id) }}" method="POST">
			
				{{ csrf_field() }}

				<input value="" id="title" name="title" type="text" placeholder="Title"/>
				
				<input type="file" name="uploadPDF" accept="application/pdf" />

				<button type="submit" class="btn btn-default">Add</button>
			</form>
			
			
        </div>
    </div>
</div>

		
    </div>
</div>



@endsection





















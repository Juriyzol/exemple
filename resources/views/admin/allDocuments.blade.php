@extends('layouts.app')

<?php
	// dump($documents);
?>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
			
			
			<table class="table table-striped">
			  <thead>
				<tr>
				  <th scope="col">#</th>
				  <th scope="col">Title</th>
				  <th scope="col">Delete</th>
				  <th scope="col">View</th>
				</tr>
			  </thead>
			  <tbody>


				@foreach($documents as $document)
					<tr>
					  <th scope="row">{{ $document->id }}</th>
					  <td>{{ $document->title }}</td>
					  <td>
						<form id="deleteDocumentForm" name="deleteDocumentForm" action="{{ url('/admin/delete-document/'.$document->id) }}" method="POST">
							{{ method_field('delete') }}
							{{ csrf_field() }}

							<button type="submit" class="btn btn-default">Delete</button>
						</form>					  
					  </td>
					  <td>
						<a href="{{ url('/admin/view-document/'.$document->id) }}"> <button class="btn btn-default">View</button> </a>
					  </td>
					</tr>
			
				@endforeach


			  </tbody>
			</table>
			
			
        </div>
    </div>
</div>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
		
			
			<h2>Update Document</h2>
			<form id="updateDocumentForm" name="updateDocumentForm" action="{{ url('/admin/documents') }}" method="POST">
			
				{{ csrf_field() }}

				<input value="" id="title" name="title" type="text" placeholder="Title"/>

				<button type="submit" class="btn btn-default">Add</button>
			</form>
			
			
        </div>
    </div>
</div>
@endsection





















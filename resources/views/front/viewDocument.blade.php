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

			</tr>
			
        </div>        
		
		
		<div class="col-md-8">
		
			<table class="table table-striped">
			  <thead>
				<tr>
				  <th scope="col">#</th>
				  <th scope="col">Title</th>
				  <th scope="col">PDF</th>
				</tr>
			  </thead>
			  <tbody>


				@foreach($document->files as $file)
				
					<tr>
					  <th scope="row">{{ $file->id }}</th>
					  <td>{{ $file->title }}</td>
					  <td>
						<a href="{{ asset('files/pdf/'.$file->href) }}" target="_blank"><button type="submit" class="btn btn-default">View</button></a>				  
					  </td>

					</tr>
			
				@endforeach


			  </tbody>
			</table>

			
        </div>
		


		
    </div>
</div>



@endsection





















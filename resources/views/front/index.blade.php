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
				  <th scope="col">View</th>
				</tr>
			  </thead>
			  <tbody>


				@foreach($documents as $document)
					<tr>
					  <th scope="row">{{ $document->id }}</th>
					  <td>{{ $document->title }}</td>
					  <td>
						<a href="{{ url('/view-document/'.$document->id) }}" target="_blank"> <button class="btn btn-default">View</button> </a>
					  </td>
					</tr>
			
				@endforeach


			  </tbody>
			</table>
			
			
        </div>
    </div>
</div>

@endsection





















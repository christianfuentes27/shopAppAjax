@extends('layouts.app')

@section('content')
<!-- Page Hero Start -->
    <div class="container-fluid mb-5">
        <div class="d-flex flex-column align-items-center justify-content-center responsive-img create-hero" style="min-height: 300px; background-image: url({{ url('assets/img/create-hero.jpg') }});">
            <h1 class="font-weight-semi-bold text-uppercase mb-3">Create</h1>
        </div>
    </div>
<!-- Page Hero End -->

<!-- Form Start -->
<div class="container">
    <form action="{{ url('clothes') }}" method="post" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label for="name">Name</label>
        <input required type="text" class="form-control" id="name" aria-describedby="name" placeholder="Enter name" name="name">
      </div>
      <div class="form-group">
        <label for="category">Category</label>
        <select class="form-select" aria-label="Category" name="category" id="category" required>
          <option value="man">Man</option>
          <option value="woman">Woman</option>
        </select>
      </div>
      <div class="form-group">
        <label for="size">Sizes</label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="xs" id="xs" name="size[]">
          <label class="form-check-label" for="xs">
            XS
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="s" id="s" name="size[]">
          <label class="form-check-label" for="s">
            S
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="m" id="m" name="size[]">
          <label class="form-check-label" for="m">
            M
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="l" id="l" name="size[]">
          <label class="form-check-label" for="l">
            L
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="xl" id="xl" name="size[]">
          <label class="form-check-label" for="xl">
            XL
          </label>
        </div>
      </div>
      <div class="form-group">
        <label for="price">Price</label>
        <input required type="number" step="0.01" class="form-control" id="price" placeholder="Price" name="price">
      </div>
      <div class="form-group">
        <label style="width: 100%;" for="thumbnail">Thumbnail</label>
        <input required style="display: block; margin-left: 0;" type="file" accept="image/jpeg" class="form-check-input" id="thumbnail" name="thumbnail">
      </div>
      <div class="form-group">
        <label for="photos" style="margin-top: 40px;">Article photos</label>
        <input type="file" multiple accept="image/jpeg" class="form-control-file" id="photos" name="photos[]" required>
      </div>
      <button type="submit" class="btn btn-primary mr-3 mt-2">Create</button>
      <a class="btn btn-primary px-3 mt-2" href="{{ url('/') }}">Back</a>
    </form>
</div>
<!-- Form End -->
@endsection
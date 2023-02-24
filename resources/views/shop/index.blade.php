@extends('layouts.app')

@section('scripts')
<script type="text/javascript" src="{{ url('assets/js/ajax.js') }}?{{rand(2, 20)}}"></script>
@endsection

@section('modal')
<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Create</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="createForm">
      <div class="modal-body">
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
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="saveBtn">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editForm">
      <div class="modal-body" id="editModalBody">
        <!-- Modal Body -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="saveEditBtn">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteForm">
      <div class="modal-body" id="deleteModalBody">
        <!-- Modal Body -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="confirmDeleteBtn">Delete</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('content')
<!-- Page Hero Start -->
    <div class="container-fluid mb-5">
        <div class="d-flex flex-column align-items-center justify-content-center responsive-img shop-hero" style="min-height: 300px; background-image: url({{ url('assets/img/shop.jpg') }});">
            <h1 class="font-weight-semi-bold text-uppercase mb-3">Our Shop</h1>
        </div>
    </div>
<!-- Page Hero End -->
<div class="container-fluid pt-5">
    <div class="row px-xl-5">
        <!-- Shop Sidebar Start -->
        <div class="col-lg-3 col-md-12">
            <!-- Category Start -->
            <div class="border-bottom mb-4 pb-4" id="categoryFilter">
                <!-- Categories Man or Woman -->
            </div>
            <!-- Category End -->
            
            <!-- Price Start -->
            <div class="border-bottom mb-4 pb-4" id="priceRanges">
                <!-- Price Ranges -->
            </div>
            <!-- Price End -->

            <!-- Size Start -->
            <div class="mb-5" id="sizeFilter">
                <!-- Size Filter -->
            </div>
            <!-- Size End -->
        </div>
        <!-- Shop Sidebar End -->


        <!-- Shop Product Start -->
        <div class="col-lg-9 col-md-12">
            <div class="row pb-3">
                <div class="col-12 pb-1">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <form action="" id="searchForm">
                            <!-- Search Product -->
                        </form>
                        <div class="d-flex">
                            <div class="btn border pointer" id="clearBtn">Clear filters</div>
                            <div class="dropdown ml-4">
                                <button class="btn border dropdown-toggle" type="button" id="triggerId" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                            Sort by
                                        </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="triggerId" id="sortby">
                                    <!-- Sort by Options -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row pb-3" id="shopContainer">
                    <!-- Clothes Items -->
                </div>
                
                <div class="col-12 pb-1 pt-5">
                    <ul class="pagination justify-content-center mb-3" id="pagination">
                        <!-- Pagination Links -->
                    </ul>
                </div>
            </div>
        </div>
        <!-- Shop Product End -->
    </div>
</div>
@endsection
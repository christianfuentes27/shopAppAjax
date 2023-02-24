/*global fetch*/

let csrf = document.querySelector('meta[name="csrf-token"]').content;
let editModalBody;
let deleteModalBody;
window.addEventListener('load', () => {
    document.getElementById('pagination').addEventListener('click', handleClick);
    document.getElementById('sortby').addEventListener('click', handleClick);
    document.getElementById('priceRanges').addEventListener('click', handleClick);
    document.getElementById('categoryFilter').addEventListener('click', handleClick);
    document.getElementById('sizeFilter').addEventListener('click', handleClick);
    document.getElementById('saveBtn').addEventListener('click', handleCreate);
    document.getElementById('saveEditBtn').addEventListener('click', handleEdit);
    document.getElementById('confirmDeleteBtn').addEventListener('click', handleDelete);
    document.getElementById('clearBtn').addEventListener('click', () => {fetchData('fetchdata')});
    editModalBody = document.getElementById('editModalBody');
    deleteModalBody = document.getElementById('deleteModalBody');
    fetchData('fetchdata');
});

window.onpopstate = function(e) {
    if(e.state) {
        //getPage(e.state.page, e.state.params);
        console.log('page');
        console.log(e.state);
    }
};

function fetchData(page) {
    fetch(page)
    .then(function(response) {
        // console.log(response);
        return response.json();
    })
    .then(function(jsonData) {
        // console.log(jsonData);
        showData(jsonData);
    })
    .catch(function(error) {
        console.log(error);
    });
}

function deleteItem(id) {
    fetch(`clothes/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        }
    })
    .then(response => {
        return response.json();
    })
    .then(res => {
        console.log(res);
        fetchData('fetchdata');
    })
    .catch(error => {
        console.log(error);
    });
}

function update(data, id) {
    fetch(`clothes/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        return response.json();
    })
    .then(res => {
        // console.log(res);
        fetchData('fetchdata');
    })
    .catch(error => {
        console.log(error);
    });
}

function create(data) {
    fetch('clothes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        return response.json();
    })
    .then(res => {
        fetchData('fetchdata');
    })
    .catch(error => {
        console.log(error);
    });
}

function handleDelete(e) {
    let deleteForm = document.getElementById('deleteForm');
    const formData = new FormData(deleteForm);
    deleteItem(formData.get('secretId'));
}

function handleEdit(e) {
    let editForm = document.getElementById('editForm');
    const formData = new FormData(editForm);
    let thumbnail = formData.get('thumbnail');
    if (thumbnail.size == 0) {
        thumbnail = document.getElementById('currentThumbnail').getAttribute('src').split(',')[1];
        let data = {
            name: formData.get('name'),
            category: formData.get('category'),
            price: formData.get('price'),
            sizes: formData.getAll('size[]'),
            thumbnail
        }
        // console.log('Object', data);
        // console.log('JSON', JSON.stringify(data));
        update(data, formData.get('secretId'));
    } else {
        encodeFileToBase64(formData.get('thumbnail'), function(image) {
            let imgbase64 = image.split(',');
            let thumbnail = imgbase64[1];
            let data = {
                name: formData.get('name'),
                category: formData.get('category'),
                price: formData.get('price'),
                sizes: formData.getAll('size[]'),
                thumbnail
            }
            // console.log('Object', data);
            // console.log('JSON', JSON.stringify(data));
            update(data, formData.get('secretId'));
        });
    }
}

function getItemData(itemId, cb) {
    fetch('getitemdata', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(itemId)
    })
    .then(response => {
        return response.json();
    })
    .then(res => {
        cb(res);
    })
    .catch(error => {
       console.log(error); 
    });
}

function displayDelete(e) {
    let input = e.target.parentElement.getElementsByTagName('input');
    let itemId = input[0].value;
    getItemData(itemId, function(item) {
        deleteModalBody.innerHTML = '';
        let string = 
        `
        <span>Are you sure you want to delete ${item.name}?</span>
        <input type="hidden" value="${item.id}" name="secretId"/>
        `;
        deleteModalBody.innerHTML = string;
    });
}

function displayEdit(e) {
    let input = e.target.parentElement.getElementsByTagName('input');
    let itemId = input[0].value;
    var string = '';
    getItemData(itemId, function(item) {
        console.log(item);
        editModalBody.innerHTML = '';
        
        // Category String
        let categoryString = 
        `
        <option value="man">Man</option>
        <option value="woman" selected>Woman</option>
        `;
        if(item.category == 'man') {
            categoryString = 
            `
            <option value="man" selected>Man</option>
            <option value="woman">Woman</option>
            `;
        }
        
        // Sizes String
        let sizeString = '';
        let allSizes = ['xs', 's', 'm', 'l', 'xl'];
        let sizes = [];
        for (let size of item.sizes) {
            sizes.push(size.name);
        }
        for (let size of allSizes) {
            if(sizes.includes(size)) {
                sizeString += 
                `
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="${size}" id="${size}" name="size[]" checked>
                  <label class="form-check-label" for="${size}">
                    <span style="text-transform: uppercase;">${size}</span>
                  </label>
                </div>
                `;
            } else {
                sizeString += 
                `
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="${size}" id="${size}" name="size[]">
                  <label class="form-check-label" for="${size}">
                    <span style="text-transform: uppercase;">${size}</span>
                  </label>
                </div>
                `;
            }
        }
        
        string = 
        `
          <div class="form-group">
            <label for="name">Name</label>
            <input required type="text" class="form-control" id="name" aria-describedby="name" placeholder="Enter name" name="name" value="${item.name}">
          </div>
          <div class="form-group">
            <label for="category">Category</label>
            <select class="form-select" aria-label="Category" name="category" id="category" required>
              ${categoryString}
            </select>
          </div>
          <div class="form-group">
            <label for="size">Sizes</label>
            ${sizeString}
          </div>
          <div class="form-group">
            <label for="price">Price</label>
            <input required type="number" step="0.01" class="form-control" id="price" placeholder="Price" name="price" value="${item.price}">
          </div>
          <div class="form-group">
            <label style="width: 100%;" for="thumbnail">Thumbnail</label>
            <img class="img-fluid w-100" id="currentThumbnail" src="data:image/jpeg;base64,${item.thumbnail}" alt="Clothes">
            <input required style="display: block; margin-left: 0;" type="file" accept="image/jpeg" class="form-check-input" id="thumbnail" name="thumbnail">
          </div>
          <div class="form-group">
            <label for="photos" style="margin-top: 40px;">Article photos</label>
            <input type="file" multiple accept="image/jpeg" class="form-control-file" id="photos" name="photos[]" required>
          </div>
          <input type="hidden" value="${item.id}" name="secretId"/>
        `;
        
        editModalBody.innerHTML = string;
    });
}

async function handleCreate(e) {
    let createForm = document.getElementById('createForm');
    const formData = new FormData(createForm);
    await encodeFileToBase64(formData.get('thumbnail'), function(image) {
        let imgbase64 = image.split(',');
        let thumbnail = imgbase64[1];
        generateJson(formData, thumbnail);
    });
}

function generateJson(formData, thumbnail) {
    let data = {
                name: formData.get('name'),
                category: formData.get('category'),
                sizes: formData.getAll('size[]'),
                price: formData.get('price'),
                thumbnail
            }
    console.log('Object: ', data);
    console.log('JSON Object', JSON.stringify(data));
    create(data);
}

function encodeFileToBase64(file, cb) {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = () => {
        cb(reader.result);
    }
}

function handleClick(e) {
    if (e.target.classList.contains('pulsable')) {
        console.log(e.target.getAttribute('data-url'));
        fetchData(e.target.getAttribute('data-url'));
    }
}

function showData(data) {
    let shopContainer = document.getElementById('shopContainer');
    let paginationDiv = document.getElementById('pagination');
    let sortby = document.getElementById('sortby');
    let priceRanges = document.getElementById('priceRanges');
    let categoryFilter = document.getElementById('categoryFilter');
    let sizeFilter = document.getElementById('sizeFilter');
    let searchForm = document.getElementById('searchForm');
    
    let q = data.q;
    let byvalue = data.byvalue;
    let typevalue = data.typevalue;
    let pricevalue = data.pricevalue;
    let sizevalue = data.sizevalue;
    let categvalue = data.categvalue;
    
    let order = data.order;
    let url = data.url;
    let orderby = data.orderby;
    let ordertype = data.ordertype;
    let pricerange = data.pricerange;
    let category = data.category;
    let size = data.size;
    let sizes = data.sizes;
    let clothesmodel = data.clothesmodel;
    let pagination = data.clothes.links;
    
    // Clothes items
    let string = '';
    clothesmodel.forEach(item => {
        let stringSizes = '';
        sizes[item.id].forEach(s => {
            stringSizes += `<h6 style="text-transform: uppercase; color: gray;">${s.name}&nbsp;&nbsp;</h6>`;
        });
        string += `
        <div class="col-lg-4 col-md-6 col-sm-12 pb-1">
            <div class="card product-item border-0 mb-4">
                <div class="card-header product-img position-relative overflow-hidden bg-transparent border p-0">
                    <img class="img-fluid w-100" src="data:image/jpeg;base64,${item.thumbnail}" alt="Clothes">
                </div>
                <div class="card-body border-left border-right text-center p-0 pt-4 pb-3">
                    <h6 class="text-truncate mb-3">${item.name}</h6>
                    <div class="d-flex justify-content-center">
                        <h6>${item.price} €</h6>
                    </div>
                    <div class="d-flex justify-content-center">
                        <h6>Sizes:&nbsp;&nbsp;</h6>
                        ${stringSizes}
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between bg-light border">
                    <a href="${url}/clothes/${item.id}" class="btn btn-sm text-dark p-0"><i class="fas fa-eye text-primary mr-1"></i>View</a>
                    <a href="#" class="btn btn-sm text-dark p-0 editBtn" data-toggle="modal" data-target="#editModal"><i class="fa-solid fa-pen text-primary mr-1"></i>Edit</a>
                    <a href="#" class="btn btn-sm text-dark p-0 deleteBtn" data-toggle="modal" data-target="#deleteModal"><i class="fa-solid fa-trash text-primary mr-1"></i>Delete</a>
                    <a href="#" class="btn btn-sm text-dark p-0"><i class="fas fa-shopping-cart text-primary mr-1"></i>Add</a>
                    <input type="hidden" value="${item.id}" />
                </div>
            </div>
        </div>
        `;
    });
    shopContainer.innerHTML = string;
    document.querySelectorAll('.editBtn').forEach(btn => btn.addEventListener('click', displayEdit));
    document.querySelectorAll('.deleteBtn').forEach(btn => btn.addEventListener('click', displayDelete));
    // console.log(editButtons);
    // Categories 
    string = 
    `
        <h5 class="font-weight-semi-bold mb-4">Filter by category</h5>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/man/${size}`]}">Man</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/woman/${size}`]}">Woman</div>
        </div>
    `;
    categoryFilter.innerHTML = string;
    
    // Sort by
    string = 
    `
        <div class="dropdown-item pointer pulsable" data-url="${order[`updated_at/desc/${pricerange}/${category}/${size}`]}">Latest</div>
        <div class="dropdown-item pointer pulsable" data-url="${order[`price/desc/${pricerange}/${category}/${size}`]}">Price high to low</div>
        <div class="dropdown-item pointer pulsable" data-url="${order[`price/asc/${pricerange}/${category}/${size}`]}">Price low to high</div>
    `;
    
    
    sortby.innerHTML = string;
    
    // Price ranges
    string = 
    `
        <h5 class="font-weight-semi-bold mb-4">Filter by price</h5>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/all/${category}/${size}`]}">All prices</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/0/49/${category}/${size}`]}">0 - $49</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/50/99/${category}/${size}`]}">$50 - $99</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/100/149/${category}/${size}`]}">$100 - $149</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/150/199/${category}/${size}`]}">$150 - $199</div>
        </div>
        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between">
            <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/200/249/${category}/${size}`]}">$200 - $249</div>
        </div>
    `; 
    
    priceRanges.innerHTML = string;
    
    // Size filters
    string =
    `
        <h5 class="font-weight-semi-bold mb-4">Filter by size</h5>
        <form>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/any`]}">Any</div>
            </div>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/xs`]}">XS</div>
            </div>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/s`]}">S</div>
            </div>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/m`]}">M</div>
            </div>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/l`]}">L</div>
            </div>
            <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between">
                <div class="main-color pointer pulsable" data-url="${order[`${orderby}/${ordertype}/${pricerange}/${category}/xl`]}">XL</div>
            </div>
        </form>
    `;
    sizeFilter.innerHTML = string;
    
    // Search
    string = 
    `
    <div class="input-group">
        <input type="search" class="form-control" placeholder="Search" id="searchBar" name="q" value="${(q != null) ? q : ''}">
        <input type="hidden" name="orderby" value="${(byvalue != null) ? byvalue : ''}"/>
        <input type="hidden" name="ordertype" value="${(typevalue != null) ? typevalue : ''}"/>
        <input type="hidden" name="pricerange" value="${(pricevalue != null) ? pricevalue : ''}"/>
        <input type="hidden" name="category" value="${(categvalue != null) ? categvalue : ''}"/>
        <input type="hidden" name="size" value="${(sizevalue != null) ? sizevalue : ''}"/>
        <div class="input-group-append">
            <div id="searchBtn" class="input-group-text bg-transparent text-primary pointer">
                <i class="fa fa-search"></i>
            </div>
        </div>
    </div>
    `;
    searchForm.innerHTML = string;
    
    document.getElementById('searchBtn').addEventListener('click', () => {
        const formData = new FormData(searchForm);
        formData.get('q');
        formData.get('orderby');
        formData.get('ordertype');
        formData.get('pricerange');
        formData.get('category');
        formData.get('size');
        let newUrl = `${url}/fetchdata?orderby=${formData.get('orderby')}&ordertype=${formData.get('ordertype')}&pricerange=${formData.get('pricerange')}&category=${formData.get('category')}&size=${formData.get('size')}&q=${formData.get('q')}`;
        fetchData(newUrl);
    });

    // pagination
    string = '';
    pagination.forEach(pag => {
        if (pag.active) {
            string += `
                <li class="page-item active" aria-current="page">
                    <span class="page-link pulsable" data-url="${pag.url}">${pag.label}</span>
                </li>
            `;
        } else if (pag.url != null) {
            string += `
                <li class="page-item">
                    <span class="btn btn-link pulsable" data-url="${pag.url}" id="${'pag' + pag.label}">${pag.label}</span>
                </li>
            `;
        } else {
            string += `
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">${pag.label}</span>
                </li>
            `;
        }
    });
    paginationDiv.innerHTML = string;
}

// function pushState(url) {
//     var jsonPage = {'url': url};
//     window.history.pushState(jsonPage, '', url);
// }